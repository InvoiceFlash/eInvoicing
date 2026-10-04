<?php
/**
 * Cargador de la libreria josemmo/einvoicing (EN 16931, UBL) y de josemmo/uxml, sin Composer.
 *
 * Las dos librerias (MIT) estan copiadas tal cual en system/vendor/einvoicing/{Einvoicing,UXML}
 * con sus licencias. Aqui solo se registra un autocargador PSR-4 para sus espacios de nombres.
 * Requiere PHP 7.1 o posterior y la extension dom.
 */
class EinvoicingLoader {
	private static $registered = false;

	public static function register() {
		if (self::$registered) {
			return;
		}

		$base = DIR_SYSTEM . 'vendor/einvoicing/';

		spl_autoload_register(function ($class) use ($base) {
			foreach (array('Einvoicing\\', 'UXML\\') as $prefix) {
				if (strncmp($class, $prefix, strlen($prefix)) === 0) {
					$file = $base . str_replace('\\', '/', $class) . '.php';

					if (is_file($file)) {
						require_once($file);
					}

					return;
				}
			}
		});

		self::$registered = true;
	}

	// Mensaje de error si el servidor no puede usar la libreria, o '' si todo esta bien.
	public static function requirementsError() {
		if (version_compare(PHP_VERSION, '7.1.0', '<')) {
			return 'php';
		}

		if (!extension_loaded('dom')) {
			return 'dom';
		}

		if (!is_file(DIR_SYSTEM . 'vendor/einvoicing/Einvoicing/Invoice.php') || !is_file(DIR_SYSTEM . 'vendor/einvoicing/UXML/UXML.php')) {
			return 'library';
		}

		return '';
	}
}
