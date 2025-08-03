<!-- inventory/effort/list.ihtml - START -->
<?php
	include($GLOBALS['_PJ_root'] . '/templates/inventory/effort/options/list.ihtml.php');
?>
<TABLE	WIDTH="100%"
		BORDER="<?php print($_PJ_inner_frame_border); ?>"
		CELLPADDING="<?php print($_PJ_inner_frame_cellpadding); ?>"
		CELLSPACING="<?php print($_PJ_inner_frame_cellspacing ); ?>">
	<TR>
		<TD CLASS="content">
		<TABLE BORDER="0" CELLPADDING="0" CELLSPACING="0" WIDTH="100%">
			<TR>
				<TD COLSPAN="3"><TABLE BORDER="0" CELLPADDING="0" CELLSPACING="0" WIDTH="100%">
					<TR>
						<TD>&nbsp;</TD>
					</TR><TR>
						<TD ALIGN="center"><TABLE BORDER="0" CELLPADDING="0" CELLSPACING="0" WIDTH="90%">
							<TR VALIGN="center">
								<TH CLASS="list"><?php
if($pid && $project && $project->checkUserAccess('new')) {
?><A CLASS="list" HREF="<?= $GLOBALS['_PJ_efforts_inventory_script'] . "?new=1&cid=$cid&pid=$pid"; ?>"><IMG SRC="<?php if(!empty($GLOBALS['_PJ_icon_path'])) echo $GLOBALS['_PJ_icon_path'] ?>/effort.gif" BORDER="0" WIDTH="16" HEIGHT="16" ALT="<?php if(!empty($GLOBALS['_PJ_strings']['new_effort'])) echo $GLOBALS['_PJ_strings']['new_effort'] ?>" ALIGN="absmiddle">&nbsp;<?php if(!empty($GLOBALS['_PJ_strings']['new_effort'])) echo $GLOBALS['_PJ_strings']['new_effort'] ?></A><?php
} else { ?>&nbsp;<?php
} ?></TH>
							</TR>
						</TABLE></TD>
					</TR><TR>
						<TD>&nbsp;</TD>
					</TR><TR>
						<TD COLSPAN="3" BGCOLOR="#DDDDDD"><IMG src="<?php if(!empty($GLOBALS['_PJ_image_path'])) echo $GLOBALS['_PJ_image_path'] ?>/abstand.gif" HEIGHT="1" WIDTH="1" BORDER="0"></TD>
					</TR><TR>
						<TD>&nbsp;</TD>
					</TR>
				</TABLE></TD>
			</TR><TR>
				<TD ALIGN="center"><TABLE BORDER="0" CELLPADDING="0" CELLSPACING="0" WIDTH="90%">
					<TR>
						<TH CLASS="list" WIDTH="30%"><?php if(!empty($GLOBALS['_PJ_strings']['description'])) echo $GLOBALS['_PJ_strings']['description'] ?></TH>
						<?php if(empty($cid)) { ?><TH CLASS="list"><?php if(!empty($GLOBALS['_PJ_strings']['customer'])) echo $GLOBALS['_PJ_strings']['customer']; ?></TH><?php } ?>
						<?php if(empty($pid)) { ?><TH CLASS="list"><?php if(!empty($GLOBALS['_PJ_strings']['project'])) echo $GLOBALS['_PJ_strings']['project']; ?></TH><?php } ?>
						<TH CLASS="list"><?php if(!empty($GLOBALS['_PJ_strings']['agent'])) echo $GLOBALS['_PJ_strings']['agent']; ?></TH>
						<TH CLASS="list" WIDTH="150">
							<?php if(!empty($GLOBALS['_PJ_strings']['date'])) echo $GLOBALS['_PJ_strings']['date'] ?>
							<a href="<?= $_SERVER['PHP_SELF'] ?>?<?= http_build_query(array_merge($_GET, ['sort' => ($_GET['sort'] ?? 'desc') === 'desc' ? 'asc' : 'desc'])) ?>" class="sort-toggle" title="Sortierung umkehren">
								<?= ($_GET['sort'] ?? 'desc') === 'desc' ? '↓' : '↑' ?>
							</a>
						</TH>
						<TH CLASS="listNumeric"><?php if(!empty($GLOBALS['_PJ_strings']['workinghours'])) echo $GLOBALS['_PJ_strings']['workinghours']; ?></TH>
						<TH CLASS="listNumeric" WIDTH="200"><?= $GLOBALS['_PJ_strings']['costs'] . '&nbsp;' . $GLOBALS['_PJ_currency']; ?></TH>
						<TH CLASS="list">&nbsp;</TH>
						<TH CLASS="list">&nbsp;</TH>
					</TR>
<?php
	$rowclass = 1;
	while($efforts->nextEffort()) {
		$rowclass = !$rowclass;
		$effort = $efforts->giveEffort();
		$row_class = !$row_class;
		include("$_PJ_root/templates/inventory/effort/row.ihtml.php");
	}
?>
					<TR>
						<TD COLSPAN="<?php echo (empty($cid) ? 1 : 0) + (empty($pid) ? 1 : 0) + 8; ?>"><IMG SRC="<?php if(!empty($GLOBALS['_PJ_image_path'])) echo $GLOBALS['_PJ_image_path'] ?>/gray.gif" WIDTH="100%" HEIGHT="1" BORDER="0"></TD>
					</TR>
<?php
	// Calculate and display totals
	$total_hours = $efforts->getTotalHours();
	$total_costs = $efforts->getTotalCosts();
	$total_days = $efforts->getTotalDays();
	if($total_hours > 0 || $total_costs > 0) {
?>
					<TR>
						<TD COLSPAN="<?php echo (empty($cid) ? 1 : 0) + (empty($pid) ? 1 : 0) + 8; ?>"><IMG SRC="<?php if(!empty($GLOBALS['_PJ_image_path'])) echo $GLOBALS['_PJ_image_path'] ?>/gray.gif" WIDTH="100%" HEIGHT="2" BORDER="0"></TD>
					</TR>
					<TR HEIGHT="25">
						<TD CLASS="listSum" WIDTH="35%">&nbsp;<B><?= $GLOBALS['_PJ_strings']['sum'] ?>:</B></TD>
						<TD CLASS="listSum">&nbsp;</TD>
						<TD CLASS="listSumNumeric">&nbsp;<B><?= formatNumber($total_days, true) ?> <?= $GLOBALS['_PJ_strings']['workingdays'] ?> (<?= formatNumber($total_hours, true) ?> h)</B>&nbsp;</TD>
						<TD CLASS="listSumNumeric" WIDTH="200">&nbsp;<B><?= formatNumber($total_costs, true) . '&nbsp;' . $GLOBALS['_PJ_currency'] ?></B></TD>
						<TD CLASS="listSum">&nbsp;</TD>
						<TD CLASS="listSum">&nbsp;</TD>
					</TR>
<?php
	}
?>
					<TR>
						<TD COLSPAN="<?php echo (empty($cid) ? 1 : 0) + (empty($pid) ? 1 : 0) + 8; ?>"><IMG SRC="<?php if(!empty($GLOBALS['_PJ_image_path'])) echo $GLOBALS['_PJ_image_path'] ?>/gray.gif" WIDTH="100%" HEIGHT="1" BORDER="0"></TD>
					</TR>
				</TABLE></TD>
			</TR><TR>
				<TD ALIGN="center"><TABLE BORDER="0" CELLPADDING="0" CELLSPACING="0" WIDTH="90%">
					<TR>
						<TD COLSPAN="2"><IMG src="<?php if(!empty($GLOBALS['_PJ_image_path'])) echo $GLOBALS['_PJ_image_path'] ?>/abstand.gif" HEIGHT="3" WIDTH="1" BORDER="0"></TD>
					</TR><TR>
						<TD CLASS="listFoot" ALIGN="left"><?php
if(empty($shown['be'])) {
						?><A CLASS="listFoot" HREF="<?= $GLOBALS['_PJ_efforts_inventory_script'] . "?sbe=1&cid=".@$cid.'&pid='.@$pid.'&eid='.@$eid.""; ?>"><IMG SRC="<?php if(!empty($GLOBALS['_PJ_icon_path'])) echo $GLOBALS['_PJ_icon_path'] ?>/show-closed.gif" WIDTH="16" HEIGHT="16" BORDER="0" ALIGN="absmiddle">&nbsp;<?php if(!empty($GLOBALS['_PJ_strings']['show_closed_efforts'])) echo $GLOBALS['_PJ_strings']['show_closed_efforts'] ?></A><?php
} else {
						?><A CLASS="listFoot" HREF="<?= $GLOBALS['_PJ_efforts_inventory_script'] . "?sbe=0&cid=".@$cid.'&pid='.@$pid.'&eid='.@$eid.""; ?>"><IMG SRC="<?php if(!empty($GLOBALS['_PJ_icon_path'])) echo $GLOBALS['_PJ_icon_path'] ?>/hide-closed.gif" WIDTH="16" HEIGHT="16" BORDER="0" ALIGN="absmiddle">&nbsp;<?php if(!empty($GLOBALS['_PJ_strings']['hide_closed_efforts'])) echo $GLOBALS['_PJ_strings']['hide_closed_efforts'] ?></A><?php
}
						?></TD>
						<TD CLASS="listFoot" ALIGN="right">&nbsp;</TD>
					</TR>
				</TABLE></TD>
			</TR>
		</TABLE></TD>
	</TR>
</TABLE>
<!-- inventory/effort/list.ihtml - END -->
