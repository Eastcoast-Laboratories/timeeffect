<!-- user/register.ihtml - START -->

<?php
	// Check if registration is enabled
	if (!isset($GLOBALS['_PJ_allow_registration']) || !$GLOBALS['_PJ_allow_registration']) {
		$error_message = $GLOBALS['_PJ_strings']['registration_disabled'];
		include("$_PJ_root/templates/error.ihtml.php");
		return;
	}

	$gids = array();
	$permissions = array('agent'); // Default permission for new registrations
	
	// Only allow basic agent permission for self-registration
	$a_permissions = array('agent' => 1); // Restrict to agent only
	
	// Load user-defined groups from gids table (not system permission groups)
	$db = new Database();
	$db->connect();
	// Load all available user groups from gids table
	$db->query("SELECT id, name FROM " . $GLOBALS['_PJ_gid_table'] . " ORDER BY name");
	$a_gids = array();
	while ($db->next_record()) {
		// TODO: only allow groups with the flag 'public' set
		// $a_gids[$db->Record['id']] = $db->Record['name'];
	}
	
	// Add option for no group membership (secure default)
	$a_gids[0] = $GLOBALS['_PJ_strings']['no_group'] ?? 'Keine Gruppenzugehörigkeit';
?>

<?php 
	// Check if this is email-only registration step or completion step
	$completion_step = isset($_REQUEST['token']) && isset($_REQUEST['complete']);
?>

<?php if (!$completion_step): ?>
<!-- Email-only registration step -->
<FORM ACTION="<?= $GLOBALS['_PJ_http_root'] ?>/register.php" METHOD="POST">
<INPUT TYPE="hidden" NAME="register" VALUE="1">
<INPUT TYPE="hidden" NAME="altered" VALUE="1">
<INPUT TYPE="hidden" NAME="email_only" VALUE="1">

	<CENTER>
	<TABLE WIDTH="90%" BORDER="0" CELLPADDING="3" CELLSPACING="0">
		<TR>
			<TD CLASS="content">
			<TABLE BORDER="0" CELLPADDING="3" CELLSPACING="0">
				<TR>
					<TD CLASS="Error" COLSPAN="2"><?php if(isset($message)) echo $message; ?></TD>
				</TR><TR>
					<TD CLASS="FormFieldName" COLSPAN="2">
						<h3>Register with Email</h3>
						<p>Enter your email address to start the registration process. You will receive an email with a link to complete your account setup.</p>
					</TD>
				</TR><TR>
					<TD CLASS="FormFieldName"><?= $GLOBALS['_PJ_strings']['email'] ?>*:</TD>
					<TD CLASS="FormField"><INPUT CLASS="FormField" TYPE="email" NAME="email" VALUE="<?php if(isset($email)) echo $email; ?>" required placeholder="Enter your email address"></TD>
				</TR>
				<TR>
					<TD>&nbsp;</TD>
					<TD>&nbsp;</TD>
				</TR><TR>
					<TD COLSPAN="2">
						<INPUT CLASS="FormSubmit" TYPE="SUBMIT" VALUE="<?= $GLOBALS['_PJ_strings']['register'] ?> >>">
						&nbsp;&nbsp;
						<A HREF="<?= $GLOBALS['_PJ_http_root'] ?>/" CLASS="FormCancel"><?= $GLOBALS['_PJ_strings']['cancel'] ?></A>
					</TD>
				</TR>
			</TABLE>
			</TD>
		</TR>
	</TABLE>
</FORM>

<?php else: ?>
<!-- Registration completion step with token -->
<FORM ACTION="<?= $GLOBALS['_PJ_http_root'] ?>/register.php" METHOD="POST">
<INPUT TYPE="hidden" NAME="register" VALUE="1">
<INPUT TYPE="hidden" NAME="altered" VALUE="1">
<INPUT TYPE="hidden" NAME="complete" VALUE="1">
<INPUT TYPE="hidden" NAME="token" VALUE="<?= htmlspecialchars($_REQUEST['token']) ?>">

	<CENTER>
	<TABLE WIDTH="90%" BORDER="0" CELLPADDING="3" CELLSPACING="0">
		<TR>
			<TD CLASS="content">
			<TABLE BORDER="0" CELLPADDING="3" CELLSPACING="0">
				<TR>
					<TD CLASS="Error" COLSPAN="2"><?php if(isset($message)) echo $message; ?></TD>
				</TR><TR>
					<TD CLASS="FormFieldName" COLSPAN="2">
						<h3>Complete Your Registration</h3>
						<p>Please fill in the details below to complete your account setup.</p>
					</TD>
				</TR><TR>
					<TD CLASS="FormFieldName"><?= $GLOBALS['_PJ_strings']['username'] ?>*:</TD>
					<TD CLASS="FormField"><INPUT CLASS="FormField" NAME="login" VALUE="<?php if(isset($username)) echo $username; ?>" required></TD>
				</TR><TR>
					<TD CLASS="FormFieldName"><?= $GLOBALS['_PJ_strings']['password'] ?>*:</TD>
					<TD CLASS="FormField"><INPUT CLASS="FormField" TYPE="password" NAME="password" VALUE="" required></TD>
				</TR><TR>
					<TD CLASS="FormFieldName"><?= $GLOBALS['_PJ_strings']['password_retype'] ?>*:</TD>
					<TD CLASS="FormField"><INPUT CLASS="FormField" TYPE="password" NAME="password_retype" VALUE="" required></TD>
				</TR><TR>
					<TD CLASS="FormFieldName"><?= $GLOBALS['_PJ_strings']['firstname'] ?>:</TD>
					<TD CLASS="FormField"><INPUT CLASS="FormField" NAME="firstname" VALUE="<?php if(isset($firstname)) echo $firstname; ?>"></TD>
				</TR><TR>
					<TD CLASS="FormFieldName"><?= $GLOBALS['_PJ_strings']['lastname'] ?>*:</TD>
					<TD CLASS="FormField"><INPUT CLASS="FormField" NAME="lastname" VALUE="<?php if(isset($lastname)) echo $lastname; ?>" required></TD>
				</TR><TR>
					<TD CLASS="FormFieldName"><?= $GLOBALS['_PJ_strings']['telephone'] ?>:</TD>
					<TD CLASS="FormField"><INPUT CLASS="FormField" TYPE="text" NAME="telephone" VALUE="<?php if(isset($telephone)) echo $telephone; ?>"></TD>
				</TR><TR>
					<TD CLASS="FormFieldName"><?= $GLOBALS['_PJ_strings']['facsimile'] ?>:</TD>
					<TD CLASS="FormField"><INPUT CLASS="FormField" TYPE="text" NAME="facsimile" VALUE="<?php if(isset($facsimile)) echo $facsimile; ?>"></TD>
				</TR><?php
					if(sizeof($a_gids)==1) {
						?><INPUT TYPE="hidden" NAME="gids" VALUE="<?= $a_gids[0] ?>"><?php
					} else {
						?><TR>
							<TD CLASS="FormFieldName"><?= $GLOBALS['_PJ_strings']['gids'] ?>:</TD>
							<TD CLASS="FormField"><SELECT CLASS="FormSelect" NAME="gids[]" multiple="multiple" size="4"><?php
							foreach($a_gids as $id => $name) {
								$selected = ($id == 0) ? ' selected="selected"' : ''; // Default to no group
							?>
								<OPTION value="<?= $id ?>"<?= $selected ?>><?= $name ?>
							<?php
							}
							?></SELECT></TD>
						</TR><?php
					}
				?>
				<TR>
					<TD>&nbsp;</TD>
					<TD>&nbsp;</TD>
				</TR><TR>
					<TD COLSPAN="2">
						<INPUT CLASS="FormSubmit" TYPE="SUBMIT" VALUE="Complete Registration >>">
						&nbsp;&nbsp;
						<A HREF="<?= $GLOBALS['_PJ_http_root'] ?>/" CLASS="FormCancel"><?= $GLOBALS['_PJ_strings']['cancel'] ?></A>
					</TD>
				</TR>
			</TABLE>
			</TD>
		</TR>
	</TABLE>
</FORM>
<?php endif; ?>

<!-- user/register.ihtml - END -->