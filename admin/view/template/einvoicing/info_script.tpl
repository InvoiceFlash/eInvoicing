<script>
$('#button-einvoicing-generate').on('click',function(e){
	e.preventDefault();

	var button = $(this);

	button.prop('disabled', true);
	button.find('svg.bi').addClass('bi-spin');

	$.ajax({
		url:'index.php?route=einvoicing/einvoicing/invoiceGenerate&token=<?php echo $token; ?>&invoice_id=<?php echo $invoice_id; ?>',
		type:'get',
		dataType:'json',
		success:function(json){
			if(json['error']){
				alertMessage('danger',json['error']);
			} else {
				$('#einvoicing-generated').html(json['generated']);
				$('#einvoicing-status').html(json['status']);
				$('#einvoicing-profile').html(json['profile']);
				$('#einvoicing-notice').html(json['notice']);
				$('#button-einvoicing-xml').toggle(!!json['has_xml']);

				alertMessage(json['success'] ? 'success' : 'danger',json['message']);
			}
		},
		error:function(request){
			console.log("ajax call went wrong:" + request.responseText);
		},
		complete:function(){
			button.prop('disabled', false);
			button.find('svg.bi').removeClass('bi-spin');
		}
	});
});
</script>
