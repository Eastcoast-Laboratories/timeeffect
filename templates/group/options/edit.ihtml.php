<!-- group/options/edit.ihtml - START -->
		<TABLE CELLPADDING="0" CELLSPACING="0" BORDER="0" WIDTH="100%">
			<TR VALIGN="center">
				<td class="spacer_before_path"></td>
				<TD class="path"><?php include($GLOBALS['_PJ_root'] . '/templates/shared/path.ihtml.php'); ?></TD>
			</TR>
		</TABLE>
		<TABLE CELLPADDING="0" CELLSPACING="0" BORDER="0" WIDTH="100%" BACKGROUND="<?php echo $GLOBALS['_PJ_image_path'] ?>/option-bg.gif">
			<TR>
				<TD VALIGN="top"><TABLE CELLPADDING="0" CELLSPACING="0" BORDER="0">
					<TR HEIGHT="24">
						<TD WIDTH="40"><IMG SRC="<?php echo $GLOBALS['_PJ_image_path'] ?>/abstand.gif" WIDTH="40" HEIGHT="1" BORDER="0"></TD>
						<TD BACKGROUND="<?php echo $GLOBALS['_PJ_image_path'] ?>/option-sb.gif"><IMG SRC="<?php echo $GLOBALS['_PJ_image_path'] ?>/option-bs.gif" BORDER="0"></TD>
						<TD CLASS="option" BACKGROUND="<?php echo $GLOBALS['_PJ_image_path'] ?>/option-sb.gif">&nbsp;&nbsp;<A CLASS="option" HREF="<?= $GLOBALS['PHP_SELF']  ?>?edit=1&gid=<?php if(isset($gid)) echo $gid; ?>"><?= $GLOBALS['_PJ_strings']['group_basic_data']?></A></TD>
						<TD>&nbsp;</TD>
					</TR>
				</TABLE></TD>
			</TR>
		</TABLE>
<!-- group/options/edit.ihtml - END -->
