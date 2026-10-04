<?php
/**
 * Factura electronica europea (EN 16931) sobre josemmo/einvoicing (system/vendor/einvoicing).
 *
 * Por cada factura de venta construye el documento UBL 2.1 con el perfil elegido (Peppol BIS
 * Billing 3.0 por defecto), lo valida con las reglas de negocio de la libreria y guarda el XML en
 * `einvoicing_invoice`. No envia nada a ninguna red: el XML se descarga y se entrega por el canal
 * que se use (punto de acceso Peppol, portal del cliente, email...).
 */
class ModelEinvoicingEinvoicing extends Model {
	const STATUS_VALID   = 'valid';
	const STATUS_INVALID = 'invalid';

	private static $tables_checked = false;
	private $texts = null;
	private $status_cache = array();

	public function install() {
		if (self::$tables_checked) {
			return;
		}

		$this->db->query("CREATE TABLE IF NOT EXISTS `" . DB_PREFIX . "einvoicing_invoice` (
			`einvoicing_invoice_id` INT(11) NOT NULL AUTO_INCREMENT,
			`invoice_id` INT(11) NOT NULL,
			`store_id` INT(11) NOT NULL DEFAULT '0',
			`preset` VARCHAR(32) NOT NULL DEFAULT '',
			`type_code` INT(11) NOT NULL DEFAULT '380',
			`credit_of` INT(11) NOT NULL DEFAULT '0',
			`number` VARCHAR(64) NOT NULL DEFAULT '',
			`issue_date` DATE NULL DEFAULT NULL,
			`customer` VARCHAR(255) NOT NULL DEFAULT '',
			`total` DECIMAL(15,2) NOT NULL DEFAULT '0.00',
			`status` VARCHAR(16) NOT NULL DEFAULT 'invalid',
			`message` TEXT NOT NULL,
			`xml` MEDIUMTEXT NOT NULL,
			`attempts` INT(11) NOT NULL DEFAULT '0',
			`date_generated` DATETIME NOT NULL,
			PRIMARY KEY (`einvoicing_invoice_id`),
			UNIQUE KEY `invoice_id` (`invoice_id`),
			KEY `status` (`status`)
		) ENGINE=InnoDB DEFAULT CHARSET=utf8");

		self::$tables_checked = true;
	}

	public function isActive() {
		return (bool)$this->config->get('einvoicing_active');
	}

	// Perfiles (CIUS) que ofrece la libreria. 'en16931' = norma sin CIUS.
	public function getPresets() {
		return array(
			'peppol'       => array('class' => 'Einvoicing\\Presets\\Peppol',       'name' => 'Peppol BIS Billing 3.0'),
			'en16931'      => array('class' => '',                                  'name' => 'EN 16931 (sin CIUS)'),
			'cius-es-face' => array('class' => 'Einvoicing\\Presets\\CiusEsFace',   'name' => 'CIUS-ES-FACe'),
			'cius-it'      => array('class' => 'Einvoicing\\Presets\\CiusIt',       'name' => 'CIUS-IT'),
			'cius-ro'      => array('class' => 'Einvoicing\\Presets\\CiusRo',       'name' => 'CIUS-RO'),
			'nlcius'       => array('class' => 'Einvoicing\\Presets\\Nlcius',       'name' => 'NLCIUS'),
			'cius-at-gov'  => array('class' => 'Einvoicing\\Presets\\CiusAtGov',    'name' => 'CIUS-AT-GOV'),
			'cius-at-nat'  => array('class' => 'Einvoicing\\Presets\\CiusAtNat',    'name' => 'CIUS-AT-NAT')
		);
	}

	public function getPreset() {
		$presets = $this->getPresets();
		$code = (string)$this->config->get('einvoicing_preset');

		return isset($presets[$code]) ? $code : 'peppol';
	}

	// Mensaje de error si el servidor no puede generar facturas, o ''.
	public function requirementsError() {
		require_once(DIR_SYSTEM . 'library/einvoicing.php');

		$code = EinvoicingLoader::requirementsError();

		if ($code == 'php') {
			return sprintf($this->text('error_php'), PHP_VERSION);
		} elseif ($code == 'dom') {
			return $this->text('error_dom');
		} elseif ($code == 'library') {
			return $this->text('error_library');
		}

		return '';
	}

	// ---- Registros -----------------------------------------------------------------------

	public function getRecord($invoice_id) {
		$this->install();

		$query = $this->db->query("SELECT * FROM `" . DB_PREFIX . "einvoicing_invoice` WHERE invoice_id = '" . (int)$invoice_id . "'");

		return $query->num_rows ? $query->row : array();
	}

	public function getRecords($data = array()) {
		$this->install();

		$sql = "SELECT e.*, i.date_added FROM `" . DB_PREFIX . "einvoicing_invoice` e LEFT JOIN `" . DB_PREFIX . "invoice` i ON (i.invoice_id = e.invoice_id)" . $this->getRecordsWhere($data) . " ORDER BY e.einvoicing_invoice_id DESC";

		if (isset($data['start']) || isset($data['limit'])) {
			$sql .= " LIMIT " . max(0, (int)$data['start']) . "," . max(1, (int)$data['limit']);
		}

		return $this->db->query($sql)->rows;
	}

	public function getTotalRecords($data = array()) {
		$this->install();

		$query = $this->db->query("SELECT COUNT(*) AS total FROM `" . DB_PREFIX . "einvoicing_invoice` e" . $this->getRecordsWhere($data));

		return (int)$query->row['total'];
	}

	private function getRecordsWhere($data) {
		$where = array();

		if (!empty($data['filter_status'])) {
			$where[] = "e.status = '" . $this->db->escape($data['filter_status']) . "'";
		}

		return $where ? " WHERE " . implode(" AND ", $where) : "";
	}

	// Estado ('' si nunca se genero) para listados.
	public function getStatus($invoice_id) {
		$invoice_id = (int)$invoice_id;

		if (!isset($this->status_cache[$invoice_id])) {
			$record = $this->getRecord($invoice_id);

			$this->status_cache[$invoice_id] = $record ? $record['status'] : '';
		}

		return $this->status_cache[$invoice_id];
	}

	// Datos de la pestana de la ficha de factura.
	public function getInfo($invoice_id) {
		$record = $this->getRecord($invoice_id);

		if (!$record) {
			return array('status' => $this->text('text_not_generated'), 'valid' => false, 'generated' => '', 'notice' => '', 'profile' => '', 'has_xml' => false);
		}

		$presets = $this->getPresets();

		return array(
			'status'    => $this->text($record['status'] == self::STATUS_VALID ? 'text_status_valid' : 'text_status_invalid'),
			'valid'     => $record['status'] == self::STATUS_VALID,
			'generated' => $record['date_generated'],
			'notice'    => $record['message'],
			'profile'   => (isset($presets[$record['preset']]) ? $presets[$record['preset']]['name'] : $record['preset']) . ' - ' . ($record['type_code'] == 381 ? $this->text('text_credit_note') : $this->text('text_invoice')),
			'has_xml'   => $record['xml'] !== ''
		);
	}

	// ---- Generacion ----------------------------------------------------------------------

	/**
	 * Generacion automatica tras crear una factura (hooks de einvoicing.xml). No hace nada si el
	 * modulo no esta activo y nunca interrumpe el alta de la factura.
	 */
	public function autoGenerate($invoice_id, $credit_of = 0) {
		if (!$this->isActive()) {
			return false;
		}

		try {
			return $this->generate($invoice_id, $credit_of);
		} catch (Exception $exception) {
			return array('success' => false, 'message' => $exception->getMessage());
		} catch (Throwable $exception) {
			return array('success' => false, 'message' => $exception->getMessage());
		}
	}

	/**
	 * Construye, valida y guarda el XML de una factura. $credit_of > 0 la emite como abono
	 * (tipo 381) de esa factura. Devuelve array('success' => bool, 'message' => string).
	 */
	public function generate($invoice_id, $credit_of = 0) {
		$this->install();

		$error = $this->requirementsError();

		if ($error) {
			return array('success' => false, 'message' => $error);
		}

		$this->load->model('sale/invoice');

		$invoice_info = $this->model_sale_invoice->getInvoice($invoice_id);

		if (!$invoice_info) {
			return array('success' => false, 'message' => $this->text('error_invoice_not_found'));
		}

		$previous = $this->getRecord($invoice_id);

		if (!$credit_of && $previous) {
			$credit_of = (int)$previous['credit_of'];
		}

		$preset = $this->getPreset();
		$presets = $this->getPresets();
		$type_code = 380;
		$xml = '';
		$message = '';

		try {
			$built = $this->buildInvoice($invoice_info, $preset, $credit_of);

			$type_code = $built['type_code'];

			$built['invoice']->validate();

			$writer = new \Einvoicing\Writers\UblWriter();
			$xml = $writer->export($built['invoice']);

			$status = self::STATUS_VALID;
			$message = $this->text('text_generated_ok');
		} catch (\Einvoicing\Exceptions\ValidationException $exception) {
			$status = self::STATUS_INVALID;
			$message = $exception->getMessage();
		} catch (Exception $exception) {
			$status = self::STATUS_INVALID;
			$message = $exception->getMessage();
		}

		$customer = $invoice_info['payment_company'] ? $invoice_info['payment_company'] : ($invoice_info['company'] ? $invoice_info['company'] : trim($invoice_info['payment_firstname'] . ' ' . $invoice_info['payment_lastname']));

		$values = "preset = '" . $this->db->escape($preset) . "',
			type_code = '" . (int)$type_code . "',
			credit_of = '" . (int)$credit_of . "',
			number = '" . $this->db->escape($this->getNumber($invoice_info)) . "',
			issue_date = '" . $this->db->escape(date('Y-m-d', strtotime($invoice_info['date_added']))) . "',
			customer = '" . $this->db->escape(utf8_substr(html_entity_decode((string)$customer, ENT_QUOTES, 'UTF-8'), 0, 255)) . "',
			total = '" . (float)$invoice_info['total'] . "',
			status = '" . $this->db->escape($status) . "',
			message = '" . $this->db->escape($message) . "',
			xml = '" . $this->db->escape($xml) . "',
			date_generated = NOW()";

		if ($previous) {
			$this->db->query("UPDATE `" . DB_PREFIX . "einvoicing_invoice` SET " . $values . ", attempts = attempts + 1 WHERE invoice_id = '" . (int)$invoice_id . "'");
		} else {
			$this->db->query("INSERT INTO `" . DB_PREFIX . "einvoicing_invoice` SET invoice_id = '" . (int)$invoice_id . "', store_id = '" . (int)$invoice_info['store_id'] . "', attempts = 1, " . $values);
		}

		unset($this->status_cache[(int)$invoice_id]);

		return array('success' => $status == self::STATUS_VALID, 'message' => $message);
	}

	private function getNumber($invoice_info) {
		return trim((string)$invoice_info['invoice_prefix']) . ($invoice_info['invoice_no'] ? $invoice_info['invoice_no'] : $invoice_info['invoice_id']);
	}

	/**
	 * Monta el objeto \Einvoicing\Invoice a partir de la factura de InvoiceFlash.
	 * Devuelve array('invoice' => Invoice, 'type_code' => int). Lanza Exception con un texto
	 * legible si faltan datos.
	 */
	private function buildInvoice($invoice_info, $preset, $credit_of) {
		EinvoicingLoader::register();

		$invoice_id = (int)$invoice_info['invoice_id'];

		$products = $this->model_sale_invoice->getInvoiceProducts($invoice_id);

		if (!$products) {
			throw new Exception($this->text('error_no_products'));
		}

		// La factura negativa que crea "Anular" (o cualquier factura con total negativo) se emite
		// como abono: cantidades e importes en positivo, tipo 381.
		$credit = $credit_of > 0 || (float)$invoice_info['total'] < 0;

		$presets = $this->getPresets();
		$preset_class = $presets[$preset]['class'];

		$invoice = $preset_class !== '' ? new \Einvoicing\Invoice($preset_class) : new \Einvoicing\Invoice();

		if ($preset_class === '') {
			$invoice->setSpecification('urn:cen.eu:en16931:2017');
		}

		$issue_date = new DateTime(date('Y-m-d', strtotime($invoice_info['date_added'])));

		$number = $this->getNumber($invoice_info);

		$invoice->setNumber($number)
			->setType($credit ? \Einvoicing\Invoice::TYPE_CREDIT_NOTE : \Einvoicing\Invoice::TYPE_COMMERCIAL_INVOICE)
			->setCurrency($invoice_info['currency_code'] ? strtoupper($invoice_info['currency_code']) : strtoupper((string)$this->config->get('config_currency')))
			->setIssueDate($issue_date)
			->setBuyerReference($number);

		if (!$credit) {
			$days = max(0, (int)$this->config->get('einvoicing_payment_days'));

			$due_date = clone $issue_date;
			$due_date->modify('+' . $days . ' days');

			$invoice->setDueDate($due_date);
		}

		if ($credit_of > 0) {
			$original = $this->model_sale_invoice->getInvoice($credit_of);

			if ($original) {
				$invoice->addPrecedingInvoiceReference(new \Einvoicing\InvoiceReference($this->getNumber($original), new DateTime(date('Y-m-d', strtotime($original['date_added'])))));
			}
		}

		$comment = trim(html_entity_decode(strip_tags((string)$invoice_info['comment']), ENT_QUOTES, 'UTF-8'));

		if ($comment !== '') {
			$invoice->addNote(utf8_substr($comment, 0, 1000));
		}

		list($seller, $buyer, $seller_country) = $this->buildParties($invoice_info);

		$invoice->setSeller($seller)->setBuyer($buyer);

		$rates = $this->getVatRates();

		$line_no = 0;

		foreach ($products as $product) {
			$line_no++;

			$quantity = abs((float)$product['quantity']);
			$price = abs((float)$product['price']);
			$discount_rate = abs((float)$product['discount']);
			$net_total = abs((float)$product['total']);
			$tax_total = abs((float)$product['tax'] * (float)$product['quantity']);

			if ($quantity <= 0) {
				throw new Exception(sprintf($this->text('error_line_quantity'), $line_no));
			}

			$name = trim(html_entity_decode((string)$product['name'], ENT_QUOTES, 'UTF-8'));

			$line = new \Einvoicing\InvoiceLine();
			$line->setId((string)$line_no)
				->setName($name !== '' ? utf8_substr($name, 0, 250) : '-')
				->setPrice($price)
				->setQuantity($quantity)
				->setUnit('C62');

			if ($product['model'] !== '') {
				$line->setSellerIdentifier(utf8_substr((string)$product['model'], 0, 64));
			}

			// invoice_product.discount es un porcentaje; el IVA es por unidad y va sobre el neto.
			$discount_amount = round($price * $quantity * $discount_rate / 100, 2);

			if ($discount_amount > 0) {
				$allowance = new \Einvoicing\AllowanceOrCharge();
				$allowance->setAmount($discount_amount)->setReason($this->text('text_discount'));

				$line->addAllowance($allowance);
			}

			if ($tax_total > 0 && $net_total > 0) {
				$vat_rate = $this->snapVatRate($tax_total / $net_total * 100, $rates);

				if ($vat_rate === null) {
					throw new Exception(sprintf($this->text('error_tax_rate'), $line_no, round($tax_total / $net_total * 100, 2)));
				}

				$line->setVatCategory('S')->setVatRate($vat_rate);
			} else {
				$this->setExempt($line);
			}

			$invoice->addLine($line);
		}

		$this->setPayment($invoice, $invoice_info);

		// El total de InvoiceFlash redondea el IVA por documento; si difiere unos centimos del que
		// calcula la libreria se declara como importe de redondeo (BT-114) en vez de falsear lineas.
		$totals = $invoice->getTotals();
		$difference = round(abs((float)$invoice_info['total']) - $totals->payableAmount, 2);

		if ($difference != 0) {
			if (abs($difference) > 0.05) {
				throw new Exception(sprintf($this->text('error_total_mismatch'), number_format(abs((float)$invoice_info['total']), 2, '.', ''), number_format($totals->payableAmount, 2, '.', '')));
			}

			$invoice->setRoundingAmount($difference);
		}

		return array('invoice' => $invoice, 'type_code' => $credit ? 381 : 380);
	}

	private function setExempt($vat_holder) {
		$category = (string)$this->config->get('einvoicing_exempt_category');

		if (!in_array($category, array('E', 'Z', 'AE', 'K', 'G', 'O'))) {
			$category = 'E';
		}

		$vat_holder->setVatCategory($category);

		if ($category != 'O') {
			$vat_holder->setVatRate(0);
		}

		if (in_array($category, array('E', 'AE', 'K', 'G', 'O'))) {
			$code = trim((string)$this->config->get('einvoicing_exempt_code'));
			$reason = trim((string)$this->config->get('einvoicing_exempt_reason'));

			if ($code !== '') {
				$vat_holder->setVatExemptionReasonCode($code);
			}

			if ($reason !== '' || $code === '') {
				$vat_holder->setVatExemptionReason($reason !== '' ? $reason : $this->text('text_default_exempt_reason'));
			}
		}
	}

	private function setPayment($invoice, $invoice_info) {
		$bank = $this->getBank($invoice_info);

		if (!$bank || $bank['iban'] === '') {
			return;
		}

		$means = (string)$this->config->get('einvoicing_means_code');

		$transfer = new \Einvoicing\Payments\Transfer();
		$transfer->setAccountId($bank['iban']);

		if ($bank['name'] !== '') {
			$transfer->setAccountName($bank['name']);
		}

		if ($bank['bic'] !== '') {
			$transfer->setProvider($bank['bic']);
		}

		$payment = new \Einvoicing\Payments\Payment();
		$payment->setMeansCode(preg_match('/^\d{1,3}$/', $means) ? $means : '30')->addTransfer($transfer);

		$invoice->addPayment($payment);
	}

	// Cuenta del emisor: la de la factura (bank_index), la predeterminada o la unica de Ajustes.
	private function getBank($invoice_info) {
		$banks = (array)$this->config->get('banks');

		if ($banks) {
			$index = $invoice_info['bank_index'] !== '' && isset($banks[(int)$invoice_info['bank_index']]) ? (int)$invoice_info['bank_index'] : (int)$this->config->get('bank_default');

			if (isset($banks[$index])) {
				return array(
					'name' => trim((string)$banks[$index]['name']),
					'iban' => strtoupper(preg_replace('/[^A-Za-z0-9]/', '', (string)$banks[$index]['iban'])),
					'bic'  => strtoupper(trim((string)$banks[$index]['bic']))
				);
			}
		}

		$iban = strtoupper(preg_replace('/[^A-Za-z0-9]/', '', (string)$this->config->get('iban')));

		return $iban === '' ? array() : array('name' => '', 'iban' => $iban, 'bic' => strtoupper(trim((string)$this->config->get('bic'))));
	}

	// ---- Partes --------------------------------------------------------------------------

	private function buildParties($invoice_info) {
		$seller_country = $this->getCountryIso2((int)$this->config->get('config_country_id'), '', 'ES');

		$address = html_entity_decode((string)$this->config->get('config_address'), ENT_QUOTES, 'UTF-8');
		$postcode = trim((string)$this->config->get('config_postcode'));
		$city = '';

		// Igual que Facturae del nucleo: el codigo postal puede ir dentro del texto de la direccion.
		if (preg_match('/(\d{5})\s*[\,\-]?\s*(.*)$/m', $address, $matches)) {
			if ($postcode === '') {
				$postcode = $matches[1];
			}

			$city = trim($matches[2]);
			$address = trim(str_replace($matches[0], '', $address));
		}

		$lines = array_values(array_filter(array_map('trim', preg_split('/\r\n|\r|\n/', $address)), 'strlen'));

		if ($city === '') {
			$zone = $this->db->query("SELECT name FROM `" . DB_PREFIX . "zone` WHERE zone_id = '" . (int)$this->config->get('config_zone_id') . "'");

			$city = $zone->num_rows ? $zone->row['name'] : '';
		}

		$seller_vat = $this->vatNumber($this->config->get('config_vat_id') ? $this->config->get('config_vat_id') : $this->config->get('config_nif'), $seller_country);

		if ($seller_vat === '') {
			throw new Exception($this->text('error_seller_vat'));
		}

		$seller_name = trim(html_entity_decode((string)$this->config->get('config_name'), ENT_QUOTES, 'UTF-8'));
		$seller_email = trim((string)$this->config->get('config_email'));

		$seller = new \Einvoicing\Party();
		$seller->setName($seller_name)
			->setVatNumber($seller_vat)
			->setAddress($lines ? array_slice($lines, 0, 3) : array())
			->setCity($city !== '' ? $city : null)
			->setPostalCode($postcode !== '' ? $postcode : null)
			->setCountry($seller_country);

		$endpoint = trim((string)$this->config->get('einvoicing_seller_endpoint'));

		if (preg_match('/^([A-Za-z0-9]{2,4}):(.+)$/', $endpoint, $parts)) {
			$seller->setElectronicAddress(new \Einvoicing\Identifier(trim($parts[2]), strtoupper($parts[1])));
		} else {
			$seller->setElectronicAddress($this->electronicAddress($seller_vat, $seller_country, $seller_email));
		}

		if ($seller_email !== '' || $this->config->get('config_telephone')) {
			$seller->setContactName($seller_name !== '' ? $seller_name : null)
				->setContactEmail($seller_email !== '' ? $seller_email : null)
				->setContactPhone(trim((string)$this->config->get('config_telephone')) !== '' ? trim((string)$this->config->get('config_telephone')) : null);
		}

		// Comprador: datos de facturacion de la factura y, si faltan, la ficha del cliente.
		$buyer_name = $invoice_info['payment_company'] ? $invoice_info['payment_company'] : $invoice_info['company'];

		if (!$buyer_name) {
			$buyer_name = trim($invoice_info['payment_firstname'] . ' ' . $invoice_info['payment_lastname']);
		}

		$buyer_nif = $invoice_info['payment_tax_id'] ? $invoice_info['payment_tax_id'] : $invoice_info['payment_company_id'];
		$buyer_address = trim($invoice_info['payment_address_1'] . ' ' . $invoice_info['payment_address_2']);
		$buyer_postcode = $invoice_info['payment_postcode'];
		$buyer_city = $invoice_info['payment_city'];
		$buyer_country_id = (int)$invoice_info['payment_country_id'];

		if ($invoice_info['customer_id']) {
			$this->load->model('sale/customer');

			$customer = $this->model_sale_customer->getCustomer($invoice_info['customer_id']);

			if ($customer) {
				if (!$buyer_nif && !empty($customer['nif'])) {
					$buyer_nif = $customer['nif'];
				}

				if (!$buyer_postcode && !empty($customer['postcode'])) {
					$buyer_postcode = $customer['postcode'];
					$buyer_city = $customer['city'];

					if (!empty($customer['address'])) {
						$buyer_address = $customer['address'];
					}

					if (!empty($customer['country_id'])) {
						$buyer_country_id = (int)$customer['country_id'];
					}
				}
			}
		}

		$buyer_name = trim(html_entity_decode((string)$buyer_name, ENT_QUOTES, 'UTF-8'));

		if ($buyer_name === '') {
			throw new Exception($this->text('error_buyer_name'));
		}

		$buyer_country = $this->getCountryIso2($buyer_country_id, $invoice_info['payment_iso_code_2'], $seller_country);
		$buyer_vat = $this->vatNumber($buyer_nif, $buyer_country);
		$buyer_email = trim((string)$invoice_info['email']);

		$buyer = new \Einvoicing\Party();
		$buyer->setName(utf8_substr($buyer_name, 0, 250))
			->setAddress($buyer_address !== '' ? array(utf8_substr(html_entity_decode($buyer_address, ENT_QUOTES, 'UTF-8'), 0, 250)) : array())
			->setCity(trim((string)$buyer_city) !== '' ? trim((string)$buyer_city) : null)
			->setPostalCode(trim((string)$buyer_postcode) !== '' ? trim((string)$buyer_postcode) : null)
			->setCountry($buyer_country);

		if ($buyer_vat !== '') {
			$buyer->setVatNumber($buyer_vat);
		}

		$buyer_address_id = $this->electronicAddress($buyer_vat, $buyer_country, $buyer_email);

		if (!$buyer_address_id) {
			throw new Exception($this->text('error_buyer_endpoint'));
		}

		$buyer->setElectronicAddress($buyer_address_id);

		if ($buyer_email !== '' || trim((string)$invoice_info['telephone']) !== '') {
			$buyer->setContactEmail($buyer_email !== '' ? $buyer_email : null)
				->setContactPhone(trim((string)$invoice_info['telephone']) !== '' ? trim((string)$invoice_info['telephone']) : null);
		}

		return array($seller, $buyer, $seller_country);
	}

	// NIF-IVA con el prefijo del pais (ESB12345678), que es como lo piden EN 16931 y Peppol.
	private function vatNumber($value, $country) {
		$value = strtoupper(preg_replace('/[^A-Za-z0-9]/', '', (string)$value));

		if ($value === '') {
			return '';
		}

		// Grecia usa EL como prefijo de IVA aunque su codigo ISO sea GR.
		$prefix = $country == 'GR' ? 'EL' : $country;

		return strncmp($value, $prefix, 2) === 0 ? $value : $prefix . $value;
	}

	// Direccion electronica (BT-34 / BT-49): el NIF-IVA con su esquema EAS si el pais lo tiene
	// en la lista de abajo; si no, el email (esquema EM). null si no hay ni lo uno ni lo otro.
	private function electronicAddress($vat, $country, $email) {
		$schemes = array(
			'AT' => '9914', 'BE' => '9925', 'DE' => '9930', 'ES' => '9920', 'FR' => '9957',
			'IT' => '9906', 'NL' => '9944', 'PT' => '9946', 'SE' => '9955'
		);

		if ($vat !== '' && isset($schemes[$country])) {
			return new \Einvoicing\Identifier($vat, $schemes[$country]);
		}

		if ($email !== '') {
			return new \Einvoicing\Identifier($email, 'EM');
		}

		return null;
	}

	private function getCountryIso2($country_id, $iso2, $default) {
		if ($iso2) {
			return strtoupper($iso2);
		}

		if ($country_id) {
			$query = $this->db->query("SELECT iso_code_2 FROM `" . DB_PREFIX . "country` WHERE country_id = '" . (int)$country_id . "'");

			if ($query->num_rows && $query->row['iso_code_2']) {
				return strtoupper($query->row['iso_code_2']);
			}
		}

		return $default;
	}

	// ---- IVA -----------------------------------------------------------------------------

	// Tipos de IVA en porcentaje definidos en Sistema > Localizacion > Impuestos.
	private function getVatRates() {
		$this->load->model('localisation/tax_rate');

		$rates = array();

		foreach ($this->model_localisation_tax_rate->getTaxRates() as $tax_rate) {
			if ($tax_rate['type'] == 'P' && (float)$tax_rate['rate'] > 0) {
				$rates[] = (float)$tax_rate['rate'];
			}
		}

		return $rates;
	}

	// Ajusta el tipo calculado (cuota / base) al tipo real mas cercano; null si no se parece a ninguno.
	private function snapVatRate($calculated, $rates) {
		$best = null;
		$best_distance = 0.6;

		foreach ($rates as $rate) {
			$distance = abs($rate - $calculated);

			if ($distance < $best_distance) {
				$best = $rate;
				$best_distance = $distance;
			}
		}

		return $best;
	}

	// ---- Textos --------------------------------------------------------------------------

	// Textos del modulo leidos aparte: $this->language->load() los mezclaria con los de la
	// pantalla que llama (sale/invoice, sale/delivery...) y pisaria claves como heading_title.
	public function label($key) {
		return $this->text($key);
	}

	private function text($key) {
		if ($this->texts === null) {
			$this->texts = array();

			$directory = 'en-gb';

			$query = $this->db->query("SELECT directory FROM `" . DB_PREFIX . "language` WHERE code = '" . $this->db->escape((string)$this->config->get('config_admin_language')) . "' LIMIT 1");

			if ($query->num_rows && $query->row['directory']) {
				$directory = $query->row['directory'];
			}

			foreach (array_unique(array('en-gb', $directory)) as $dir) {
				$file = DIR_LANGUAGE . $dir . '/einvoicing/einvoicing.php';

				if (is_file($file)) {
					$_ = array();

					require($file);

					$this->texts = array_merge($this->texts, $_);
				}
			}
		}

		return isset($this->texts[$key]) ? html_entity_decode($this->texts[$key], ENT_QUOTES, 'UTF-8') : $key;
	}
}
