<!-- statistic/customer/list.ihtml - START -->
<?php
	include($GLOBALS['_PJ_root'] . '/templates/statistic/customer/options/list.ihtml.php');
?>
<TABLE BORDER="0" CELLPADDING="0" CELLSPACING="0" WIDTH="100%">
	<TR>
		<TD CLASS="content">
		<TABLE BORDER="0" CELLPADDING="0" CELLSPACING="0" WIDTH="100%">
			<TR>
				<TD><TABLE BORDER="0" CELLPADDING="0" CELLSPACING="0" WIDTH="100%">
					<TR>
						<TD>&nbsp;</TD>
					</TR><TR>
						<TD ALIGN="center"><TABLE BORDER="0" CELLPADDING="0" CELLSPACING="0" WIDTH="90%">
							<TR VALIGN="center">
								<TH CLASS="list">
								<A CLASS="list" HREF="<?php if(!empty($GLOBALS['_PJ_pdf_statistics_script'])) echo $GLOBALS['_PJ_pdf_statistics_script'] ?>"><IMG SRC="<?php if(!empty($GLOBALS['_PJ_icon_path'])) echo $GLOBALS['_PJ_icon_path'] ?>/acrobat.gif" BORDER="0" ALT="<?php if(!empty($GLOBALS['_PJ_strings']['createpdf'])) echo $GLOBALS['_PJ_strings']['createpdf'] ?>" WIDTH="16" HEIGHT="16" ALIGN="absmiddle">&nbsp;<?php if(!empty($GLOBALS['_PJ_strings']['createpdf'])) echo $GLOBALS['_PJ_strings']['createpdf'] ?></A>
								&nbsp;&nbsp;&nbsp;&nbsp;
								<A CLASS="list" HREF="<?php if(!empty($GLOBALS['_PJ_csv_statistics_script'])) echo $GLOBALS['_PJ_csv_statistics_script'] ?>"><IMG SRC="<?php if(!empty($GLOBALS['_PJ_icon_path'])) echo $GLOBALS['_PJ_icon_path'] ?>/csv.gif" BORDER="0" ALT="<?php if(!empty($GLOBALS['_PJ_strings']['createcsv'])) echo $GLOBALS['_PJ_strings']['createcsv'] ?>" WIDTH="16" HEIGHT="16" ALIGN="absmiddle">&nbsp;<?php if(!empty($GLOBALS['_PJ_strings']['createcsv'])) echo $GLOBALS['_PJ_strings']['createcsv'] ?></A>
								</TH>
							</TR>
						</TABLE></TD>
					</TR><TR>
						<TD>&nbsp;</TD>
					</TR><TR>
						<TD COLSPAN="3" BGCOLOR="#DDDDDD"><IMG src="<?php if(!empty($GLOBALS['_PJ_image_path'])) echo $GLOBALS['_PJ_image_path'] ?>/abstand.gif" HEIGHT="1" WIDTH="1" BORDER="0"></TD>
					</TR>
				</TABLE></TD>
			</TR><TR>
				<TD ALIGN="center"><TABLE BORDER="0" CELLPADDING="0" CELLSPACING="0" WIDTH="90%">
					<TR>
						<TH CLASS="list"><?php if(!empty($GLOBALS['_PJ_strings']['name'])) echo $GLOBALS['_PJ_strings']['name'] ?></TH>
						<TH CLASS="listNumeric"><?php if(!empty($GLOBALS['_PJ_strings']['agent'])) echo $GLOBALS['_PJ_strings']['agent'] ?></TH>
						<TH CLASS="listNumeric"><?php if(!empty($GLOBALS['_PJ_strings']['workingdays'])) echo $GLOBALS['_PJ_strings']['workingdays'] ?></TH>
						<TH CLASS="listNumeric"><?php if(!empty($GLOBALS['_PJ_strings']['costs'])) echo $GLOBALS['_PJ_strings']['costs'] ?></TH>
						<TH CLASS="listNumeric"><?php if(!empty($GLOBALS['_PJ_strings']['fullbudget'])) echo $GLOBALS['_PJ_strings']['fullbudget'] ?></TH>
						<TH CLASS="listNumeric"><?php if(!empty($GLOBALS['_PJ_strings']['remainingbudget'])) echo $GLOBALS['_PJ_strings']['remainingbudget'] ?></TH>
					</TR>
<?php
	$rowclass = 1;
	while($customer_list->nextCustomer()) {
		$rowclass = !$rowclass;
		$customer = $customer_list->giveCustomer();
		$row_class = !$row_class;
		if(!empty($expanded['cid']['all'])) {
			$expanded['cid'][$customer->giveValue('id')] = 1;
		}
		include("$_PJ_root/templates/statistic/customer/row.ihtml.php");
	}
	if(isset($expanded['cid']['all'])) unset($expanded['cid']['all']);

	// Add unassigned efforts as special customer/project
	$unassigned_efforts = new Statistics($_PJ_auth, true, null, null, null, null, true); // show_unassigned = true
	if($unassigned_efforts->effort_count > 0) {
		// Create virtual customer for unassigned efforts
		$virtual_customer = new stdClass();
		$virtual_customer->customer_name = 'nicht zugeordnet';
		$virtual_customer->id = 0;
		$virtual_customer->active = 'yes';
		
		// Calculate totals for unassigned efforts
		$unassigned_costs = 0;
		$unassigned_days = 0;
		$unassigned_efforts->reset();
		while($unassigned_efforts->nextEffort()) {
			$effort = $unassigned_efforts->giveEffort();
			$unassigned_costs += $effort->giveValue('costs');
			$unassigned_days += $effort->giveValue('days');
		}
		
		// Add to totals
		@$sum_customer_costs += (float)$unassigned_costs;
		@$sum_customer_days += (float)$unassigned_days;
		?>
		<TR>
			<TD COLSPAN="10"><IMG SRC="<?php if(!empty($GLOBALS['_PJ_image_path'])) echo $GLOBALS['_PJ_image_path'] ?>/gray.gif" WIDTH="100%" HEIGHT="2" BORDER="0" ALIGN="absmiddle"></TD>
		</TR><TR HEIGHT="25">
			<TD CLASS="list">&nbsp;<IMG SRC="<?php if(!empty($GLOBALS['_PJ_icon_path'])) echo $GLOBALS['_PJ_icon_path'] ?>/customer.gif" BORDER="0" WIDTH="16" HEIGHT="16" ALIGN="absmiddle">&nbsp;nicht zugeordnet</TD>
			<TD CLASS="listDetail">&nbsp;</TD>
			<TD CLASS="listDetailNumeric"><?php if(!empty($unassigned_days)) print formatNumber($unassigned_days, true); ?></TD>
			<TD CLASS="listDetailNumeric"><?php if(!empty($unassigned_costs)) print formatNumber($unassigned_costs, true) . '&nbsp;' . $GLOBALS['_PJ_currency']; ?></TD>
			<TD CLASS="listDetailNumeric">&nbsp;</TD>
			<TD CLASS="listDetailNumeric">&nbsp;</TD>
		</TR>
		<!-- Project row for unassigned efforts -->
		<TR>
			<TD COLSPAN="11"><IMG SRC="<?php if(!empty($GLOBALS['_PJ_image_path'])) echo $GLOBALS['_PJ_image_path'] ?>/light-gray.gif" WIDTH="100%" HEIGHT="2" BORDER="0" ALIGN="absmiddle"></TD>
		</TR><TR HEIGHT="25">
			<TD CLASS="list">&nbsp;<IMG SRC="<?php if(!empty($GLOBALS['_PJ_image_path'])) echo $GLOBALS['_PJ_image_path'] ?>/abstand.gif" BORDER="0" WIDTH="16" HEIGHT="16" ALIGN="absmiddle">&nbsp;<IMG SRC="<?php if(!empty($GLOBALS['_PJ_icon_path'])) echo $GLOBALS['_PJ_icon_path'] ?>/project.gif" BORDER="0" WIDTH="16" HEIGHT="16" ALIGN="absmiddle">&nbsp;nicht zugeordnet</TD>
			<TD CLASS="listDetail">&nbsp;</TD>
			<TD CLASS="listDetailNumeric"><?php if(!empty($unassigned_days)) print formatNumber($unassigned_days, true); ?></TD>
			<TD CLASS="listDetailNumeric"><?php if(!empty($unassigned_costs)) print formatNumber($unassigned_costs, true) . '&nbsp;' . $GLOBALS['_PJ_currency']; ?></TD>
			<TD CLASS="listDetailNumeric">&nbsp;</TD>
			<TD CLASS="listDetailNumeric">&nbsp;</TD>
		</TR>
		<?php
		// Show individual unassigned efforts
		$unassigned_efforts->reset();
		while($unassigned_efforts->nextEffort()) {
			$effort = $unassigned_efforts->giveEffort();
			$agent = $_PJ_auth->giveUserById($effort->giveValue('user'));
			?>
			<TR>
				<TD CLASS="list"><IMG SRC="<?php if(!empty($GLOBALS['_PJ_image_path'])) echo $GLOBALS['_PJ_image_path'] ?>/abstand.gif" WIDTH="100%" HEIGHT="1" BORDER="0"></TD>
				<TD COLSPAN="10"><IMG SRC="<?php if(!empty($GLOBALS['_PJ_image_path'])) echo $GLOBALS['_PJ_image_path'] ?>/light-gray.gif" WIDTH="100%" HEIGHT="1" BORDER="0"></TD>
			</TR><TR HEIGHT="25">
				<TD CLASS="list"><IMG SRC="<?php if(!empty($GLOBALS['_PJ_image_path'])) echo $GLOBALS['_PJ_image_path'] ?>/abstand.gif" BORDER="0" WIDTH="16" HEIGHT="16" ALIGN="absmiddle">&nbsp;<IMG SRC="<?php if(!empty($GLOBALS['_PJ_image_path'])) echo $GLOBALS['_PJ_image_path'] ?>/abstand.gif" BORDER="0" WIDTH="16" HEIGHT="16" ALIGN="absmiddle">&nbsp;<IMG SRC="<?php if(!empty($GLOBALS['_PJ_image_path'])) echo $GLOBALS['_PJ_image_path'] ?>/abstand.gif" BORDER="0" WIDTH="16" HEIGHT="16" ALIGN="absmiddle">&nbsp;<IMG SRC="<?php if(!empty($GLOBALS['_PJ_icon_path'])) echo $GLOBALS['_PJ_icon_path'] ?>/effort<?php if(!($effort->giveValue('billed') == '' || $effort->giveValue('billed') == '0000-00-00')) print 'b' ?>.gif" BORDER="0" WIDTH="16" HEIGHT="16" ALIGN="absmiddle">&nbsp;<A CLASS="list" HREF="<?= $GLOBALS['_PJ_efforts_inventory_script'] . '?eid=' . $effort->giveValue('id') ?>"><?= $effort->giveValue('description') ?></A></TD>
				<TD CLASS="listDetailNumeric"><?= $agent['firstname'] . ' ' . $agent['lastname']; ?></TD>
				<TD CLASS="listDetailNumeric"><?= formatNumber($effort->giveValue('days'), true); ?></TD>
				<TD CLASS="listDetailNumeric"><?php 
					$costs = $effort->giveValue('costs');
					$rate = $effort->giveValue('rate');
					if($costs && $rate > 0) {
						print formatNumber($costs, true) . '&nbsp;' . $GLOBALS['_PJ_currency'];
					} else {
						print 'kein Tarif';
					}
				?></TD>
				<TD CLASS="listSubDetailNumeric" COLSPAN="2"><?= $effort->formatDate($effort->giveValue('date')); ?>, <?= $effort->formatTime($effort->giveValue('begin'), "H:i"); ?> - <?= $effort->formatTime($effort->giveValue('end'), "H:i"); ?></TD>
			</TR>
			<?php
		}
	}
?>
					<TR>
						<TD COLSPAN="10"><IMG SRC="<?php if(!empty($GLOBALS['_PJ_image_path'])) echo $GLOBALS['_PJ_image_path'] ?>/gray.gif" WIDTH="100%" HEIGHT="1" BORDER="0"></TD>
					</TR><TR>
						<TD COLSPAN="10"><IMG SRC="<?php if(!empty($GLOBALS['_PJ_image_path'])) echo $GLOBALS['_PJ_image_path'] ?>/abstand.gif" WIDTH="1" HEIGHT="20" BORDER="0"></TD>
					</TR><TR HEIGHT="25">
						<TD>&nbsp;</TD>
						<TD CLASS="listSum" ALIGN="right"><?php if(!empty($GLOBALS['_PJ_strings']['sum'])) echo $GLOBALS['_PJ_strings']['sum'] ?>:</TD>
						<TD CLASS="listSumNumeric"><?php if(!empty($sum_customer_days)) print formatNumber($sum_customer_days, true); ?></TD>
						<TD CLASS="listSumNumeric"><?php if(!empty($sum_customer_costs)) print formatNumber($sum_customer_costs, true) . '&nbsp;' . $GLOBALS['_PJ_currency']; ?></TD>
						<TD CLASS="listSumNumeric"><?php if(!empty($sum_customer_full_budget)) print formatNumber($sum_customer_full_budget, true) . '&nbsp;' . $GLOBALS['_PJ_currency']; ?></TD>
						<TD CLASS="listSumNumeric"><?php if(isset($sum_customer_full_budget) && $sum_customer_full_budget && $sum_customer_remaining_budget) print formatNumber($sum_customer_remaining_budget, true) . '&nbsp;' . $GLOBALS['_PJ_currency']; ?></TD>
					</TR><TR>
						<TD COLSPAN="10"><IMG SRC="<?php if(!empty($GLOBALS['_PJ_image_path'])) echo $GLOBALS['_PJ_image_path'] ?>/gray.gif" WIDTH="100%" HEIGHT="1" BORDER="0"></TD>
					</TR>
				</TABLE></TD>
			</TR><TR>
				<TD ALIGN="center"><TABLE BORDER="0" CELLPADDING="0" CELLSPACING="0" WIDTH="90%">
					<TR>
						<TD COLSPAN="2"><IMG src="<?php if(!empty($GLOBALS['_PJ_image_path'])) echo $GLOBALS['_PJ_image_path'] ?>/abstand.gif" HEIGHT="3" WIDTH="1" BORDER="0"></TD>
					</TR><TR>
						<TD ALIGN="left"><?php
if(empty($shown['ic'])) {
						?><A CLASS="listFoot" HREF="<?= $GLOBALS['_PJ_customer_statistics_script'] . "?sic=1&cid=".@$cid.'&pid='.@$pid.'&eid='.@$eid.""; ?>"><IMG SRC="<?php if(!empty($GLOBALS['_PJ_icon_path'])) echo $GLOBALS['_PJ_icon_path'] ?>/show-closed.gif" WIDTH="16" HEIGHT="16" BORDER="0" ALIGN="absmiddle">&nbsp;<?php if(!empty($GLOBALS['_PJ_strings']['show_closed_customers'])) echo $GLOBALS['_PJ_strings']['show_closed_customers'] ?></A>&nbsp;|&nbsp;<?= $customer_list->inactive_count . " " . $GLOBALS['_PJ_strings']['inactive_hidden'] ?><?php
} else {
						?><A CLASS="listFoot" HREF="<?= $GLOBALS['_PJ_customer_statistics_script'] . "?sic=0&cid=".@$cid.'&pid='.@$pid.'&eid='.@$eid.""; ?>"><IMG SRC="<?php if(!empty($GLOBALS['_PJ_icon_path'])) echo $GLOBALS['_PJ_icon_path'] ?>/hide-closed.gif" WIDTH="16" HEIGHT="16" BORDER="0" ALIGN="absmiddle">&nbsp;<?php if(!empty($GLOBALS['_PJ_strings']['hide_closed_customers'])) echo $GLOBALS['_PJ_strings']['hide_closed_customers'] ?></A><?php
}
						?>
						
						</TD>
						<TD ALIGN="right">
						<A CLASS="listFoot" HREF="<?= $GLOBALS['_PJ_customer_statistics_script'] . "?exca=1&cid=".@$cid.'&pid='.@$pid.'&eid='.@$eid.""; ?>"><IMG SRC="<?php if(!empty($GLOBALS['_PJ_icon_path'])) echo $GLOBALS['_PJ_icon_path'] ?>/triangle-d.gif" WIDTH="16" HEIGHT="16" BORDER="0" ALIGN="absmiddle">&nbsp;<?php if(!empty($GLOBALS['_PJ_strings']['expand_all'])) echo $GLOBALS['_PJ_strings']['expand_all'] ?></A> |
						<A CLASS="listFoot" HREF="<?= $GLOBALS['_PJ_customer_statistics_script'] . "?coca=1&cid=".@$cid.'&pid='.@$pid.'&eid='.@$eid.""; ?>"><IMG SRC="<?php if(!empty($GLOBALS['_PJ_icon_path'])) echo $GLOBALS['_PJ_icon_path'] ?>/triangle-l.gif" WIDTH="16" HEIGHT="16" BORDER="0" ALIGN="absmiddle">&nbsp;<?php if(!empty($GLOBALS['_PJ_strings']['collapse_all'])) echo $GLOBALS['_PJ_strings']['collapse_all'] ?>
						</TD>
					</TR>
				</TABLE></TD>
			</TR>
		</TABLE></TD>
	</TR>
</TABLE>
<!-- statistic/customer/list.ihtml - END -->
