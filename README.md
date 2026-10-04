# eInvoicing (módulo vqmod de InvoiceFlash)

Genera la **factura electrónica europea** (norma EN 16931) de cada factura de venta, en formato
**UBL 2.1** con el perfil **Peppol BIS Billing 3.0** (o CIUS-ES-FACe, CIUS-IT, CIUS-RO, NLCIUS,
CIUS-AT, o EN 16931 sin CIUS). Valida el documento con las reglas de negocio de la norma, lo guarda
y lo ofrece para descargar.

Módulo independiente: solo se ancla al núcleo de InvoiceFlash (0.0.17 o posterior). No depende de
Facturae, VeriFactu ni TicketBAI y puede convivir con ellos (probado junto a `ticketbai.xml`).

Se apoya en [josemmo/einvoicing](https://github.com/josemmo/einvoicing) (MIT, commit `6e042d1`) y su
dependencia [josemmo/uxml](https://github.com/josemmo/uxml) (MIT, commit `ecdc9f1`). Están copiadas
sin cambios en `system/vendor/einvoicing/{Einvoicing,UXML}` con sus licencias: **no hace falta
Composer**. `system/library/einvoicing.php` solo registra su autocargador.

## Qué no hace

- **No envía** la factura a ninguna red. Para entregarla por Peppol hace falta un punto de acceso
  (Access Point) AS4, que se contrata aparte; con el XML descargado se la das a ese proveedor.
- **No sustituye** al validador Schematron oficial. La librería valida las reglas de negocio de la
  norma y del perfil, no todo el Schematron de Peppol. Los documentos de prueba también pasaron las
  reglas oficiales de CEN (`EN16931-UBL-validation.xslt` 1.3.16 con Saxon-HE 9.9); las reglas
  específicas de Peppol (`PEPPOL-EN16931-UBL.sch`) no se ejecutaron.
- No genera CII ni importa facturas recibidas (la librería sí puede; no está conectado).

## Requisitos

- InvoiceFlash 0.0.17 o posterior.
- PHP 7.1 o posterior con la extensión `dom`. Probado en PHP 8.3.
- Sistema > Ajustes con el **NIF/IVA**, nombre, dirección, código postal, país y email de la empresa.
- Los impuestos en **Sistema > Localización > Impuestos** como porcentaje (tipo `P`).

## Contenido

```
eInvoicing/
├── admin/controller/einvoicing/einvoicing.php     Ventas > Factura electrónica: listado, Ajustes, regenerar, XML
├── admin/model/einvoicing/einvoicing.php          factura de InvoiceFlash -> Invoice de la librería, validación, tabla
├── admin/language/{es_ES,en-gb}/einvoicing/einvoicing.php
├── admin/view/template/einvoicing/{einvoicing_list,einvoicing_setting,info_pane,info_script}.tpl
├── system/library/einvoicing.php                  autocargador de la librería
├── system/vendor/einvoicing/{Einvoicing,UXML}/    josemmo/einvoicing y josemmo/uxml (MIT), sin cambios
└── vqmod/xml/einvoicing.xml                       id einvoicing_modulo
```

## Qué hace el XML (`einvoicing.xml`)

| Fichero del núcleo | Cambio |
|---|---|
| `admin/controller/common/header.php` | Antes de `if ($sales) {`: crea la tabla `einvoicing_invoice` (idempotente), da permisos `einvoicing/einvoicing` al grupo 1 y añade **Ventas > Factura electrónica** |
| `admin/language/{es_ES,en-gb}/common/header.php` | `text_einvoicing` |
| `admin/controller/sale/invoice.php`, alta | Tras `addInvoice()`, `autoGenerate()` genera y valida el XML si `einvoicing_active` está activo. Nunca interrumpe el alta |
| `sale/draft.php`, `sale/delivery.php` | Lo mismo al convertir un borrador o un albarán en factura |
| `sale/invoice.php`, `delete()` ("Anular") | La factura negativa de `createNegativeInvoice()` se emite como **abono (tipo 381)** de la original, con su referencia (BT-25) |
| `sale/invoice.php`, ficha, y `sale/invoice_info.tpl` | Pestaña **Factura electrónica** (fecha, estado, perfil, mensaje) con los botones **Generar XML** y **Descargar XML** |

No toca ninguna tabla ni columna del núcleo.

## Cómo se arma el documento

- **Emisor**: nombre, `config_vat_id` (o `config_nif`), dirección y código postal, país, email y
  teléfono de Ajustes. El NIF se completa con el prefijo del país (`ESB12345678`).
- **Comprador**: datos de facturación de la factura y, si faltan, la ficha del cliente (NIF, código
  postal, ciudad, dirección, país).
- **Dirección electrónica** (BT-34/BT-49): el NIF-IVA con el esquema EAS del país (ES 9920, DE 9930,
  FR 9957, IT 9906, AT 9914, BE 9925, NL 9944, PT 9946, SE 9955) y, si no hay, el email con el esquema
  `EM`. El emisor puede fijarla a mano en Ajustes (`ESQUEMA:valor`). Los países que no están en la
  lista usan el email: añade el esquema en `electronicAddress()` si lo necesitas.
- **Líneas**: cantidad, precio y descuento (se emite como descuento de línea). El tipo de IVA se
  deduce de cuota / base y se ajusta al tipo real de **Impuestos** más cercano; si no se parece a
  ninguno la factura queda con error. Las líneas sin IVA usan la categoría de Ajustes (por defecto
  `E` exenta) con su código VATEX y/o texto.
- **Pago**: la cuenta de la factura (`bank_index`), la predeterminada o el IBAN de Ajustes, con el
  medio de pago elegido. Vencimiento = fecha de la factura + los días de Ajustes (los abonos no
  llevan).
- **Referencia del comprador** (BT-10, obligatoria en Peppol): el número de la factura.
- **Redondeo**: si el total de InvoiceFlash difiere hasta 5 céntimos del que calcula la librería, la
  diferencia va como importe de redondeo (BT-114). Si es mayor, la factura queda con error.

## Datos

Tabla `einvoicing_invoice`, una fila por factura (`UNIQUE(invoice_id)`): perfil, tipo (380/381),
factura original (abonos), número, fecha, cliente, total, estado (`valid` o `invalid`), mensaje de la
validación, **XML** y número de intentos. Regenerar sobrescribe el XML (no hay firma ni
encadenamiento que lo impidan). Una factura con errores se guarda igualmente, sin XML, para que se vea
en el listado qué corregir.

Los ajustes van al grupo `einvoicing` de `setting`: `einvoicing_active`, `_preset`, `_seller_endpoint`,
`_payment_days`, `_means_code`, `_exempt_category`, `_exempt_code`, `_exempt_reason`.

## Instalación

1. Copiar `admin/`, `system/` y `vqmod/` a la raíz de la instalación, sin pisar nada del núcleo.
2. Borrar `vqmod/vqcache/*` y `vqmod/mods.cache`.
3. Entrar al admin (la tabla y los permisos se crean solos) y abrir **Ventas > Factura electrónica >
   Ajustes**: elegir perfil, activar y guardar.
4. Los permisos de otros grupos de usuarios se dan en Sistema > Grupos de usuarios.

## Pruebas hechas

Con un arnés PHP 8.3 sobre una BD temporal (borrada después) y modelos del núcleo simulados:

- factura con tres tipos de IVA, descuento, cuenta bancaria y nota; abono de la misma; factura exenta a
  un cliente alemán; cliente sin país con esquema (usa el email); diferencia de redondeo;
- errores esperados: cliente sin nombre, IVA que no existe en Impuestos;
- los XML generados pasaron `EN16931-UBL-validation.xslt` (CEN 1.3.16) sin ningún fallo, y el mismo
  validador detecta un total de IVA alterado a mano;
- `einvoicing.xml` aplicado con vqmod a copias de los ficheros de InvoiceFlash-0.0.17, junto con
  `ticketbai.xml`: cada operación casa una vez, los ficheros resultantes pasan `php -l`.

**Sin probar** en un navegador ni dentro de una instalación real de InvoiceFlash, ni con las reglas
Schematron propias de Peppol, ni contra un punto de acceso.

## Licencia

GNU GPL v3, la misma que InvoiceFlash (ver `LICENSE`). Las librerías de `system/vendor/einvoicing`
(josemmo/einvoicing y josemmo/uxml) son MIT, compatible con la GPL, y conservan sus licencias
(`LICENSE-josemmo-einvoicing`, `LICENSE-josemmo-uxml`).
