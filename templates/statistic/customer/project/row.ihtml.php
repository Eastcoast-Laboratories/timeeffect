<!-- statistic/customer/project/row.ihtml - START -->
	<TR>
		<TD COLSPAN="11"><IMG SRC="<?php echo $GLOBALS['_PJ_image_path'] ?>/light-gray.gif" WIDTH="100%" HEIGHT="2" BORDER="0" ALIGN="absmiddle"></TD>
	</TR><TR HEIGHT="25">
		<TD CLASS="list<?php if(isset($rowclass)) echo $rowclass; ?>"><IMG SRC="<?php echo $GLOBALS['_PJ_image_path'] ?>/abstand.gif" BORDER="0" WIDTH="16" HEIGHT="16" ALIGN="absmiddle">&nbsp;<?php
		if($project->count(@$GLOBALS['shown']['be']) && $project->checkUserAccess('read')) {
			if(isset($expanded) && (!empty($expanded['pid'][$project->giveValue('id')]) || !empty($expanded['cid']['all']))) {
		?><A CLASS="list" HREF="<?= $GLOBALS['_PJ_customer_statistics_script'] . "?cid=" . $project->giveValue('customer_id') . "&pid=" . $project->giveValue('id') . "&cop=" . $project->giveValue('id') ?>"><IMG SRC="<?php if(!empty($GLOBALS['_PJ_icon_path'])) echo $GLOBALS['_PJ_icon_path'] ?>/triangle-d.gif" BORDER="0" WIDTH="16" HEIGHT="16" ALIGN="absmiddle"></A><?php
			} else {
		?><A CLASS="list" HREF="<?= $GLOBALS['_PJ_customer_statistics_script'] . "?cid=" . $project->giveValue('customer_id') . "&pid=" . $project->giveValue('id') . "&exp=" . $project->giveValue('id') ?>"><IMG SRC="<?php if(!empty($GLOBALS['_PJ_icon_path'])) echo $GLOBALS['_PJ_icon_path'] ?>/triangle-l.gif" BORDER="0" WIDTH="16" HEIGHT="16" ALIGN="absmiddle"></A><?php
			}
		} else {
		?><IMG SRC="<?php echo $GLOBALS['_PJ_image_path'] ?>/abstand.gif" BORDER="0" WIDTH="16" HEIGHT="16" ALIGN="absmiddle"><?php
		}
		?>&nbsp;<IMG SRC="<?php if(!empty($GLOBALS['_PJ_icon_path'])) echo $GLOBALS['_PJ_icon_path'] ?>/project.gif" BORDER="0" WIDTH="16" HEIGHT="16" ALIGN="absmiddle">&nbsp;<?php if($customer->checkUserAccess('read')) { ?><A CLASS="list" HREF="<?= $GLOBALS['_PJ_efforts_statistics_script'] . "?list=1&cid=" . $project->giveValue('customer_id') . "&pid=" . $project->giveValue('id') ?>"><?php } ?><?= $project->giveValue('project_name') ?></A></TD>
		<TD CLASS="listDetail<?php if(isset($rowclass)) echo $rowclass; ?>">&nbsp;</TD>
		<TD CLASS="listDetailNumeric<?php if(isset($rowclass)) echo $rowclass; ?>"><?php if($project->giveValue('days')) print formatNumber($project->giveValue('days'), true); ?></TD>
		<TD CLASS="listDetailNumeric<?php if(isset($rowclass)) echo $rowclass; ?>"><?php if($project->giveValue('costs')) print formatNumber($project->giveValue('costs'), true) . '&nbsp;' . $GLOBALS['_PJ_currency']; ?></TD>
		<TD CLASS="listDetailNumeric<?php if(isset($rowclass)) echo $rowclass; ?>"><?php if($project->giveValue('project_budget')) print formatNumber($project->giveValue('project_budget'), true) . '&nbsp;' . $GLOBALS['_PJ_currency']; ?></TD>
		<TD CLASS="listDetailNumeric<?php if(isset($rowclass)) echo $rowclass; ?>"><?php if($project->giveValue('project_budget')) print formatNumber($project->giveValue('project_budget')-$project->giveValue('costs'), true) . '&nbsp;' . $GLOBALS['_PJ_currency']; ?></TD>
	</TR><?php
	if(isset($expanded) && $expanded['pid'][$project->giveValue('id')]) {
		// Fix: Check if 'be' key exists to prevent undefined array key warning
		$be_filter = isset($GLOBALS['shown']['be']) ? $GLOBALS['shown']['be'] : '';
		$efforts	= new EffortList($customer, $project, $project->user, $be_filter);
		$project_total_costs = 0;
		while($efforts->nextEffort()) {
			$effort = $efforts->giveEffort();
			$row_class = !$row_class;
			$project_total_costs += $effort->giveValue('costs');
			include("$_PJ_root/templates/statistic/customer/project/effort/row.ihtml.php");
		}
		// Add project summary row with total costs
		if($project_total_costs > 0) {
			?>
			<TR>
				<TD CLASS="listDetail" COLSPAN="3">&nbsp;</TD>
				<TD CLASS="listDetailNumeric" style="font-weight: bold; border-top: 1px solid #ccc;">Projekt Summe: <?= formatNumber($project_total_costs, true) . '&nbsp;' . $GLOBALS['_PJ_currency']; ?></TD>
				<TD CLASS="listDetail" COLSPAN="2">&nbsp;</TD>
			</TR>
			<TR><TD COLSPAN="6" style="height: 10px;">&nbsp;</TD></TR>
			<?php
		}
	}
	?>
<!-- statistic/customer/project/row.ihtml - END -->
