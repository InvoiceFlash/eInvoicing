<?php
// Heading
$_['heading_title']              = 'E-invoicing (EN 16931)';
$_['heading_setting']            = 'E-invoicing settings';

// Text
$_['text_home']                  = 'Home';
$_['text_success_setting']       = 'E-invoicing settings saved.';
$_['text_generated_ok']          = 'XML generated and validated.';
$_['text_no_results']            = 'No e-invoice has been generated yet.';
$_['text_all']                   = 'All';
$_['text_yes']                   = 'Yes';
$_['text_no']                    = 'No';
$_['text_invoice']               = 'Invoice';
$_['text_credit_note']           = 'Credit note';
$_['text_status_valid']          = 'Valid';
$_['text_status_invalid']        = 'With errors';
$_['text_not_generated']         = 'Not generated';
$_['text_discount']              = 'Discount';
$_['text_default_exempt_reason'] = 'VAT exempt operation';
$_['text_active_note']           = 'With the module active, the XML of every new invoice is generated and validated when the invoice is created. It is not sent to any network: download it from here or from the invoice page.';
$_['text_requirements_ok']       = 'Server ready to generate e-invoices.';
$_['text_validation_note']       = 'Validation checks the EN 16931 business rules and those of the chosen profile (josemmo/einvoicing library). It does not replace the official Peppol/KoSIT Schematron validator before sending to a customer or a public body.';
$_['text_confirm_regenerate']    = 'Generate the XML of this invoice again?';
$_['text_preset_note']           = 'Peppol BIS Billing 3.0 is the profile accepted by the Peppol network and most countries.';
$_['text_endpoint_note']         = 'Format SCHEME:value, for example 9920:ESB12345678. If empty, the seller VAT number is used with the scheme of its country or, if there is none, the email from Settings.';
$_['text_days_note']             = 'Due date = invoice date + these days.';
$_['text_means_30']              = '30 - Credit transfer';
$_['text_means_58']              = '58 - SEPA credit transfer';
$_['text_means_42']              = '42 - Payment to bank account';
$_['text_means_10']              = '10 - Cash';
$_['text_means_48']              = '48 - Bank card';
$_['text_means_49']              = '49 - Direct debit';
$_['text_cat_e']                 = 'E - Exempt';
$_['text_cat_z']                 = 'Z - Zero rated';
$_['text_cat_ae']                = 'AE - Reverse charge';
$_['text_cat_k']                 = 'K - Intra-community supply';
$_['text_cat_g']                 = 'G - Export';
$_['text_cat_o']                 = 'O - Not subject to VAT';
$_['text_exempt_note']           = 'Applied to lines without VAT. The exemption reason is mandatory for categories E, AE, K, G and O: a VATEX code (for example VATEX-EU-132) or a text, or both.';

// Column
$_['column_invoice']             = 'Invoice';
$_['column_type']                = 'Type';
$_['column_customer']            = 'Customer';
$_['column_date']                = 'Date';
$_['column_total']               = 'Total';
$_['column_profile']             = 'Profile';
$_['column_status']              = 'Status';
$_['column_action']              = 'Action';

// Entry
$_['entry_active']               = 'Generate when the invoice is created';
$_['entry_preset']               = 'Profile (CIUS)';
$_['entry_endpoint']             = 'Seller electronic address';
$_['entry_payment_days']         = 'Due days';
$_['entry_means_code']           = 'Payment means';
$_['entry_exempt_category']      = 'VAT category without VAT';
$_['entry_exempt_code']          = 'Exemption code (VATEX)';
$_['entry_exempt_reason']        = 'Exemption text';
$_['entry_status']               = 'Status';

// Button
$_['button_setting']             = 'Settings';
$_['button_save']                = 'Save';
$_['button_cancel']              = 'Cancel';
$_['button_regenerate']          = 'Regenerate';
$_['button_xml']                 = 'XML';
$_['button_filter']              = 'Filter';

// Error
$_['error_warning']              = 'Please check the marked form fields.';
$_['error_permission']           = 'You do not have permission to modify e-invoicing.';
$_['error_php']                  = 'E-invoicing needs PHP 7.1 or later (this server has %s).';
$_['error_dom']                  = 'E-invoicing needs the PHP &quot;dom&quot; extension (enable it in php.ini and restart the web server).';
$_['error_library']              = 'The module libraries are missing in system/vendor/einvoicing.';
$_['error_invoice_not_found']    = 'Invoice not found.';
$_['error_no_products']          = 'The invoice has no lines.';
$_['error_line_quantity']        = 'Line %s has zero quantity.';
$_['error_tax_rate']             = 'Line %s carries a %s %% VAT that does not match any rate in System > Localisation > Taxes.';
$_['error_total_mismatch']       = 'The invoice total (%s) does not match the one resulting from lines and VAT (%s).';
$_['error_seller_vat']           = 'The seller VAT number is missing in System > Settings.';
$_['error_buyer_name']           = 'The customer has no name or company name.';
$_['error_buyer_endpoint']       = 'The customer has neither a VAT number from a country with a Peppol scheme nor an email: there is no buyer electronic address.';
$_['error_not_generated']        = 'This invoice has no XML.';
$_['error_endpoint_format']      = 'Write the electronic address as SCHEME:value, for example 9920:ESB12345678.';

// Invoice page tab
$_['tab_einvoicing']             = 'E-invoice';
$_['text_info_generated']        = 'Generated on';
$_['text_info_status']           = 'Status';
$_['text_info_profile']          = 'Profile';
$_['text_info_notice']           = 'Message';
$_['button_regenerate_invoice']  = 'Generate XML';
$_['button_download_xml']        = 'Download XML';
