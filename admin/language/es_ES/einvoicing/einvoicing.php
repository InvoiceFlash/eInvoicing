<?php
// Heading
$_['heading_title']              = 'Factura electr&oacute;nica (EN 16931)';
$_['heading_setting']            = 'Ajustes de factura electr&oacute;nica';

// Text
$_['text_home']                  = 'Inicio';
$_['text_success_setting']       = 'Ajustes de factura electr&oacute;nica guardados.';
$_['text_generated_ok']          = 'XML generado y validado.';
$_['text_no_results']            = 'Todav&iacute;a no se ha generado ninguna factura electr&oacute;nica.';
$_['text_all']                   = 'Todos';
$_['text_yes']                   = 'S&iacute;';
$_['text_no']                    = 'No';
$_['text_invoice']               = 'Factura';
$_['text_credit_note']           = 'Abono';
$_['text_status_valid']          = 'V&aacute;lida';
$_['text_status_invalid']        = 'Con errores';
$_['text_not_generated']         = 'Sin generar';
$_['text_discount']              = 'Descuento';
$_['text_default_exempt_reason'] = 'Operaci&oacute;n exenta de IVA';
$_['text_active_note']           = 'Con el m&oacute;dulo activo, el XML de cada factura nueva se genera y se valida al crearla. No se env&iacute;a a ninguna red: se descarga desde aqu&iacute; o desde la ficha de la factura.';
$_['text_requirements_ok']       = 'Servidor preparado para generar facturas electr&oacute;nicas.';
$_['text_validation_note']       = 'La validaci&oacute;n comprueba las reglas de negocio de EN 16931 y del perfil elegido (librer&iacute;a josemmo/einvoicing). No sustituye al validador Schematron oficial de Peppol/KoSIT antes de enviar a un cliente o a una administraci&oacute;n.';
$_['text_confirm_regenerate']    = '&iquest;Volver a generar el XML de esta factura?';
$_['text_preset_note']           = 'Peppol BIS Billing 3.0 es el perfil que aceptan la red Peppol y la mayor&iacute;a de pa&iacute;ses.';
$_['text_endpoint_note']         = 'Formato ESQUEMA:valor, por ejemplo 9920:ESB12345678. Si se deja vac&iacute;o se usa el NIF-IVA del emisor con el esquema de su pa&iacute;s o, si no lo hay, el email de Ajustes.';
$_['text_days_note']             = 'Fecha de vencimiento = fecha de la factura + estos d&iacute;as.';
$_['text_means_30']              = '30 - Transferencia';
$_['text_means_58']              = '58 - Transferencia SEPA';
$_['text_means_42']              = '42 - Ingreso en cuenta';
$_['text_means_10']              = '10 - Efectivo';
$_['text_means_48']              = '48 - Tarjeta';
$_['text_means_49']              = '49 - Domiciliaci&oacute;n';
$_['text_cat_e']                 = 'E - Exenta';
$_['text_cat_z']                 = 'Z - Tipo cero';
$_['text_cat_ae']                = 'AE - Inversi&oacute;n del sujeto pasivo';
$_['text_cat_k']                 = 'K - Entrega intracomunitaria';
$_['text_cat_g']                 = 'G - Exportaci&oacute;n';
$_['text_cat_o']                 = 'O - No sujeta';
$_['text_exempt_note']           = 'Se aplica a las l&iacute;neas sin IVA. La causa de exenci&oacute;n es obligatoria en las categor&iacute;as E, AE, K, G y O: c&oacute;digo VATEX (por ejemplo VATEX-EU-132) o texto, o los dos.';

// Column
$_['column_invoice']             = 'Factura';
$_['column_type']                = 'Tipo';
$_['column_customer']            = 'Cliente';
$_['column_date']                = 'Fecha';
$_['column_total']               = 'Total';
$_['column_profile']             = 'Perfil';
$_['column_status']              = 'Estado';
$_['column_action']              = 'Acci&oacute;n';

// Entry
$_['entry_active']               = 'Generar al crear la factura';
$_['entry_preset']               = 'Perfil (CIUS)';
$_['entry_endpoint']             = 'Direcci&oacute;n electr&oacute;nica del emisor';
$_['entry_payment_days']         = 'D&iacute;as de vencimiento';
$_['entry_means_code']           = 'Medio de pago';
$_['entry_exempt_category']      = 'Categor&iacute;a de IVA sin cuota';
$_['entry_exempt_code']          = 'C&oacute;digo de exenci&oacute;n (VATEX)';
$_['entry_exempt_reason']        = 'Texto de exenci&oacute;n';
$_['entry_status']               = 'Estado';

// Button
$_['button_setting']             = 'Ajustes';
$_['button_save']                = 'Guardar';
$_['button_cancel']              = 'Cancelar';
$_['button_regenerate']          = 'Regenerar';
$_['button_xml']                 = 'XML';
$_['button_filter']              = 'Filtrar';

// Error
$_['error_warning']              = 'Revise los datos marcados del formulario.';
$_['error_permission']           = 'No tiene permiso para modificar la factura electr&oacute;nica.';
$_['error_php']                  = 'La factura electr&oacute;nica necesita PHP 7.1 o posterior (este servidor tiene %s).';
$_['error_dom']                  = 'La factura electr&oacute;nica necesita la extensi&oacute;n de PHP &quot;dom&quot; (act&iacute;vela en php.ini y reinicie el servidor web).';
$_['error_library']              = 'Faltan las librer&iacute;as del m&oacute;dulo en system/vendor/einvoicing.';
$_['error_invoice_not_found']    = 'No se encuentra la factura.';
$_['error_no_products']          = 'La factura no tiene l&iacute;neas.';
$_['error_line_quantity']        = 'La l&iacute;nea %s tiene cantidad cero.';
$_['error_tax_rate']             = 'La l&iacute;nea %s lleva un IVA del %s %% que no coincide con ning&uacute;n tipo de Sistema &gt; Localizaci&oacute;n &gt; Impuestos.';
$_['error_total_mismatch']       = 'El total de la factura (%s) no coincide con el que resulta de las l&iacute;neas y el IVA (%s).';
$_['error_seller_vat']           = 'Falta el NIF/IVA del emisor en Sistema &gt; Ajustes.';
$_['error_buyer_name']           = 'El cliente no tiene nombre o raz&oacute;n social.';
$_['error_buyer_endpoint']       = 'El cliente no tiene NIF-IVA de un pa&iacute;s con esquema Peppol ni email: no hay direcci&oacute;n electr&oacute;nica del comprador.';
$_['error_not_generated']        = 'Esta factura no tiene XML.';
$_['error_endpoint_format']      = 'Escriba la direcci&oacute;n electr&oacute;nica como ESQUEMA:valor, por ejemplo 9920:ESB12345678.';

// Pestana de la ficha de factura
$_['tab_einvoicing']             = 'Factura electr&oacute;nica';
$_['text_info_generated']        = 'Generada el';
$_['text_info_status']           = 'Estado';
$_['text_info_profile']          = 'Perfil';
$_['text_info_notice']           = 'Mensaje';
$_['button_regenerate_invoice']  = 'Generar XML';
$_['button_download_xml']        = 'Descargar XML';
