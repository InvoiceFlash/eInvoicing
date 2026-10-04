<?php echo $header; ?>

<?php include(DIR_TEMPLATE . 'common/template-header.tpl'); ?>

<div class="card page-card">

	<div class="card-header clearfix">
		<div class="float-start h2"><i class="fa fa-cog"></i> <?php echo $heading_title; ?></div>
		<div class="float-end">
			<button type="submit" form="form-einvoicing" class="btn btn-primary"><i class="fa fa-save"></i> <?php echo $button_save; ?></button>
			<a href="<?php echo $cancel; ?>" class="btn btn-default"><i class="fa fa-reply"></i> <?php echo $button_cancel; ?></a>
		</div>
	</div>

	<div class="card-body">
		<?php if ($requirements) { ?>
		<div class="alert alert-warning"><?php echo $requirements; ?></div>
		<?php } else { ?>
		<div class="alert alert-success"><?php echo $text_requirements_ok; ?></div>
		<?php } ?>

		<form action="<?php echo $action; ?>" method="post" enctype="multipart/form-data" id="form-einvoicing">
			<div class="form-group row">
				<label class="col-sm-3 col-form-label text-sm-end pb-0" for="input-active"><?php echo $entry_active; ?></label>
				<div class="col-sm-9">
					<select name="einvoicing_active" id="input-active" class="form-select">
						<option value="0"<?php echo !$einvoicing_active ? ' selected="selected"' : ''; ?>><?php echo $text_no; ?></option>
						<option value="1"<?php echo $einvoicing_active ? ' selected="selected"' : ''; ?>><?php echo $text_yes; ?></option>
					</select>
					<small class="text-muted"><?php echo $text_active_note; ?></small>
				</div>
			</div>

			<div class="form-group row">
				<label class="col-sm-3 col-form-label text-sm-end pb-0" for="input-preset"><?php echo $entry_preset; ?></label>
				<div class="col-sm-9">
					<select name="einvoicing_preset" id="input-preset" class="form-select">
						<?php foreach ($presets as $preset_code => $preset_name) { ?>
						<option value="<?php echo $preset_code; ?>"<?php echo ($einvoicing_preset == $preset_code) ? ' selected="selected"' : ''; ?>><?php echo $preset_name; ?></option>
						<?php } ?>
					</select>
					<small class="text-muted"><?php echo $text_preset_note; ?></small>
				</div>
			</div>

			<div class="form-group row">
				<label class="col-sm-3 col-form-label text-sm-end pb-0" for="input-endpoint"><?php echo $entry_endpoint; ?></label>
				<div class="col-sm-9">
					<input type="text" name="einvoicing_seller_endpoint" id="input-endpoint" value="<?php echo htmlspecialchars($einvoicing_seller_endpoint, ENT_QUOTES, 'UTF-8'); ?>" class="form-control" />
					<?php if ($error_endpoint) { ?>
					<div class="text-danger"><?php echo $error_endpoint; ?></div>
					<?php } ?>
					<small class="text-muted"><?php echo $text_endpoint_note; ?></small>
				</div>
			</div>

			<div class="form-group row">
				<label class="col-sm-3 col-form-label text-sm-end pb-0" for="input-days"><?php echo $entry_payment_days; ?></label>
				<div class="col-sm-9">
					<input type="number" min="0" name="einvoicing_payment_days" id="input-days" value="<?php echo (int)$einvoicing_payment_days; ?>" class="form-control" />
					<small class="text-muted"><?php echo $text_days_note; ?></small>
				</div>
			</div>

			<div class="form-group row">
				<label class="col-sm-3 col-form-label text-sm-end pb-0" for="input-means"><?php echo $entry_means_code; ?></label>
				<div class="col-sm-9">
					<select name="einvoicing_means_code" id="input-means" class="form-select">
						<?php foreach ($means as $means_code => $means_name) { ?>
						<option value="<?php echo $means_code; ?>"<?php echo ($einvoicing_means_code == $means_code) ? ' selected="selected"' : ''; ?>><?php echo $means_name; ?></option>
						<?php } ?>
					</select>
				</div>
			</div>

			<div class="form-group row">
				<label class="col-sm-3 col-form-label text-sm-end pb-0" for="input-exempt-category"><?php echo $entry_exempt_category; ?></label>
				<div class="col-sm-9">
					<select name="einvoicing_exempt_category" id="input-exempt-category" class="form-select">
						<?php foreach ($categories as $category_code => $category_name) { ?>
						<option value="<?php echo $category_code; ?>"<?php echo ($einvoicing_exempt_category == $category_code) ? ' selected="selected"' : ''; ?>><?php echo $category_name; ?></option>
						<?php } ?>
					</select>
					<small class="text-muted"><?php echo $text_exempt_note; ?></small>
				</div>
			</div>

			<div class="form-group row">
				<label class="col-sm-3 col-form-label text-sm-end pb-0" for="input-exempt-code"><?php echo $entry_exempt_code; ?></label>
				<div class="col-sm-9">
					<input type="text" name="einvoicing_exempt_code" id="input-exempt-code" value="<?php echo htmlspecialchars($einvoicing_exempt_code, ENT_QUOTES, 'UTF-8'); ?>" maxlength="40" class="form-control" />
				</div>
			</div>

			<div class="form-group row">
				<label class="col-sm-3 col-form-label text-sm-end pb-0" for="input-exempt-reason"><?php echo $entry_exempt_reason; ?></label>
				<div class="col-sm-9">
					<input type="text" name="einvoicing_exempt_reason" id="input-exempt-reason" value="<?php echo htmlspecialchars($einvoicing_exempt_reason, ENT_QUOTES, 'UTF-8'); ?>" maxlength="200" class="form-control" />
				</div>
			</div>

			<p class="text-muted"><small><?php echo $text_validation_note; ?></small></p>
		</form>
	</div>
</div>

<?php echo $footer; ?>
