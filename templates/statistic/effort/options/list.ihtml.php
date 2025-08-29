<!-- statistic/effort/option/list.ihtml - START -->
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
					<a class="modern-tab" href="<?= $GLOBALS['_PJ_customer_statistics_script'] . "?list=1&cid=$cid&pid=$pid"?>"><?= $GLOBALS['_PJ_strings']['customers']?></a>
					<a class="modern-tab" href="<?= $GLOBALS['_PJ_projects_statistics_script'] . "?list=1&cid=$cid&pid=$pid"?>"><?= $GLOBALS['_PJ_strings']['projects']?></a>
					<a class="modern-tab active" href="<?= $GLOBALS['_PJ_efforts_statistics_script'] . "?list=1&cid=$cid&pid=$pid"?>"><?= $GLOBALS['_PJ_strings']['efforts']?></a>
				</div>
			</TD>
			</TR>
		</TABLE>
<!-- statistic/effort/option/list.ihtml - END -->
