				<div class="tab-pane" id="tab-einvoicing">
					<table class="table table-bordered table-striped table-hover info-page">
						<tr>
							<td class="col-sm-3"><?php echo $einvoicing_text_info_generated; ?></td>
							<td id="einvoicing-generated"><?php echo htmlspecialchars($einvoicing_generated, ENT_QUOTES, 'UTF-8'); ?></td>
						</tr>
						<tr>
							<td><?php echo $einvoicing_text_info_status; ?></td>
							<td id="einvoicing-status"><?php echo htmlspecialchars($einvoicing_status, ENT_QUOTES, 'UTF-8'); ?></td>
						</tr>
						<tr>
							<td><?php echo $einvoicing_text_info_profile; ?></td>
							<td id="einvoicing-profile"><?php echo htmlspecialchars($einvoicing_profile, ENT_QUOTES, 'UTF-8'); ?></td>
						</tr>
						<tr>
							<td><?php echo $einvoicing_text_info_notice; ?></td>
							<td id="einvoicing-notice"><?php echo $einvoicing_notice; ?></td>
						</tr>
					</table>
					<button type="button" id="button-einvoicing-generate" class="btn btn-primary"><svg class="bi" aria-hidden="true"><use href="view/image/bootstrap-icons.svg#arrow-repeat"/></svg> <?php echo $einvoicing_button_regenerate_invoice; ?></button>
					<a href="<?php echo $einvoicing_xml; ?>" id="button-einvoicing-xml" class="btn btn-default"<?php echo $einvoicing_has_xml ? '' : ' style="display:none;"'; ?>><svg class="bi" aria-hidden="true"><use href="view/image/bootstrap-icons.svg#download"/></svg> <?php echo $einvoicing_button_download_xml; ?></a>
				</div>
