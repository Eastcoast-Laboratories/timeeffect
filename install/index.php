<?php
require_once('../include/fix_php7.php');
require_once('../include/functions.inc.php');
require_once('../include/db_mysql.inc.php');
$db = new DB_Sql();

if(empty($step) and empty($_POST['step'])) {
	$step = 1;
}
if(!empty($_POST['step'])) {
	$step = $_POST['step'];
}
if($step > 1) {
	if(empty($_POST['db_name'])) {
		error_log("no database name given");
		die('no default database name given');
	}
	$db_name = $_POST['db_name'];
	$db_host = $_POST['db_host'];
	$db_user = $_POST['db_user'];
	$db_password = $_POST['db_password'];
	$db_prefix = $_POST['db_prefix'] ?? 'te_';
	$currency = $_POST['currency'] ?? 'EUR';
	$admin_user = $_POST['admin_user'] ?? '';
	$admin_password = $_POST['admin_password'] ?? '';
	$interface_language = $_POST['interface_language'] ?? 'de';
	$decimal_point = $_POST['decimal_point'] ?? ',';
	$thousands_seperator = $_POST['thousands_seperator'] ?? '.';
	$session_length = $_POST['session_length'] ?? '36000';
	$allow_delete = $_POST['allow_delete'] ?? '1';
	
	// Extract registration variables from POST data
	$allow_registration = $_POST['allow_registration'] ?? 'false';
	$registration_email_confirm = $_POST['registration_email_confirm'] ?? 'false';
	$allow_password_recovery = $_POST['allow_password_recovery'] ?? 'true';
	$registration_secure_defaults = $_POST['registration_secure_defaults'] ?? 'true';
	$registration_default_access = $_POST['registration_default_access'] ?? 'rwxr-----';
	$db->Halt_On_Error = 'no';
	$db->Database	= $db_name;
	$db->Host		= $db_host;
	$db->User		= $db_user;
	$db->Password	= $db_password;
	if(!$db->connect()) {
		$error_message = 'Error connecting database! Please check the entered values.';
		$step--;
	}
}
if($step == 3) {
	if(empty($currency) || empty($admin_user) || empty($admin_password)) {
		$error_message = 'Please enter values in every field (currency, admin user, admin password).';
		$step--;
	}
}
?>
<HTML>
<HEAD>
<TITLE>TIMEEFFECT - Installation</TITLE>
<LINK REL="stylesheet" HREF="../css/project.css" TYPE="text/css">
<SCRIPT LANGUAGE="Javascript1.2" SRC="../include/functions.js" type="text/javascript"></SCRIPT>
<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
</HEAD>

<BODY>
<TABLE CELLPADDING="0" CELLSPACING="0" BORDER="0" WIDTH="100%" HEIGHT="100%">
	<TR HEIGHT="100">
<!-- START - left navigation -->
		<TD ID="leftNavigation" WIDTH="160" ROWSPAN="2">
		<TABLE CELLPADDING="0" CELLSPACING="0" BORDER="0" WIDTH="160" HEIGHT="100%">
			<TR>
				<TD VALIGN="top" CLASS="leftNavi"><TABLE CELLPADDING="3" CELLSPACING="0" BORDER="0">
					<TR>
						<TD CLASS="headFrame" COLSPAN="2" HEIGHT="150" VALIGN="top" id="logo"><a href="../"><IMG SRC="../images/logo_te_150.png" WIDTH="150" HEIGHT="19" BORDER="0" HSPACE="5" VSPACE="0"></a></TD>
					</TR>
				</TABLE></TD>
			</TR>
		</TABLE>
		</TD>
<!-- END - left navigation -->
		<TD CLASS="headFrame"><TABLE CELLPADDING="0" CELLSPACING="0" BORDER="0" WIDTH="100%">
	<TR HEIGHT="100">
<!-- START - Main Options  -->
		<TD CLASS="mainOptionFrame" VALIGN="bottom">
		<TABLE CELLPADDING="0" CELLSPACING="0" BORDER="0" WIDTH="100%">
			<TR>
				<TD><TABLE CELLPADDING="0" CELLSPACING="0" BORDER="0">
					<TR>
						<TD CLASS="mainOptionS">&nbsp;&nbsp;<A CLASS="mainOptionS" HREF="index.php">Installation</A>&nbsp;&nbsp;</TD>
						<TD CLASS="mainOptionDivision"><IMG SRC="../images/main-option-es.gif" WIDTH="10" HEIGHT="24" BORDER="0"></TD>
					</TR>
				</TABLE></td>
			</TR>
		</TABLE>
		</TD>
<!-- END - Main Options  -->
	</TR>
</TABLE></TD>
	</TR><TR>
		<TD VALIGN="top">
		<TABLE BORDER="0" CELLPADDING="0" CELLSPACING="0" WIDTH="100%">
			<TR>
				<TD><IMG SRC="../images/abstand.gif" HEIGHT="40" WIDTH="1" BORDER="0"></TD>
			</TR><TR>
				<TD rowspan="3" ALIGN="right"><IMG SRC="../images/abstand.gif" HEIGHT="1" WIDTH="40" BORDER="0"></TD>
<!-- START - content -->
				<TD CLASS="content">
<?php
if(!empty($error_message)) {
	print '<span class="errorMessage"><b>ERROR: ' . $error_message . '</b></span><br><br>';
}
include("step$step.ihtml.php"); //step1 till step4
?>
				</TD>
<!-- END - content -->
				<TD rowspan="3" ALIGN="right"><IMG SRC="../images/abstand.gif" HEIGHT="1" WIDTH="40" BORDER="0"></TD>
			</TR><TR>
				<TD><IMG SRC="../images/abstand.gif" HEIGHT="40" WIDTH="1" BORDER="0"></TD>
			</TR><TR>
				<TD><IMG SRC="../images/gray.gif" WIDTH="100%" HEIGHT="1" BORDER="0"></TD>
			</TR>
		</TABLE></TD>
	</TR>
</TABLE>

</BODY>
</HTML>
