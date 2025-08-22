<?php
include_once('functions.inc.php');

$a_buffer="";
$sql_buffer="";
$error_message="";

// Check if installation was already completed
$tables_exist = false;
$config_exists = file_exists('../include/config.inc.php');

$db_check = new DB_Sql();
$db_check->Halt_On_Error = 'no';
$db_check->Database = $db_name;
$db_check->Host = $db_host;
$db_check->User = $db_user;
$db_check->Password = $db_password;
if($db_check->connect()) {
    // Check if tables already exist
    $db_check->query("SHOW TABLES LIKE '{$db_prefix}auth'");
    if($db_check->num_rows() > 0) {
        $tables_exist = true;
        $error_message .= 'Installation already completed! Tables already exist.<br>';
        if ($config_exists) {
            $error_message .= 'If you want to reinstall, please drop the existing tables first.<br>';
        } else {
            $error_message .= 'Config file missing - will be recreated, but tables exist.<br>';
        }
    }
}

if($a_file = @fopen('config.inc.php-dist', 'r')) {
	while(!feof($a_file)) {
		$a_buffer .= fread($a_file, 4096);
	}
	@fclose($a_file);
	$http_root = str_replace('/install', '', dirname($PHP_SELF));
	$a_buffer = str_replace('<%db_prefix%>', $db_prefix, $a_buffer);
	$a_buffer = str_replace('<%db_name%>', $db_name, $a_buffer);
	$a_buffer = str_replace('<%db_host%>', $db_host, $a_buffer);
	$a_buffer = str_replace('<%db_user%>', $db_user, $a_buffer);
	$a_buffer = str_replace('<%db_password%>', $db_password, $a_buffer);
	$a_buffer = str_replace('<%language%>', $interface_language, $a_buffer);
	$a_buffer = str_replace('<%currency%>', $currency, $a_buffer);
	$a_buffer = str_replace('<%decimal_point%>', $decimal_point, $a_buffer);
	$a_buffer = str_replace('<%thousands_seperator%>', $thousands_seperator, $a_buffer);
	$a_buffer = str_replace('<%session_length%>', $session_length, $a_buffer);
	$a_buffer = str_replace('<%http_root%>', $http_root, $a_buffer);
	$a_buffer = str_replace('<%allow_delete%>', $allow_delete, $a_buffer);
	$a_buffer = str_replace('<%allow_registration%>', $allow_registration, $a_buffer);
	$a_buffer = str_replace('<%registration_email_confirm%>', $registration_email_confirm, $a_buffer);
	$a_buffer = str_replace('<%allow_password_recovery%>', $allow_password_recovery, $a_buffer);
	$a_buffer = str_replace('<%registration_secure_defaults%>', $registration_secure_defaults, $a_buffer);
	$a_buffer = str_replace('<%registration_default_access%>', $registration_default_access, $a_buffer);
	if($a_file = @fopen('../include/config.inc.php', 'w')) {
		fputs($a_file, $a_buffer);
		@fclose($a_file);
	} else {
		$error_message .= 'opening of file \'config.inc.php\' for writing failed!<br>';
	}
} else {
	$error_message .= 'opening of file \'config.inc.php-dist\' failed!<br>';
}

// Check if we should stop installation (only if tables exist AND config exists)
if(!empty($error_message) && $tables_exist && $config_exists) {
?>
				<b>TIMEEFFECT Installation - failed!</b><br><br>
				<span class="errorMessage"><b>ERROR(S): <?php if(isset($error_message)) echo $error_message; ?></b></span><br>
<?php
	return;
} elseif (!empty($error_message)) {
	// Show warning but continue if only tables exist (config will be recreated)
?>
				<b>TIMEEFFECT Installation - Warning</b><br><br>
				<span class="warning"><b>WARNING: <?php if(isset($error_message)) echo $error_message; ?></b></span><br>
				<span style="color: green;"><b>Continuing with config file creation...</b></span><br><br>
<?php
}

// Only create tables if they don't exist yet
if (!$tables_exist && $sql_file = @fopen('timeeffect.sql', 'r')) {
	while(!feof($sql_file)) {
		$sql_buffer .= fread($sql_file, 4096);
	}
	@fclose($sql_file);
	$sql_buffer = str_replace('<%db_prefix%>', $db_prefix, $sql_buffer);
	$sql_buffer = str_replace('<%admin_user%>', $admin_user, $sql_buffer);
	$sql_buffer = str_replace('<%admin_password%>', md5($admin_password), $sql_buffer);

	$pieces = array();
	splitSqlFile($pieces, $sql_buffer, 0);
	$pieces_count = count($pieces);
	for($i = 0; $i < $pieces_count; $i++) {
		$db->query($pieces[$i]);
		if($db->Errno) {
			$error_message .= 'execution of SQL command failed (' . $pieces[$i] . ')<br>';
			break;
		}
	}
} else {
	$error_message .= 'opening of file \'timeeffect.sql\' failed!<br>';
}

if ($tables_exist) {
	// Tables already exist, skip SQL execution
	echo "<span style='color: orange;'><b>Tables already exist - skipping SQL execution</b></span><br><br>";
}

if(!empty($error_message)) {
?>
				<b>TIMEEFFECT Installation - failed!</b><br><br>
				<span class="errorMessage"><b>ERROR(S): <?php if(isset($error_message)) echo $error_message; ?></b></span><br>
<?php
	return;
}
?>
				<b>TIMEEFFECT Installation - finished</b><br><br>
				The installation process has been successfully finished.<br><br>
				
				<b>IMPORTANT: Complete the following steps to finalize your installation:</b><br><br>
				
				<b>Step 1: Install Composer Dependencies</b><br>
				Run the following commands in your terminal:<br>
				<code style="background: #f0f0f0; padding: 5px; display: block; margin: 5px 0;">
				# Navigate to your TimeEffect directory<br>
				cd <?php echo $_SERVER['DOCUMENT_ROOT'] . str_replace('/install', '', dirname($_SERVER['PHP_SELF'])); ?><br><br>
				# Install dependencies<br>
				composer install --no-dev --optimize-autoloader
				</code><br>
				
				<b>Step 2: Generate .env Configuration</b><br>
				Generate a .env file from your configuration:<br>
				<code style="background: #f0f0f0; padding: 5px; display: block; margin: 5px 0;">
				# Generate .env from config.inc.php<br>
				php dev/generate_env_from_config.php<br><br>
				# Review and adjust the generated .env file if needed<br>
				nano .env
				</code><br>
				
				<b>Step 3: Security & Permissions</b><br>
				• Remove the 'install' directory for security reasons<br>
				• Set 'include/config.inc.php' to read-only for your web server process<br>
				• Set 'include/pdflayout.inc.php' to writeable for your web server process<br><br>
				
				After completing these steps, you can access your TIMEEFFECT installation <a href="../index.php"><b>>> here</b></a>;
