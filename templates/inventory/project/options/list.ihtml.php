<!-- inventory/project/options/list.ihtml - START -->
		<TABLE CELLPADDING="0" CELLSPACING="0" BORDER="0" WIDTH="100%">
			<TR VALIGN="center">
				<td class="spacer_before_path"></td>
				<TD class="path"><?php include($GLOBALS['_PJ_root'] . '/templates/shared/path.ihtml.php'); ?></TD>
			</TR>
		</TABLE>
		<TABLE CELLPADDING="0" CELLSPACING="0" BORDER="0" WIDTH="100%" BACKGROUND="<?php echo $GLOBALS['_PJ_image_path'] ?>/option-bg.gif">
			<TR>
				<TD VALIGN="top">
				<!-- Modern Tab Navigation -->
				<div class="modern-tabs subnav-container">
					<a class="modern-tab" href="<?= $GLOBALS['_PJ_customer_inventory_script'] . "?list=1&cid=$cid&pid=$pid"?>"><?= $GLOBALS['_PJ_strings']['customers']?></a>
					<a class="modern-tab active" href="<?= $GLOBALS['_PJ_projects_inventory_script'] . "?list=1&cid=$cid&pid=$pid"?>"><?= $GLOBALS['_PJ_strings']['projects']?></a>
					<a class="modern-tab" href="<?= $GLOBALS['_PJ_efforts_inventory_script'] . "?list=1&cid=$cid&pid=$pid"?>"><?= $GLOBALS['_PJ_strings']['efforts']?></a>
					<?php if($cid): ?>
					<a class="modern-tab" href="<?= $GLOBALS['_PJ_customer_inventory_script'] . "?edit=1&rates=1&cid=$cid" ?>"><?= $GLOBALS['_PJ_strings']['customer_rates_data'] ?? 'Stundensätze' ?></a>
					<?php endif; ?>
				</div>
			</TD>
			</TR>
		</TABLE>
<!-- inventory/project/options/list.ihtml - END -->
