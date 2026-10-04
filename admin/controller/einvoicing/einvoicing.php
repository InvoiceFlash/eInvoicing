<?php
class ControllerEinvoicingEinvoicing extends Controller {
	private $error = array();

	private $setting_keys = array(
		'einvoicing_active',
		'einvoicing_preset',
		'einvoicing_seller_endpoint',
		'einvoicing_payment_days',
		'einvoicing_means_code',
		'einvoicing_exempt_category',
		'einvoicing_exempt_code',
		'einvoicing_exempt_reason'
	);

	// Listado de facturas con XML generado y su estado de validacion.
	public function index() {
		$this->language->load('einvoicing/einvoicing');

		$this->document->setTitle($this->language->get('heading_title'));

		$this->load->model('einvoicing/einvoicing');

		$filter_status = isset($this->request->get['filter_status']) ? (string)$this->request->get['filter_status'] : '';
		$page = isset($this->request->get['page']) ? max(1, (int)$this->request->get['page']) : 1;
		$limit = (int)$this->config->get('config_admin_limit') ? (int)$this->config->get('config_admin_limit') : 20;

		$this->data['heading_title'] = $this->language->get('heading_title');

		foreach (array('text_no_results', 'text_all', 'text_confirm_regenerate', 'text_active_note', 'text_validation_note', 'column_invoice', 'column_type', 'column_customer', 'column_date', 'column_total', 'column_profile', 'column_status', 'column_action', 'button_setting', 'button_regenerate', 'button_xml', 'button_filter') as $key) {
			$this->data[$key] = $this->language->get($key);
		}

		$this->data['statuses'] = array(
			ModelEinvoicingEinvoicing::STATUS_VALID   => $this->language->get('text_status_valid'),
			ModelEinvoicingEinvoicing::STATUS_INVALID => $this->language->get('text_status_invalid')
		);

		$this->data['filter_status'] = $filter_status;
		$this->data['can_modify'] = $this->user->hasPermission('modify', 'einvoicing/einvoicing');

		$this->data['warnings'] = array();

		$requirements = $this->model_einvoicing_einvoicing->requirementsError();

		if ($requirements) {
			$this->data['warnings'][] = $requirements;
		}

		$this->data['active'] = $this->model_einvoicing_einvoicing->isActive();

		if (isset($this->session->data['success'])) {
			$this->data['success'] = $this->session->data['success'];

			unset($this->session->data['success']);
		} else {
			$this->data['success'] = '';
		}

		$this->data['error_warning'] = '';
		$this->data['breadcrumbs'] = $this->getBreadcrumbs();

		$this->data['setting'] = $this->url->link('einvoicing/einvoicing/setting', 'token=' . $this->session->data['token'], 'SSL');
		$this->data['filter_action'] = str_replace('&amp;', '&', $this->url->link('einvoicing/einvoicing', 'token=' . $this->session->data['token'], 'SSL'));
		$this->data['regenerate_url'] = str_replace('&amp;', '&', $this->url->link('einvoicing/einvoicing/regenerate', 'token=' . $this->session->data['token'], 'SSL'));

		$filter = array(
			'filter_status' => $filter_status,
			'start'         => ($page - 1) * $limit,
			'limit'         => $limit
		);

		$presets = $this->model_einvoicing_einvoicing->getPresets();

		$this->data['records'] = array();

		foreach ($this->model_einvoicing_einvoicing->getRecords($filter) as $record) {
			$this->data['records'][] = array(
				'invoice_id' => $record['invoice_id'],
				'number'     => $record['number'],
				'type'       => $this->language->get($record['type_code'] == 381 ? 'text_credit_note' : 'text_invoice'),
				'customer'   => $record['customer'],
				'date'       => $record['issue_date'] ? date($this->language->get('date_format_short'), strtotime($record['issue_date'])) : '',
				'total'      => $this->currency->format($record['total'], $this->config->get('config_currency'), '', true, true),
				'profile'    => isset($presets[$record['preset']]) ? $presets[$record['preset']]['name'] : $record['preset'],
				'status'     => $record['status'],
				'status_text' => isset($this->data['statuses'][$record['status']]) ? $this->data['statuses'][$record['status']] : $record['status'],
				'message'    => $record['status'] == ModelEinvoicingEinvoicing::STATUS_VALID ? '' : nl2br(htmlspecialchars($record['message'], ENT_QUOTES, 'UTF-8')),
				'has_xml'    => $record['xml'] !== '',
				'invoice'    => $this->url->link('sale/invoice/info', 'token=' . $this->session->data['token'] . '&invoice_id=' . $record['invoice_id'], 'SSL'),
				'xml'        => $this->url->link('einvoicing/einvoicing/xml', 'token=' . $this->session->data['token'] . '&invoice_id=' . $record['invoice_id'], 'SSL')
			);
		}

		$pagination = new Pagination();
		$pagination->total = $this->model_einvoicing_einvoicing->getTotalRecords($filter);
		$pagination->page = $page;
		$pagination->limit = $limit;
		$pagination->text = $this->language->get('text_pagination');
		$pagination->url = $this->url->link('einvoicing/einvoicing', 'token=' . $this->session->data['token'] . ($filter_status !== '' ? '&filter_status=' . urlencode($filter_status) : '') . '&page={page}', 'SSL');

		$this->data['pagination'] = $pagination->render();

		$this->template = 'einvoicing/einvoicing_list.tpl';
		$this->children = array(
			'common/header',
			'common/footer'
		);

		$this->response->setOutput($this->render());
	}

	public function setting() {
		$this->language->load('einvoicing/einvoicing');

		$this->document->setTitle($this->language->get('heading_setting'));

		$this->load->model('setting/setting');
		$this->load->model('einvoicing/einvoicing');

		if (($this->request->server['REQUEST_METHOD'] == 'POST') && $this->validateSetting()) {
			$data = array();

			foreach ($this->setting_keys as $key) {
				$data[$key] = isset($this->request->post[$key]) ? trim((string)$this->request->post[$key]) : '';
			}

			$data['einvoicing_payment_days'] = (string)max(0, (int)$data['einvoicing_payment_days']);

			$this->model_setting_setting->editSetting('einvoicing', $data);

			$this->session->data['success'] = $this->language->get('text_success_setting');

			$this->redirect($this->url->link('einvoicing/einvoicing', 'token=' . $this->session->data['token'], 'SSL'));
		}

		$this->data['heading_title'] = $this->language->get('heading_setting');

		foreach (array('text_yes', 'text_no', 'text_active_note', 'text_validation_note', 'text_requirements_ok', 'text_preset_note', 'text_endpoint_note', 'text_days_note', 'text_exempt_note', 'entry_active', 'entry_preset', 'entry_endpoint', 'entry_payment_days', 'entry_means_code', 'entry_exempt_category', 'entry_exempt_code', 'entry_exempt_reason', 'button_save', 'button_cancel') as $key) {
			$this->data[$key] = $this->language->get($key);
		}

		$this->data['presets'] = array();

		foreach ($this->model_einvoicing_einvoicing->getPresets() as $code => $preset) {
			$this->data['presets'][$code] = $preset['name'];
		}

		$this->data['means'] = array();

		foreach (array('30', '58', '42', '10', '48', '49') as $code) {
			$this->data['means'][$code] = $this->language->get('text_means_' . $code);
		}

		$this->data['categories'] = array();

		foreach (array('E', 'Z', 'AE', 'K', 'G', 'O') as $code) {
			$this->data['categories'][$code] = $this->language->get('text_cat_' . strtolower($code));
		}

		$this->data['requirements'] = $this->model_einvoicing_einvoicing->requirementsError();

		$this->data['error_warning'] = isset($this->error['warning']) ? $this->error['warning'] : '';
		$this->data['error_endpoint'] = isset($this->error['endpoint']) ? $this->error['endpoint'] : '';

		$this->data['breadcrumbs'] = $this->getBreadcrumbs();
		$this->data['breadcrumbs'][] = array(
			'text'      => $this->language->get('heading_setting'),
			'href'      => $this->url->link('einvoicing/einvoicing/setting', 'token=' . $this->session->data['token'], 'SSL'),
			'separator' => ' :: '
		);

		$this->data['action'] = $this->url->link('einvoicing/einvoicing/setting', 'token=' . $this->session->data['token'], 'SSL');
		$this->data['cancel'] = $this->url->link('einvoicing/einvoicing', 'token=' . $this->session->data['token'], 'SSL');

		$defaults = array(
			'einvoicing_preset'          => 'peppol',
			'einvoicing_payment_days'    => '30',
			'einvoicing_means_code'      => '30',
			'einvoicing_exempt_category' => 'E'
		);

		foreach ($this->setting_keys as $key) {
			if (isset($this->request->post[$key])) {
				$this->data[$key] = $this->request->post[$key];
			} elseif ($this->config->get($key) !== null) {
				$this->data[$key] = $this->config->get($key);
			} else {
				$this->data[$key] = isset($defaults[$key]) ? $defaults[$key] : '';
			}
		}

		$this->template = 'einvoicing/einvoicing_setting.tpl';
		$this->children = array(
			'common/header',
			'common/footer'
		);

		$this->response->setOutput($this->render());
	}

	// Boton "Regenerar" del listado (POST invoice_id).
	public function regenerate() {
		$this->language->load('einvoicing/einvoicing');

		$json = array();

		if (!$this->user->hasPermission('modify', 'einvoicing/einvoicing')) {
			$json['error'] = $this->language->get('error_permission');
		} else {
			$this->load->model('einvoicing/einvoicing');

			$invoice_id = isset($this->request->post['invoice_id']) ? (int)$this->request->post['invoice_id'] : 0;

			$result = $this->model_einvoicing_einvoicing->generate($invoice_id);

			if ($result['success']) {
				$json['success'] = $result['message'];
			} else {
				$json['error'] = htmlspecialchars($result['message'], ENT_QUOTES, 'UTF-8');
			}
		}

		$this->response->addHeader('Content-Type: application/json');
		$this->response->setOutput(json_encode($json));
	}

	// Boton "Generar XML" de la pestana de la ficha de factura (sale/invoice). Genera aunque el
	// modulo no este activo para las facturas nuevas: es una peticion explicita.
	public function invoiceGenerate() {
		$this->load->language('sale/invoice');
		$this->language->load('einvoicing/einvoicing');

		$json = array();

		if (!$this->user->hasPermission('modify', 'sale/invoice')) {
			$json['error'] = $this->language->get('error_permission');
		} else {
			$invoice_id = isset($this->request->get['invoice_id']) ? (int)$this->request->get['invoice_id'] : 0;

			$this->load->model('einvoicing/einvoicing');

			$result = $this->model_einvoicing_einvoicing->generate($invoice_id);

			$json['success'] = $result['success'];
			$json['message'] = htmlspecialchars($result['message'], ENT_QUOTES, 'UTF-8');

			$info = $this->model_einvoicing_einvoicing->getInfo($invoice_id);

			$json['status'] = htmlspecialchars($info['status'], ENT_QUOTES, 'UTF-8');
			$json['generated'] = htmlspecialchars($info['generated'], ENT_QUOTES, 'UTF-8');
			$json['profile'] = htmlspecialchars($info['profile'], ENT_QUOTES, 'UTF-8');
			$json['notice'] = $info['valid'] ? '' : nl2br(htmlspecialchars($info['notice'], ENT_QUOTES, 'UTF-8'));
			$json['has_xml'] = $info['has_xml'];
		}

		$this->response->addHeader('Content-Type: application/json');
		$this->response->setOutput(json_encode($json));
	}

	// Descarga del XML UBL generado.
	public function xml() {
		$this->load->model('einvoicing/einvoicing');

		$invoice_id = isset($this->request->get['invoice_id']) ? (int)$this->request->get['invoice_id'] : 0;

		$record = ($this->user->hasPermission('access', 'einvoicing/einvoicing') || $this->user->hasPermission('access', 'sale/invoice')) ? $this->model_einvoicing_einvoicing->getRecord($invoice_id) : array();

		if (!$record || $record['xml'] === '') {
			$this->redirect($this->url->link('einvoicing/einvoicing', 'token=' . $this->session->data['token'], 'SSL'));
		}

		$filename = 'einvoice_' . preg_replace('/[^A-Za-z0-9_-]/', '', $record['number']) . '.xml';

		$this->response->addHeader('Content-Type: application/xml; charset=utf-8');
		$this->response->addHeader('Content-Disposition: attachment; filename="' . $filename . '"');
		$this->response->setOutput($record['xml']);
	}

	private function validateSetting() {
		if (!$this->user->hasPermission('modify', 'einvoicing/einvoicing')) {
			$this->error['warning'] = $this->language->get('error_permission');
		}

		$endpoint = isset($this->request->post['einvoicing_seller_endpoint']) ? trim((string)$this->request->post['einvoicing_seller_endpoint']) : '';

		if ($endpoint !== '' && !preg_match('/^[A-Za-z0-9]{2,4}:.+$/', $endpoint)) {
			$this->error['endpoint'] = $this->language->get('error_endpoint_format');
		}

		if ($this->error && !isset($this->error['warning'])) {
			$this->error['warning'] = $this->language->get('error_warning');
		}

		return !$this->error;
	}

	private function getBreadcrumbs() {
		return array(
			array(
				'text'      => $this->language->get('text_home'),
				'href'      => $this->url->link('common/home', 'token=' . $this->session->data['token'], 'SSL'),
				'separator' => false
			),
			array(
				'text'      => $this->language->get('heading_title'),
				'href'      => $this->url->link('einvoicing/einvoicing', 'token=' . $this->session->data['token'], 'SSL'),
				'separator' => ' :: '
			)
		);
	}
}
