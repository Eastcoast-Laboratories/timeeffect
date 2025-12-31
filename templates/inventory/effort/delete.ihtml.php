<!-- inventory/effort/delete.ihtml - START -->
<?php
	if(isset($effort) && is_object($effort)) {
		$eid				= $effort->giveValue('id');
		$description		= $effort->giveValue('description');
		$effort_date		= $effort->giveValue('date');
		$effort_begin		= $effort->giveValue('begin');
		$effort_end			= $effort->giveValue('end');
		include($GLOBALS['_PJ_root'] . '/templates/inventory/effort/options/delete.ihtml.php');
	} else {
		// Show error if effort doesn't exist
		echo '<div class="alert alert-error" style="background-color: #f8d7da; color: #721c24; padding: 1rem; border-radius: 0.5rem; margin: 2rem auto; max-width: 600px;">';
		echo '<h2 style="margin: 0 0 0.5rem 0;">❌ ' . htmlspecialchars($GLOBALS['_PJ_strings']['error']) . '</h2>';
		echo '<p style="margin: 0; font-size: 1.1rem;">Error: The effort you are trying to delete does not exist or you do not have access to it.</p>';
		echo '</div>';
		exit;
	}
?>
	<FORM ACTION="<?php print $GLOBALS['_PJ_efforts_inventory_script']; ?>" METHOD="<?php if(!empty($GLOBALS['_PJ_form_method'])) echo $GLOBALS['_PJ_form_method']; ?>">
	<INPUT TYPE="hidden" NAME="eid" VALUE="<?php if(isset($eid)) echo $eid; ?>">
	<INPUT TYPE="hidden" NAME="pid" VALUE="<?php if(isset($pid)) echo $pid; ?>">
	<INPUT TYPE="hidden" NAME="cid" VALUE="<?php if(isset($cid)) echo $cid; ?>">
	<INPUT TYPE="hidden" NAME="delete" VALUE="1">
	<CENTER>
	<TABLE	WIDTH="90%"
			BORDER="<?php print($_PJ_inner_frame_border); ?>"
			CELLPADDING="<?php print($_PJ_inner_frame_cellpadding); ?>"
			CELLSPACING="<?php print($_PJ_inner_frame_cellspacing ); ?>">
		<TR>
			<TD CLASS="content">
			<TABLE BORDER="0" CELLPADDING="0" CELLSPACING="0" WIDTH="100%">
				<TR>
					<TD CLASS="MessageAsk" COLSPAN="2"><?php if(!empty($GLOBALS['_PJ_strings']['ask_effort_delete'])) echo $GLOBALS['_PJ_strings']['ask_effort_delete'] ?></TD>
				</TR><TR>
					<TD COLSPAN="2" style="padding: 1rem 0; border-top: 1px solid #ddd; border-bottom: 1px solid #ddd;">
						<table style="width: 100%; margin: 0.5rem 0;">
							<tr>
								<td style="font-weight: bold; width: 30%;">Description:</td>
								<td><?php echo htmlspecialchars($description); ?></td>
							</tr>
							<tr>
								<td style="font-weight: bold;">Date:</td>
								<td><?php echo $effort->formatTime($effort_date, "d.m.Y"); ?></td>
							</tr>
							<tr>
								<td style="font-weight: bold;">Time:</td>
								<td><?php echo $effort->formatTime($effort_begin, "H:i"); ?> - <?php echo $effort->formatTime($effort_end, "H:i"); ?></td>
							</tr>
						</table>
					</TD>
				</TR><TR>
					<TD ALIGN="left"><INPUT CLASS="FormSubmit" TYPE="SUBMIT" NAME="cancel" VALUE="<< <?php if(!empty($GLOBALS['_PJ_strings']['cancel'])) echo $GLOBALS['_PJ_strings']['cancel'] ?>"></TD>
					<TD ALIGN="right"><INPUT CLASS="FormSubmit" TYPE="SUBMIT" NAME="confirm" VALUE="<?php if(!empty($GLOBALS['_PJ_strings']['delete'])) echo $GLOBALS['_PJ_strings']['delete'] ?> >>"></TD>
				</TR>
			</TABLE></TD>
		</TR>
	</TABLE>
	</CENTER>
	</FORM>
<!-- inventory/effort/delete.ihtml - END -->
