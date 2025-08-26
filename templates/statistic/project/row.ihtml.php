<!-- statistic/project/row.ihtml - START -->
<?php
	// Initialize sum variables if not set
	if(!isset($sum_project_costs)) $sum_project_costs = 0;
	if(!isset($sum_project_days)) $sum_project_days = 0;
	if(!isset($sum_project_full_budget)) $sum_project_full_budget = 0;
	if(!isset($sum_project_remaining_budget)) $sum_project_remaining_budget = 0;
	
	if((isset($no_customer) && $no_customer) || (is_object($customer) && !$customer->giveValue('id'))) {
		$customer_id = $project->giveValue('customer_id');
		$customer = new Customer($project->user, $customer_id);
		$no_customer = 1;
	}
	$sum_project_costs				+= $project->giveValue('costs');
	$sum_project_days				+= $project->giveValue('days');
	$sum_project_full_budget		+= $project->giveValue('project_budget');
	$sum_project_remaining_budget	+= $project->giveValue('project_budget') - $project->giveValue('costs');
?>
	<TR>
		<TD COLSPAN="10"><IMG SRC="<?php if(!empty($GLOBALS['_PJ_image_path'])) echo $GLOBALS['_PJ_image_path'] ?>/gray.gif" WIDTH="100%" HEIGHT="1" BORDER="0"></TD>
	</TR><TR HEIGHT="25">
		<TD CLASS="list<?php if(isset($rowclass)) echo $rowclass; ?>" WIDTH="35%"><?php
		if($project->count(@$GLOBALS['shown']['be']) && $project->checkUserAccess('read')) {
			if(isset($expanded) && (!empty($expanded['pid'][$project->giveValue('id')]) || !empty($expanded['pid']['all']))) {
		?><A CLASS="list" HREF="<?= $GLOBALS['_PJ_projects_statistics_script'] . "?list=1&cid=".@$cid.'&pid='.@$pid.'&eid='.@$eid."&cop=" . $project->giveValue('id') ?>"><IMG SRC="<?php if(!empty($GLOBALS['_PJ_icon_path'])) echo $GLOBALS['_PJ_icon_path'] ?>/triangle-d.gif" BORDER="0" WIDTH="16" HEIGHT="16" ALIGN="absmiddle"></A><?php
			} else {
		?><A CLASS="list" HREF="<?= $GLOBALS['_PJ_projects_statistics_script'] . "?list=1&cid=".@$cid.'&pid='.@$pid.'&eid='.@$eid."&exp=" . $project->giveValue('id') ?>"><IMG SRC="<?php if(!empty($GLOBALS['_PJ_icon_path'])) echo $GLOBALS['_PJ_icon_path'] ?>/triangle-l.gif" BORDER="0" WIDTH="16" HEIGHT="16" ALIGN="absmiddle"></A><?php
			}
		} else {
		?><IMG SRC="<?php if(!empty($GLOBALS['_PJ_image_path'])) echo $GLOBALS['_PJ_image_path'] ?>/abstand.gif" BORDER="0" WIDTH="16" HEIGHT="16" ALIGN="absmiddle"><?php
		}
		?>&nbsp;<IMG SRC="<?php if(!empty($GLOBALS['_PJ_icon_path'])) echo $GLOBALS['_PJ_icon_path'] ?>/project<?php if($project->giveValue('closed') == 'Yes') print 'c' ?>.gif" BORDER="0" WIDTH="16" HEIGHT="16" ALIGN="absmiddle">&nbsp;<?php if($customer->checkUserAccess('read')) { ?><A CLASS="list" HREF="<?= $GLOBALS['_PJ_efforts_statistics_script'] . "?list=1&cid=" . $project->giveValue('customer_id') . "&pid=" . $project->giveValue('id') ?>"><?php } ?><?= $project->giveValue('project_name') ?></A></TD>
		<TD CLASS="listDetailNumeric<?php if(isset($rowclass)) echo $rowclass; ?>"><?= $customer->giveValue('customer_name') ?: $_PJ_strings['no_customer'] ?></TD>
		<TD CLASS="listDetailNumeric<?php if(isset($rowclass)) echo $rowclass; ?>">&nbsp;</TD>
		<TD CLASS="listDetailNumeric<?php if(isset($rowclass)) echo $rowclass; ?>"><?php if($project->giveValue('days')) print formatNumber($project->giveValue('days'), true); ?></TD>
		<TD CLASS="listDetailNumeric<?php if(isset($rowclass)) echo $rowclass; ?>"><?php if($project->giveValue('costs')) print formatNumber($project->giveValue('costs'), true) . '&nbsp;' . $GLOBALS['_PJ_currency']; ?></TD>
		<TD CLASS="listDetailNumeric<?php if(isset($rowclass)) echo $rowclass; ?>"><?php if($project->giveValue('project_budget')) print formatNumber($project->giveValue('project_budget'), true) . '&nbsp;' . $GLOBALS['_PJ_currency']; ?></TD>
		<TD CLASS="listDetailNumeric<?php if(isset($rowclass)) echo $rowclass; ?>"><?php if($project->giveValue('project_budget')) print formatNumber($project->giveValue('project_budget')-$project->giveValue('costs'), true) . '&nbsp;' . $GLOBALS['_PJ_currency']; ?></TD>
	</TR><?php
	if(isset($expanded) && (!empty($expanded['pid'][$project->giveValue('id')]) || !empty($expanded['pid']['all']))) {
		// Fix: Check if 'be' key exists to prevent undefined array key warning
		$be_filter = isset($GLOBALS['shown']['be']) ? $GLOBALS['shown']['be'] : '';
		$efforts	= new EffortList($customer, $project, $project->user, $be_filter);
		while($efforts->nextEffort()) {
			$effort = $efforts->giveEffort();
			$row_class = !$row_class;
			include("$_PJ_root/templates/statistic/project/effort/row.ihtml.php");
		}
	}
	?>
<!-- statistic/project/row.ihtml - END -->
