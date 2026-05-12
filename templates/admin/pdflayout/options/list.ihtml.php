<!-- admin/phplayout/option/list.ihtml - START -->
		<TABLE CELLPADDING="0" CELLSPACING="0" BORDER="0" WIDTH="100%">
			<TR VALIGN="center">
				<td class="spacer_before_path"></td>
				<TD class="path"><?php include($GLOBALS['_PJ_root'] . '/templates/shared/path.ihtml.php'); ?></TD>
			</TR>
		</TABLE>
		<div class="subnav-container">
			<a class="modern-tab active" href="<?= $GLOBALS['_PJ_pdf_admin_script'] . "?list=1&cid=$cid&pid=$pid" ?>"><?= $GLOBALS['_PJ_strings']['pdf_layout']?></a>
			<a class="modern-tab" href="<?= $GLOBALS['_PJ_http_root'] ?>/inventory/kimai_export.php"><?= $GLOBALS['_PJ_strings']['kimai_export'] ?? 'Kimai Export' ?></a>
			<a class="modern-tab" href="<?= $GLOBALS['_PJ_http_root'] ?>/inventory/kimai_efforts_export.php"><?= $GLOBALS['_PJ_strings']['kimai_efforts_export'] ?? 'Kimai Efforts Export' ?></a>
		</div>
<!-- admin/phplayout/option/list.ihtml - END -->
