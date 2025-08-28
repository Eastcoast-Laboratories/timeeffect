<!-- shared/invoice/left.ihtml - START -->
<?php
	$max_length	= 17;
	$nav_width = 120;
?>
<TABLE CELLPADDING="0" CELLSPACING="0" BORDER="0" WIDTH="160" HEIGHT="100%">
	<TR>
		<TD VALIGN="top" CLASS="leftNavi"><TABLE CELLPADDING="3" CELLSPACING="0" BORDER="0">
			<TR HEIGHT="150">
				<TD CLASS="headFrame" COLSPAN="2" VALIGN="top"><IMG SRC="<?php if(!empty($GLOBALS['_PJ_image_path'])) echo $GLOBALS['_PJ_image_path'] ?>/logo_te_150.png" WIDTH="150" HEIGHT="19" BORDER="0" HSPACE="5" VSPACE="0"></TD>
			</TR><TR>
				<TD><IMG SRC="<?php if(!empty($GLOBALS['_PJ_image_path'])) echo $GLOBALS['_PJ_image_path'] ?>/abstand.gif" WIDTH="1" HEIGHT="20" BORDER="0"></TD>
			</TR><TR>
				<TD WIDTH="10" ROWSPAN="30"><IMG SRC="<?php if(!empty($GLOBALS['_PJ_image_path'])) echo $GLOBALS['_PJ_image_path'] ?>/abstand.gif" WIDTH="10" HEIGHT="1" BORDER="0"></TD>
				<TD CLASS="leftHead"><?=$GLOBALS['_PJ_strings']['navigation']?></TD>
			</TR><TR>
				<TD><IMG SRC="<?php if(!empty($GLOBALS['_PJ_image_path'])) echo $GLOBALS['_PJ_image_path'] ?>/gray.gif" WIDTH="<?php if(isset($nav_width)) echo $nav_width; ?>" HEIGHT="1" BORDER="0"></TD>
			</TR>
			
			<?php if(isset($invoice_data) && $invoice_data): ?>
			<!-- Current Invoice Info -->
			<TR>
				<TD CLASS="leftHead">Invoice #<?= $invoice_data['invoice_number'] ?></TD>
			</TR><TR>
				<TD><IMG SRC="<?php if(!empty($GLOBALS['_PJ_image_path'])) echo $GLOBALS['_PJ_image_path'] ?>/gray.gif" WIDTH="<?php if(isset($nav_width)) echo $nav_width; ?>" HEIGHT="1" BORDER="0"></TD>
			</TR>
			
			<!-- Customer Navigation -->
			<?php if($invoice_data['customer_id']): ?>
			<TR>
				<TD><IMG SRC="<?php if(!empty($GLOBALS['_PJ_icon_path'])) echo $GLOBALS['_PJ_icon_path'] ?>/customer.gif" WIDTH="16" HEIGHT="16" BORDER="0" ALIGN="absmiddle">&nbsp;<A CLASS="left" HREF="<?= $GLOBALS['_PJ_customer_inventory_script'] ?>?edit=1&cid=<?= $invoice_data['customer_id'] ?>">View Customer</A>&nbsp;</TD>
			</TR><TR>
				<TD><IMG SRC="<?php if(!empty($GLOBALS['_PJ_icon_path'])) echo $GLOBALS['_PJ_icon_path'] ?>/acrobat.gif" WIDTH="16" HEIGHT="16" BORDER="0" ALIGN="absmiddle">&nbsp;<A CLASS="left" HREF="<?= $GLOBALS['_PJ_reports_script'] ?>?cid=<?= $invoice_data['customer_id'] ?>&syear=<?= date('Y', strtotime($invoice_data['period_start'])) ?>&smonth=<?= date('n', strtotime($invoice_data['period_start'])) ?>&sday=<?= date('j', strtotime($invoice_data['period_start'])) ?>&eyear=<?= date('Y', strtotime($invoice_data['period_end'])) ?>&emonth=<?= date('n', strtotime($invoice_data['period_end'])) ?>&eday=<?= date('j', strtotime($invoice_data['period_end'])) ?>">Customer Report</A>&nbsp;</TD>
			</TR>
			
			<!-- Project Navigation (if project is set) -->
			<?php if($invoice_data['project_id']): ?>
			<TR>
				<TD><IMG SRC="<?php if(!empty($GLOBALS['_PJ_icon_path'])) echo $GLOBALS['_PJ_icon_path'] ?>/project.gif" WIDTH="16" HEIGHT="16" BORDER="0" ALIGN="absmiddle">&nbsp;<A CLASS="left" HREF="<?= $GLOBALS['_PJ_projects_inventory_script'] ?>?edit=1&pid=<?= $invoice_data['project_id'] ?>">View Project</A>&nbsp;</TD>
			</TR>
			<?php endif; ?>
			
			<!-- Efforts for this period -->
			<TR>
				<TD><IMG SRC="<?php if(!empty($GLOBALS['_PJ_icon_path'])) echo $GLOBALS['_PJ_icon_path'] ?>/effort.gif" WIDTH="16" HEIGHT="16" BORDER="0" ALIGN="absmiddle">&nbsp;<A CLASS="left" HREF="<?= $GLOBALS['_PJ_efforts_inventory_script'] ?>?cid=<?= $invoice_data['customer_id'] ?><?= $invoice_data['project_id'] ? '&pid=' . $invoice_data['project_id'] : '' ?>&syear=<?= date('Y', strtotime($invoice_data['period_start'])) ?>&smonth=<?= date('n', strtotime($invoice_data['period_start'])) ?>&sday=<?= date('j', strtotime($invoice_data['period_start'])) ?>&eyear=<?= date('Y', strtotime($invoice_data['period_end'])) ?>&emonth=<?= date('n', strtotime($invoice_data['period_end'])) ?>&eday=<?= date('j', strtotime($invoice_data['period_end'])) ?>">Period Efforts</A>&nbsp;</TD>
			</TR>
			<?php endif; ?>
			<?php endif; ?>

			<!-- Spacer -->
			<TR>
				<TD><IMG SRC="<?php if(!empty($GLOBALS['_PJ_image_path'])) echo $GLOBALS['_PJ_image_path'] ?>/abstand.gif" WIDTH="1" HEIGHT="30" BORDER="0"></TD>
			</TR>

			<!-- General Navigation -->
			<TR>
				<TD CLASS="leftHead"><?php if(!empty($GLOBALS['_PJ_strings']['inventory'])) echo $GLOBALS['_PJ_strings']['inventory'] ?></TD>
			</TR><TR>
				<TD><IMG SRC="<?php if(!empty($GLOBALS['_PJ_image_path'])) echo $GLOBALS['_PJ_image_path'] ?>/gray.gif" WIDTH="<?php if(isset($nav_width)) echo $nav_width; ?>" HEIGHT="1" BORDER="0"></TD>
			</TR><TR>
				<TD><IMG SRC="<?php if(!empty($GLOBALS['_PJ_icon_path'])) echo $GLOBALS['_PJ_icon_path'] ?>/customer.gif" WIDTH="16" HEIGHT="16" BORDER="0" ALIGN="absmiddle">&nbsp;<A CLASS="left" HREF="<?= $GLOBALS['_PJ_customer_inventory_script'] ?><?= (isset($invoice_data) && $invoice_data['customer_id']) ? '?cid=' . $invoice_data['customer_id'] : '' ?>"><?php if(!empty($GLOBALS['_PJ_strings']['customers'])) echo $GLOBALS['_PJ_strings']['customers'] ?></A>&nbsp;</TD>
			</TR><TR>
				<TD><IMG SRC="<?php if(!empty($GLOBALS['_PJ_icon_path'])) echo $GLOBALS['_PJ_icon_path'] ?>/project.gif" WIDTH="16" HEIGHT="16" BORDER="0" ALIGN="absmiddle">&nbsp;<A CLASS="left" HREF="<?= $GLOBALS['_PJ_projects_inventory_script'] ?><?= (isset($invoice_data) && $invoice_data['customer_id']) ? '?cid=' . $invoice_data['customer_id'] : '' ?><?= (isset($invoice_data) && $invoice_data['project_id']) ? '&pid=' . $invoice_data['project_id'] : '' ?>"><?php if(!empty($GLOBALS['_PJ_strings']['projects'])) echo $GLOBALS['_PJ_strings']['projects'] ?></A>&nbsp;</TD>
			</TR><TR>
				<TD><IMG SRC="<?php if(!empty($GLOBALS['_PJ_icon_path'])) echo $GLOBALS['_PJ_icon_path'] ?>/effort.gif" WIDTH="16" HEIGHT="16" BORDER="0" ALIGN="absmiddle">&nbsp;<A CLASS="left" HREF="<?= $GLOBALS['_PJ_efforts_inventory_script'] ?><?= (isset($invoice_data) && $invoice_data['customer_id']) ? '?cid=' . $invoice_data['customer_id'] : '' ?><?= (isset($invoice_data) && $invoice_data['project_id']) ? '&pid=' . $invoice_data['project_id'] : '' ?>"><?php if(!empty($GLOBALS['_PJ_strings']['efforts'])) echo $GLOBALS['_PJ_strings']['efforts'] ?></A>&nbsp;</TD>
			</TR><TR>
				<TD><IMG SRC="<?php if(!empty($GLOBALS['_PJ_icon_path'])) echo $GLOBALS['_PJ_icon_path'] ?>/acrobat.gif" WIDTH="16" HEIGHT="16" BORDER="0" ALIGN="absmiddle">&nbsp;<A CLASS="left" HREF="<?= $GLOBALS['_PJ_reports_script'] ?><?= (isset($invoice_data) && $invoice_data['customer_id']) ? '?cid=' . $invoice_data['customer_id'] : '' ?><?= (isset($invoice_data) && $invoice_data['project_id']) ? '&pid=' . $invoice_data['project_id'] : '' ?>"><?php if(!empty($GLOBALS['_PJ_strings']['reports'])) echo $GLOBALS['_PJ_strings']['reports'] ?></A>&nbsp;</TD>
			</TR>
		</TABLE></TD>
	</TR><TR>
		<td VALIGN="bottom" CLASS="leftNavi" align="center"><br><br><a href="https://github.com/rubo77/timeeffect" target="_blank">TIMEEFFECT on GitHub</a><br><br></td>
<?php
if($GLOBALS['_PJ_session_length']) {
?>
	</TR><TR>
		<TD CLASS="leftNaviInfo">&nbsp;<?php if(!empty($GLOBALS['_PJ_strings']['session_timeout'])) echo $GLOBALS['_PJ_strings']['session_timeout'] ?>: <?php
// Fix: Cast string to int before modulo operation for PHP 8.4 compatibility
$session_timeout = (int)$GLOBALS['_PJ_session_timeout'];
printf("%dm %02ds", (($session_timeout-($session_timeout%60))/60), ($session_timeout%60));
		?></TD>
<?php
}
?>
	</TR>
</TABLE>

<!-- shared/invoice/left.ihtml - END -->
