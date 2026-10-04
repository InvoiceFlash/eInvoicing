<?php echo $header; ?>

<?php include(DIR_TEMPLATE . 'common/template-header.tpl'); ?>

<?php foreach ($warnings as $einvoicing_warning) { ?>
<div class="alert alert-warning"><?php echo $einvoicing_warning; ?></div>
<?php } ?>

<div class="card page-card">

	<div class="card-header clearfix">
		<div class="float-start h2"><i class="fa fa-file-code-o"></i> <?php echo $heading_title; ?></div>
		<div class="float-end">
			<a href="<?php echo $setting; ?>" class="btn btn-primary"><i class="fa fa-cog"></i> <?php echo $button_setting; ?></a>
		</div>
	</div>

	<div class="card-body">
		<?php if ($active) { ?>
		<p class="text-muted"><?php echo $text_active_note; ?></p>
		<?php } ?>
		<p class="text-muted"><small><?php echo $text_validation_note; ?></small></p>
		<div id="einvoicing-alert"></div>
		<div class="table-responsive">
			<table class="table table-bordered table-striped table-hover">
				<thead>
					<tr>
						<td><?php echo $column_invoice; ?></td>
						<td><?php echo $column_type; ?></td>
						<td><?php echo $column_customer; ?></td>
						<td><?php echo $column_date; ?></td>
						<td class="text-end"><?php echo $column_total; ?></td>
						<td><?php echo $column_profile; ?></td>
						<td><?php echo $column_status; ?></td>
						<td class="text-end"><?php echo $column_action; ?></td>
					</tr>
					<tr id="filter">
						<td colspan="6"></td>
						<td>
							<select name="filter_status" class="form-select">
								<option value=""><?php echo $text_all; ?></option>
								<?php foreach ($statuses as $status_code => $status_name) { ?>
								<option value="<?php echo $status_code; ?>"<?php echo ($status_code == $filter_status) ? ' selected="selected"' : ''; ?>><?php echo $status_name; ?></option>
								<?php } ?>
							</select>
						</td>
						<td class="text-end"><button type="button" id="button-einvoicing-filter" class="btn btn-default"><i class="fa fa-filter"></i> <?php echo $button_filter; ?></button></td>
					</tr>
				</thead>
				<tbody>
					<?php if ($records) { ?>
					<?php foreach ($records as $record) { ?>
					<tr>
						<td><a href="<?php echo $record['invoice']; ?>"><?php echo htmlspecialchars($record['number'], ENT_QUOTES, 'UTF-8'); ?></a></td>
						<td><?php echo $record['type']; ?></td>
						<td><?php echo htmlspecialchars($record['customer'], ENT_QUOTES, 'UTF-8'); ?></td>
						<td><?php echo $record['date']; ?></td>
						<td class="text-end"><?php echo $record['total']; ?></td>
						<td><small><?php echo htmlspecialchars($record['profile'], ENT_QUOTES, 'UTF-8'); ?></small></td>
						<td>
							<span class="badge <?php echo ($record['status'] == 'valid') ? 'bg-success text-white' : 'bg-danger text-white'; ?>"><?php echo $record['status_text']; ?></span>
							<?php if ($record['message']) { ?>
							<div><small><?php echo $record['message']; ?></small></div>
							<?php } ?>
						</td>
						<td class="text-end" style="white-space:nowrap;">
							<?php if ($can_modify) { ?>
							<button type="button" class="btn btn-primary einvoicing-regenerate" data-invoice-id="<?php echo $record['invoice_id']; ?>"><i class="fa fa-refresh"></i> <?php echo $button_regenerate; ?></button>
							<?php } ?>
							<?php if ($record['has_xml']) { ?>
							<a href="<?php echo $record['xml']; ?>" class="btn btn-default"><i class="fa fa-download"></i> <?php echo $button_xml; ?></a>
							<?php } ?>
						</td>
					</tr>
					<?php } ?>
					<?php } else { ?>
					<tr>
						<td class="text-center" colspan="8"><?php echo $text_no_results; ?></td>
					</tr>
					<?php } ?>
				</tbody>
			</table>
		</div>
		<div class="pagination"><?php echo $pagination; ?></div>
	</div>
</div>

<script type="text/javascript"><!--
$('#button-einvoicing-filter').on('click', function() {
	var url = '<?php echo $filter_action; ?>';
	var status = $('select[name=\'filter_status\']').val();

	if (status) {
		url += '&filter_status=' + encodeURIComponent(status);
	}

	location = url;
});

$('.einvoicing-regenerate').on('click', function() {
	if (!confirm('<?php echo addslashes(html_entity_decode($text_confirm_regenerate, ENT_QUOTES, 'UTF-8')); ?>')) {
		return;
	}

	var $button = $(this);

	$.ajax({
		url: '<?php echo $regenerate_url; ?>',
		type: 'post',
		data: {invoice_id: $button.data('invoice-id')},
		dataType: 'json',
		beforeSend: function() {
			$button.prop('disabled', true);
		},
		complete: function() {
			$button.prop('disabled', false);
		},
		success: function(json) {
			var $alert = $('<div class="alert"></div>').addClass(json['success'] ? 'alert-success' : 'alert-danger').html(json['success'] ? json['success'] : json['error']);

			$('#einvoicing-alert').empty().append($alert);

			setTimeout(function() { location.reload(); }, json['success'] ? 1000 : 2500);
		},
		error: function(xhr, ajaxOptions, thrownError) {
			$('#einvoicing-alert').html('<div class="alert alert-danger"></div>').find('.alert').text(thrownError);
		}
	});
});
//--></script>

<?php echo $footer; ?>
