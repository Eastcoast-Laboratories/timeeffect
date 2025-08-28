<!-- user/form.ihtml - START -->
<?php
	$gids = array();
	$id = isset($_REQUEST['id']) ? $_REQUEST['id'] : '';
	$username = isset($_REQUEST['login']) ? $_REQUEST['login'] : '';
	$login = isset($_REQUEST['login']) ? $_REQUEST['login'] : '';
	$own = isset($_REQUEST['own']) ? $_REQUEST['own'] : '';
	$permissions = array();
	if(isset($permissions) and !is_array($permissions)) {
			$permissions = array();
		}
	if(empty($form_action)) {
			$form_action = $GLOBALS['_PJ_user_script'];
		}
	/*
	if(!$_PJ_auth->checkPermission('admin')) {
		$ro_firstname	= ' READONLY';
		$ro_lastname	= ' READONLY';
		$ro_css_firstname	= 'RO';
		$ro_css_lastname	= 'RO';
	}
	*/
	
	// Explicitly clear readonly variables to ensure firstname/lastname are editable
	$ro_firstname = '';
	$ro_lastname = '';
	$ro_css_firstname = '';
	$ro_css_lastname = '';
	if(isset($user) && is_object($user) && $user->giveValue('id')) {
		// Edit existing user - populate with user data
		$ro_username		= ' READONLY';
		$ro_css_username	= 'RO';
		$id					= $user->giveValue('id');
		$firstname			= $user->giveValue('firstname');
		$lastname			= $user->giveValue('lastname');
		$username			= $user->giveValue('username');
		$email				= $user->giveValue('email');
		$telephone			= $user->giveValue('telephone');
		$facsimile			= $user->giveValue('facsimile');
		$allow_nc			= $user->giveValue('allow_nc');
		$permissions		= explode(',', $user->giveValue('permissions'));
		$gids				= explode(',', $user->giveValue('gids'));
		// Password fields will be handled by mode-based validation
	} else {
		// New user - initialize empty values (don't use current user data)
		$ro_username		= '';
		$ro_css_username	= '';
		$id					= '';
		$firstname			= '';
		$lastname			= '';
		$email				= '';
		$telephone			= '';
		$facsimile			= '';
		$allow_nc			= '';
		$permissions		= array();
		$gids				= array(); // No group membership for new users
	}
	if($username == '') {
		$username = $login;
	}
	$a_permissions	= $_PJ_auth->permissions;
	$a_gids			= $_PJ_auth->gids;
	if(!$id && (!is_array($a_gids) || !count($a_gids))) {
		print '<br><center><span class="errorMessage"><b>' . $GLOBALS['_PJ_strings']['missing_groups'] . '</b></center></span>';
		return;
	}
	include($GLOBALS['_PJ_root'] . '/templates/user/options/edit.ihtml.php');
	?>
<FORM ACTION="<?= $form_action; ?>" METHOD="<?= $_PJ_form_method; ?>">
<INPUT TYPE="hidden" NAME="uid" VALUE="<?php if(isset($id)) echo $id; ?>">
<INPUT TYPE="hidden" NAME="mode" VALUE="<?php echo empty($id) ? 'new' : 'edit'; ?>">
<INPUT TYPE="hidden" NAME="own" VALUE="<?php if(isset($own)) echo $own; ?>">
<INPUT TYPE="hidden" NAME="edit" VALUE="1">
<INPUT TYPE="hidden" NAME="altered" VALUE="1">
	<CENTER>
		<TABLE WIDTH="90%" BORDER="0" CELLPADDING="3" CELLSPACING="0">
			<TR>
					<TD CLASS="content">
						<TABLE BORDER="0" CELLPADDING="3" CELLSPACING="0">
							<TR>
									<TD CLASS="Error" COLSPAN="2"><?php if(isset($message)) echo $message; ?></TD>
									</TR><TR>
									<TD CLASS="FormFieldName"><?php if(!empty($GLOBALS['_PJ_strings']['username'])) echo $GLOBALS['_PJ_strings']['username'] ?>*:</TD>
										<TD CLASS="FormField"><INPUT CLASS="FormField<?php if(isset($ro_css_username)) echo $ro_css_username; ?>" NAME="login" VALUE="<?php if(isset($username)) echo $username; ?>"<?php if(isset($ro_username)) echo $ro_username; ?>></TD>
									</TR>
				<?php if(isset($user) && is_object($user) && $user->giveValue('id')): ?>
					<!-- Existing user: Show password change button -->
					<TR>
						<TD CLASS="FormFieldName">Password:</TD>
						<TD CLASS="FormField">
							<button type="button" id="change-password-btn" onclick="togglePasswordFields()" style="background: #007cba; color: white; border: none; padding: 5px 10px; border-radius: 3px; cursor: pointer;">Change Password</button>
						</TD>
					</TR>
					<!-- Password fields will be dynamically inserted here by JavaScript -->
				<?php else: ?>
					<!-- New user: Show password fields directly -->
					<TR>
						<TD CLASS="FormFieldName"><?php if(!empty($GLOBALS['_PJ_strings']['password'])) echo $GLOBALS['_PJ_strings']['password']; else echo 'Password'; ?>*:</TD>
						<TD CLASS="FormField">
							<INPUT CLASS="FormField" TYPE="password" NAME="password" ID="password" VALUE="" autocomplete="new-password" required>
							<div id="password-strength" style="margin-top: 5px; font-size: 12px;"></div>
						</TD>
					</TR><TR>
						<TD CLASS="FormFieldName"><?php if(!empty($GLOBALS['_PJ_strings']['password_retype'])) echo $GLOBALS['_PJ_strings']['password_retype']; else echo 'Retype Password'; ?>*:</TD>
						<TD CLASS="FormField"><INPUT CLASS="FormField" TYPE="password" NAME="password_retype" VALUE="" autocomplete="new-password" required></TD>
					</TR>
				<?php endif; ?>
								</TR><TR>
									<TD CLASS="FormFieldName" WIDTH="<?php if(isset($_PJ_form_field_name_width)) echo $_PJ_form_field_name_width; ?>"><?php if(!empty($GLOBALS['_PJ_strings']['firstname'])) echo $GLOBALS['_PJ_strings']['firstname'] ?>:</TD>
										<TD CLASS="FormField" WIDTH="<?php if(isset($_PJ_form_field_width)) echo $_PJ_form_field_width; ?>"><INPUT CLASS="FormField<?php if(isset($ro_css_firstname)) echo $ro_css_firstname; ?>" NAME="firstname" VALUE="<?php if(isset($firstname)) echo $firstname; ?>"<?php if(isset($ro_firstname)) echo $ro_firstname; ?>></TD>
									</TR><TR>
									<TD CLASS="FormFieldName" WIDTH="<?php if(isset($_PJ_form_field_name_width)) echo $_PJ_form_field_name_width; ?>"><?php if(!empty($GLOBALS['_PJ_strings']['lastname'])) echo $GLOBALS['_PJ_strings']['lastname'] ?>*:</TD>
										<TD CLASS="FormField"><INPUT CLASS="FormField<?php if(isset($ro_css_lastname)) echo $ro_css_lastname; ?>" NAME="lastname" VALUE="<?php if(isset($lastname)) echo $lastname; ?>"<?php if(isset($ro_lastname)) echo $ro_lastname; ?>></TD>
									</TR><TR>
									<TD CLASS="FormFieldName"><?php if(!empty($GLOBALS['_PJ_strings']['email'])) echo $GLOBALS['_PJ_strings']['email'] ?>:</TD>
										<TD CLASS="FormField"><INPUT CLASS="FormField" TYPE="text" NAME="email" VALUE="<?php if(isset($email)) echo $email; ?>"></TD>
									</TR><TR>
									<TD CLASS="FormFieldName"><?php if(!empty($GLOBALS['_PJ_strings']['telephone'])) echo $GLOBALS['_PJ_strings']['telephone'] ?>:</TD>
										<TD CLASS="FormField"><INPUT CLASS="FormField" TYPE="text" NAME="telephone" VALUE="<?php if(isset($telephone)) echo $telephone; ?>"></TD>
									</TR><TR>
									<TD CLASS="FormFieldName"><?php if(!empty($GLOBALS['_PJ_strings']['facsimile'])) echo $GLOBALS['_PJ_strings']['facsimile'] ?>:</TD>
										<TD CLASS="FormField"><INPUT CLASS="FormField" TYPE="text" NAME="facsimile" VALUE="<?php if(isset($facsimile)) echo $facsimile; ?>"></TD>
									</TR><TR>
									<TD CLASS="FormFieldName">Theme:</TD>
										<TD CLASS="FormField"><SELECT CLASS="FormField" NAME="theme_preference">
						<OPTION VALUE="system"<?php if((!isset($user) || $user->giveValue('theme_preference') == 'system' || $user->giveValue('theme_preference') == '')) print ' SELECTED'; ?>>System Default</OPTION>
						<OPTION VALUE="light"<?php if(isset($user) && $user->giveValue('theme_preference') == 'light') print ' SELECTED'; ?>>Light</OPTION>
						<OPTION VALUE="dark"<?php if(isset($user) && $user->giveValue('theme_preference') == 'dark') print ' SELECTED'; ?>>Dark</OPTION>
					</SELECT></TD>
				</TR><TR>
					<TD COLSPAN="2" CLASS="FormFieldName" style="padding-top: 20px; font-weight: bold; border-top: 1px solid #ccc;">Invoice Settings</TD>
				</TR><TR>
					<TD CLASS="FormFieldName">Company Name:</TD>
					<TD CLASS="FormField"><INPUT CLASS="FormField" NAME="company_name" VALUE="<?php echo htmlspecialchars($user->giveValue('company_name') ?? ''); ?>"></TD>
				</TR><TR>
					<TD CLASS="FormFieldName">Company Address:</TD>
					<TD CLASS="FormField"><TEXTAREA CLASS="FormField" NAME="company_address" rows="3"><?php echo htmlspecialchars($user->giveValue('company_address') ?? ''); ?></TEXTAREA></TD>
				</TR><TR>
					<TD CLASS="FormFieldName">Postal Code:</TD>
					<TD CLASS="FormField"><INPUT CLASS="FormField" NAME="company_postal_code" VALUE="<?php echo htmlspecialchars($user->giveValue('company_postal_code') ?? ''); ?>"></TD>
				</TR><TR>
					<TD CLASS="FormFieldName">City:</TD>
					<TD CLASS="FormField"><INPUT CLASS="FormField" NAME="company_city" VALUE="<?php echo htmlspecialchars($user->giveValue('company_city') ?? ''); ?>"></TD>
				</TR><TR>
					<TD CLASS="FormFieldName">Country:</TD>
					<TD CLASS="FormField"><INPUT CLASS="FormField" NAME="company_country" VALUE="<?php echo htmlspecialchars($user->giveValue('company_country') ?? ''); ?>"></TD>
				</TR><TR>
					<TD CLASS="FormFieldName">Tax Number:</TD>
					<TD CLASS="FormField"><INPUT CLASS="FormField" NAME="tax_number" VALUE="<?php echo htmlspecialchars($user->giveValue('tax_number') ?? ''); ?>"></TD>
				</TR><TR>
					<TD CLASS="FormFieldName">VAT Number:</TD>
					<TD CLASS="FormField"><INPUT CLASS="FormField" NAME="vat_number" VALUE="<?php echo htmlspecialchars($user->giveValue('vat_number') ?? ''); ?>"></TD>
				</TR><TR>
					<TD CLASS="FormFieldName">Bank Name:</TD>
					<TD CLASS="FormField"><INPUT CLASS="FormField" NAME="bank_name" VALUE="<?php echo htmlspecialchars($user->giveValue('bank_name') ?? ''); ?>"></TD>
				</TR><TR>
					<TD CLASS="FormFieldName">Bank IBAN:</TD>
					<TD CLASS="FormField"><INPUT CLASS="FormField" NAME="bank_iban" VALUE="<?php echo htmlspecialchars($user->giveValue('bank_iban') ?? ''); ?>"></TD>
				</TR><TR>
					<TD CLASS="FormFieldName">Bank BIC:</TD>
					<TD CLASS="FormField"><INPUT CLASS="FormField" NAME="bank_bic" VALUE="<?php echo htmlspecialchars($user->giveValue('bank_bic') ?? ''); ?>"></TD>
				</TR><TR>
					<TD CLASS="FormFieldName">Invoice Number Format:</TD>
					<TD CLASS="FormField">
						<INPUT CLASS="FormField" NAME="invoice_number_format" VALUE="<?php echo htmlspecialchars($user->giveValue('invoice_number_format') ?? 'R-{YYYY}-{MM}-{###}'); ?>">
						<div style="font-size: 11px; color: #666; margin-top: 3px;">
							Use {YYYY} for year, {MM} for month, {###} for sequential number
						</div>
					</TD>
				</TR><TR>
					<TD CLASS="FormFieldName">Default VAT Rate (%):</TD>
					<TD CLASS="FormField"><INPUT CLASS="FormField" TYPE="number" step="0.01" NAME="default_vat_rate" VALUE="<?php echo htmlspecialchars($user->giveValue('default_vat_rate') ?? '19.00'); ?>"></TD>
				</TR><TR>
					<TD CLASS="FormFieldName">Payment Terms (Days):</TD>
					<TD CLASS="FormField"><INPUT CLASS="FormField" TYPE="number" NAME="payment_terms_days" VALUE="<?php echo htmlspecialchars($user->giveValue('payment_terms_days') ?? '14'); ?>"></TD>
				</TR><TR>
					<TD CLASS="FormFieldName">Payment Terms Text:</TD>
					<TD CLASS="FormField"><TEXTAREA CLASS="FormField" NAME="payment_terms_text" rows="3"><?php echo htmlspecialchars($user->giveValue('payment_terms_text') ?? ''); ?></TEXTAREA></TD>
					<?php
if($_PJ_auth->checkPermission('admin')) {
	?>
				</TR><TR>
									<TD CLASS="FormFieldName"><?php if(!empty($GLOBALS['_PJ_strings']['allow_nc'])) echo $GLOBALS['_PJ_strings']['allow_nc'] ?>:</TD>
										<TD CLASS="FormField"><SELECT NAME="allow_nc">
											<OPTION VALUE="0"<?php if(!empty($allow_nc) and $allow_nc == '0') print ' SELECTED'; ?>><?php if(!empty($GLOBALS['_PJ_strings']['no'])) echo $GLOBALS['_PJ_strings']['no'] ?>
												<OPTION VALUE="1"<?php if(!empty($allow_nc) and $allow_nc == '1') print ' SELECTED'; ?>><?php if(!empty($GLOBALS['_PJ_strings']['yes'])) echo $GLOBALS['_PJ_strings']['yes'] ?>
											</SELECT>
										</TD>
									</TR><TR>
									<TD CLASS="FormFieldName"><?php if(!empty($GLOBALS['_PJ_strings']['permissions'])) echo $GLOBALS['_PJ_strings']['permissions'] ?>*:</TD>
										<TD CLASS="FormField"><SELECT CLASS="FormSelect" NAME="permissions[]" multiple="multiple">
					<?php
	reset($a_permissions);
		foreach($a_permissions as $name => $key) {
		?>
						<OPTION VALUE="<?php if(isset($name)) echo $name; ?>"<?php if(in_array($name, $permissions)) print ' SELECTED'; ?>><?= $GLOBALS['_PJ_permission_names'][$name] ?>
						<?php
	}
	?>
					 </SELECT></TD>
					 				</TR><TR>
									<TD CLASS="FormFieldName"><?php if(!empty($GLOBALS['_PJ_strings']['gids'])) echo $GLOBALS['_PJ_strings']['gids'] ?>*:</TD>
										<TD CLASS="FormField"><SELECT CLASS="FormSelect" NAME="gids[]" multiple="multiple" id="gids-select">
					<?php
	// For new users, add personal group option
	if(empty($id) && !empty($username)) {
		echo '<OPTION SELECTED value="new_personal_group" id="personal-group-option">' . htmlspecialchars($username) . ' (Personal Group)</OPTION>';
	}
		reset($a_gids);
		foreach($a_gids as $gid => $name) {
		?>
						<OPTION<?php if(in_array($gid, $gids)) print ' SELECTED'; ?> value="<?php if(isset($gid)) echo $gid; ?>"><?php if(isset($name)) echo $name; ?>
						<?php
	}
	?>
					 </SELECT></TD>
					 <?php
}
?>
				</TR><TR>
									<TD>&nbsp;</TD>
										<TD>&nbsp;</TD>
									</TR><TR>
									<TD COLSPAN="2"><INPUT CLASS="FormSubmit" TYPE="SUBMIT" VALUE="<?php if(!empty($GLOBALS['_PJ_strings']['save'])) echo $GLOBALS['_PJ_strings']['save'] ?> >>"></TD>
									</TR>
							</TABLE>
						</TD>
					</TR>
			</TABLE>
	</FORM>
<?php if (empty($_REQUEST['debug'])): ?>
<script>
// Get localized strings from PHP
var passwordLabel = '<?php if(!empty($GLOBALS["_PJ_strings"]["password"])) echo addslashes($GLOBALS["_PJ_strings"]["password"]); else echo "Password"; ?>';
var passwordRetypeLabel = '<?php if(!empty($GLOBALS["_PJ_strings"]["password_retype"])) echo addslashes($GLOBALS["_PJ_strings"]["password_retype"]); else echo "Retype Password"; ?>';
function togglePasswordFields() {
    var button = document.getElementById('change-password-btn');
    var existingFields = document.getElementById('password-fields');
    
    if (!existingFields) {
        // Create password fields dynamically
        var buttonRow = button.closest('tr');
        var table = buttonRow.parentNode;
        
        // Create password field row
        var passwordRow = document.createElement('tr');
        passwordRow.id = 'password-fields';
        passwordRow.innerHTML = '<td class="FormFieldName">' + passwordLabel + '*:</td>' +
                               '<td class="FormField"><input class="FormField" type="password" name="password" value="" id="password-field" autocomplete="new-password"></td>';
        
        // Create password retype field row
        var passwordRetypeRow = document.createElement('tr');
        passwordRetypeRow.id = 'password-retype-fields';
        passwordRetypeRow.innerHTML = '<td class="FormFieldName">' + passwordRetypeLabel + '*:</td>' +
                                     '<td class="FormField"><input class="FormField" type="password" name="password_retype" value="" id="password-retype-field" autocomplete="new-password"></td>';
        
        // Insert after button row
        var nextRow = buttonRow.nextElementSibling;
        table.insertBefore(passwordRow, nextRow);
        table.insertBefore(passwordRetypeRow, nextRow);
        
        // Update button
        button.textContent = 'Cancel Password Change';
        button.style.background = '#dc3545';
        
        // Focus first password field
        var dynamicPasswordField = document.getElementById('password-field');
        dynamicPasswordField.focus();
        
        // Add password validation to dynamic field
        dynamicPasswordField.addEventListener('input', function() {
            var result = validatePasswordStrength(this.value);
            var strengthDiv = document.getElementById('password-strength-dynamic');
            if (!strengthDiv) {
                // Create strength indicator for dynamic field
                strengthDiv = document.createElement('div');
                strengthDiv.id = 'password-strength-dynamic';
                strengthDiv.style.marginTop = '5px';
                strengthDiv.style.fontSize = '12px';
                this.parentNode.appendChild(strengthDiv);
            }
            strengthDiv.textContent = result.message;
            strengthDiv.className = result.class;
            
            if (result.valid) {
                strengthDiv.style.color = '#28a745';
            } else {
                strengthDiv.style.color = '#dc3545';
            }
        });
    } else {
        // Remove password fields completely
        document.getElementById('password-fields').remove();
        document.getElementById('password-retype-fields').remove();
        
        // Update button
        button.textContent = 'Change Password';
        button.style.background = '#007cba';
    }
}
// Auto-suggest username as firstname/lastname for new users
function suggestNameFromUsername() {
    var usernameField = document.querySelector('input[name="login"]');
    var firstnameField = document.querySelector('input[name="firstname"]');
    var lastnameField = document.querySelector('input[name="lastname"]');
    var isNewUser = <?php echo empty($id) ? 'true' : 'false'; ?>;
    
    if (!isNewUser || !usernameField || !firstnameField || !lastnameField) {
        return; // Only for new users
    }
    
    var username = usernameField.value.trim();
    
    // Only suggest if firstname and lastname are empty
    if (username !== '' && firstnameField.value.trim() === '' && lastnameField.value.trim() === '') {
        firstnameField.value = username;
        lastnameField.value = username;
    }
}

// Personal group management for new users
function updatePersonalGroup() {
    var usernameField = document.querySelector('input[name="login"]');
    var gidsSelect = document.getElementById('gids-select');
    var personalGroupOption = document.getElementById('personal-group-option');
    var isNewUser = <?php echo empty($id) ? 'true' : 'false'; ?>;
    
    if (!isNewUser || !usernameField || !gidsSelect) {
        return; // Only for new users
    }
    
    var username = usernameField.value.trim();
    
    if (username === '') {
        // Remove personal group option if username is empty
        if (personalGroupOption) {
            personalGroupOption.remove();
        }
    } else {
        // Update or create personal group option
        if (personalGroupOption) {
            personalGroupOption.textContent = username + ' (Personal Group)';
            personalGroupOption.value = 'new_personal_group';
        } else {
            // Create new personal group option
            var newOption = document.createElement('option');
            newOption.id = 'personal-group-option';
            newOption.value = 'new_personal_group';
            newOption.textContent = username + ' (Personal Group)';
            newOption.selected = true;
            
            // Insert as first option
            gidsSelect.insertBefore(newOption, gidsSelect.firstChild);
        }
    }
}
// Password validation function (from functions.js)
function validatePasswordStrength(password) {
    if (password.length >= 12) {
        return { valid: true, message: 'Strong password', class: 'password-strong' };
    }
    
    if (password.length >= 8) {
        var hasNumber = /\d/.test(password);
        var hasSpecial = /[!@#$%^&*()_+\-=\[\]{};':"\\|,.<>\/?]/.test(password);
        
        if (hasNumber && hasSpecial) {
            return { valid: true, message: 'Strong password', class: 'password-strong' };
        } else if (hasNumber || hasSpecial) {
            return { valid: false, message: 'Password needs both number and special character', class: 'password-weak' };
        } else {
            return { valid: false, message: 'Password needs number and special character', class: 'password-weak' };
        }
    }
    
    return { valid: false, message: 'Password too short (minimum 8 characters)', class: 'password-weak' };
}

// Add event listener when page loads
document.addEventListener('DOMContentLoaded', function() {
    var usernameField = document.querySelector('input[name="login"]');
    var passwordField = document.getElementById('password');
    var strengthIndicator = document.getElementById('password-strength');
    var form = document.querySelector('form');
    
    // Check if there are password-related errors and auto-show password fields
    var hasPasswordError = <?php echo (isset($message) && (strpos($message, 'password') !== false || strpos($message, 'Password') !== false)) ? 'true' : 'false'; ?>;
    var isEditMode = <?php echo (!empty($id)) ? 'true' : 'false'; ?>;
    
    if (hasPasswordError && isEditMode) {
        // Auto-show password fields if there are password errors
        setTimeout(function() {
            var changePasswordBtn = document.getElementById('change-password-btn');
            if (changePasswordBtn && !document.getElementById('password-fields')) {
                togglePasswordFields();
            }
        }, 100);
    }
    
    if (usernameField) {
        // Update personal group on input (real-time)
        usernameField.addEventListener('input', function() {
            updatePersonalGroup();
        });
        
        // Suggest name only on blur and only if name fields are empty
        usernameField.addEventListener('blur', function() {
            var firstnameField = document.querySelector('input[name="firstname"]');
            var lastnameField = document.querySelector('input[name="lastname"]');
            
            // Only suggest if both name fields are empty
            if (firstnameField && lastnameField && 
                firstnameField.value.trim() === '' && lastnameField.value.trim() === '') {
                suggestNameFromUsername();
            }
        });
        
        // Initialize personal group on page load
        updatePersonalGroup();
    }
    
    // Password strength validation for new users
    if (passwordField && strengthIndicator) {
        passwordField.addEventListener('input', function() {
            var result = validatePasswordStrength(this.value);
            strengthIndicator.textContent = result.message;
            strengthIndicator.className = result.class;
            
            // Add CSS styles
            if (result.valid) {
                strengthIndicator.style.color = '#28a745';
            } else {
                strengthIndicator.style.color = '#dc3545';
            }
        });
    }
    
    // Form validation on submit
    if (form) {
        form.addEventListener('submit', function(e) {
            // Check for both static and dynamic password fields
            var passwordField = document.getElementById('password') || document.getElementById('password-field');
            var retypeField = document.querySelector('input[name="password_retype"]');
            
            // Only validate for new users or when changing password
            if (passwordField && passwordField.value !== '') {
                var result = validatePasswordStrength(passwordField.value);
                
                if (!result.valid) {
                    alert('Password validation failed: ' + result.message);
                    // Auto-show password fields if they're hidden
                    if (isEditMode && !document.getElementById('password-fields')) {
                        togglePasswordFields();
                    }
                    e.preventDefault();
                    return false;
                }
                
                // Check password match
                if (retypeField && passwordField.value !== retypeField.value) {
                    alert('Passwords do not match!');
                    // Auto-show password fields if they're hidden
                    if (isEditMode && !document.getElementById('password-fields')) {
                        togglePasswordFields();
                    }
                    e.preventDefault();
                    return false;
                }
            }
        });
    }
});
</script>
<?php endif; ?>
<!-- user/form.ihtml - END -->
