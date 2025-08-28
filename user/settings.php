<?php
    require_once(__DIR__ . "/../bootstrap.php");
	include_once("../include/config.inc.php");
	include_once($_PJ_include_path . '/scripts.inc.php');
	include_once($_PJ_include_path . '/auth.inc.php');

	// Initialize variables from request
	$altered = $_REQUEST['altered'] ?? null;
	$id = $_REQUEST['id'] ?? null;
	$firstname = $_REQUEST['firstname'] ?? '';
	$lastname = $_REQUEST['lastname'] ?? '';
	$telephone = $_REQUEST['telephone'] ?? '';
	$facsimile = $_REQUEST['facsimile'] ?? '';
	$email = $_REQUEST['email'] ?? '';
	$password = $_REQUEST['password'] ?? '';
	$password_retype = $_REQUEST['password_retype'] ?? '';
	$theme_preference = $_REQUEST['theme_preference'] ?? null;
	$gids = $_REQUEST['gids'] ?? [];
	
	// Invoice settings
	$company_name = $_REQUEST['company_name'] ?? '';
	$company_address = $_REQUEST['company_address'] ?? '';
	$company_postal_code = $_REQUEST['company_postal_code'] ?? '';
	$company_city = $_REQUEST['company_city'] ?? '';
	$company_country = $_REQUEST['company_country'] ?? '';
	$tax_number = $_REQUEST['tax_number'] ?? '';
	$vat_number = $_REQUEST['vat_number'] ?? '';
	$bank_name = $_REQUEST['bank_name'] ?? '';
	$bank_iban = $_REQUEST['bank_iban'] ?? '';
	$bank_bic = $_REQUEST['bank_bic'] ?? '';
	$invoice_number_format = $_REQUEST['invoice_number_format'] ?? 'R-{YYYY}-{MM}-{###}';
	$default_vat_rate = $_REQUEST['default_vat_rate'] ?? '19.00';
	$payment_terms_days = $_REQUEST['payment_terms_days'] ?? '14';
	$payment_terms_text = $_REQUEST['payment_terms_text'] ?? '';

	$center_template	= "user";
	$center_title		= !empty($GLOBALS['_PJ_strings']['user']) ? $GLOBALS['_PJ_strings']['user'] : 'Benutzer';

	if(isset($altered)) {
		// Handle theme preference update separately via direct database update
		if($theme_preference && in_array($theme_preference, ['light', 'dark', 'system'])) {
			$db = new Database();
			$user_id = $_PJ_auth->giveValue('id');
			$theme_escaped = add_slashes($theme_preference);
			$query = "UPDATE " . $GLOBALS['_PJ_auth_table'] . " SET theme_preference = '$theme_escaped' WHERE id = " . intval($user_id);
			
			if($db->query($query)) {
				// Refresh auth data to reflect theme changes
				$_PJ_auth->fetchAdditionalData();
			}
		}
		
		// Handle invoice settings update
		$invoice_fields = [
			'company_name', 'company_address', 'company_postal_code', 'company_city', 'company_country',
			'tax_number', 'vat_number', 'bank_name', 'bank_iban', 'bank_bic',
			'invoice_number_format', 'default_vat_rate', 'payment_terms_days', 'payment_terms_text'
		];
		
		$invoice_updates = [];
		foreach($invoice_fields as $field) {
			if(isset($_REQUEST[$field])) {
				$value = add_slashes($_REQUEST[$field]);
				$invoice_updates[] = "$field = '$value'";
			}
		}
		
		if(!empty($invoice_updates)) {
			$db = new Database();
			$user_id = $_PJ_auth->giveValue('id');
			$update_query = "UPDATE " . $GLOBALS['_PJ_auth_table'] . " SET " . implode(', ', $invoice_updates) . " WHERE id = " . intval($user_id);
			$db->query($update_query);
		}
		
		// Handle regular user data updates
		// Use current user ID if no ID provided (normal user editing own settings)
		$data['id']					= $id ?: $_PJ_auth->giveValue('id');
		$data['mode']				= 'edit'; // Always edit mode for settings
		$data['firstname']			= $firstname;
		$data['lastname']			= $lastname;
		$data['telephone']			= $telephone;
		$data['facsimile']			= $facsimile;
		$data['email']				= $email;
		// on user edit no password is needed (no change)
		if(!empty($password)) {
			$data['password']			= $password;
			$data['password_retype']	= $password_retype;
		}
		$data['permissions']		= $_PJ_auth->giveValue('permissions');
		// Fix: Use gids from request instead of overwriting with old values (DRY function)
		if (!function_exists('processGroupIds')) {
			include_once($_PJ_include_path . '/functions.inc.php');
		}
		// Debug logging for group saving
		$GLOBALS['_PJ_debug'] = true;
		debugLog('SETTINGS_GROUPS_DEBUG', 'Raw gids from request: ' . print_r($gids, true));
		debugLog('SETTINGS_GROUPS_DEBUG', 'Current gids from auth: ' . $_PJ_auth->giveValue('gids'));
		$data['gids'] = processGroupIds($gids, $_PJ_auth->giveValue('gids'));
		debugLog('SETTINGS_GROUPS_DEBUG', 'Processed gids for save: ' . $data['gids']);
		$data['allow_nc']			= $_PJ_auth->giveValue('allow_nc');
		
		if($error = $_PJ_auth->save($data)) {
			$message = "<FONT COLOR=\"red\"><B>$error</B></FONT>";
		} else {
			$message = "<FONT COLOR=\"green\"><B>" . (!empty($GLOBALS['_PJ_strings']['settings_updated_successfully']) ? $GLOBALS['_PJ_strings']['settings_updated_successfully'] : 'Settings updated successfully.') . "</B></FONT>";
			
			// Refresh auth data to show changes immediately
			$_PJ_auth->fetchAdditionalData();
		}
	}
	$form_action = $GLOBALS['_PJ_own_user_script'];
	$user			= $_PJ_auth;
	$center_title	= $GLOBALS['_PJ_strings']['edit_user'];
	include("$_PJ_root/templates/edit.ihtml.php");

	include_once("$_PJ_include_path/degestiv.inc.php");
?>