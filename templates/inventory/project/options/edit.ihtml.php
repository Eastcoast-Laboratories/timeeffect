<!-- inventory/project/options/edit.ihtml - START -->
		<TABLE CELLPADDING="0" CELLSPACING="0" BORDER="0" WIDTH="100%">
			<TR VALIGN="center">
				<td class="spacer_before_path"></td>
				<TD class="path"><?php include($GLOBALS['_PJ_root'] . '/templates/shared/path.ihtml.php'); ?></TD>
			</TR>
		</TABLE>
		<div class="subnav-container">
			<a class="modern-tab active" href="<?= $GLOBALS['_PJ_projects_inventory_script'] . "?edit=1&cid=$cid&pid=$pid" ?>"><?= $GLOBALS['_PJ_strings']['edit_project']?></a>
			<a class="modern-tab" href="<?= $GLOBALS['_PJ_customer_inventory_script'] . "?edit=1&rates=1&cid=$cid" ?>"><?= $GLOBALS['_PJ_strings']['customer_rates_data'] ?? 'Stundensätze' ?></a>
		</div>
<!-- inventory/project/options/edit.ihtml - END -->
