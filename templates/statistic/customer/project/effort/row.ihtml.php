<?php
	$agent = $_PJ_auth->giveUserById($effort->giveValue('user'));
?>
<!-- statistic/customer/project/effort/row.ihtml - START -->
	<TR>
		<TD CLASS="list<?php if(isset($rowclass)) echo $rowclass; ?>"><IMG SRC="<?php echo $GLOBALS['_PJ_image_path'] ?>/abstand.gif" WIDTH="100%" HEIGHT="1" BORDER="0"></TD>
		<TD COLSPAN="10"><IMG SRC="<?php echo $GLOBALS['_PJ_image_path'] ?>/light-gray.gif" WIDTH="100%" HEIGHT="1" BORDER="0"></TD>
	</TR><TR HEIGHT="25">
		<TD CLASS="list<?php if(isset($rowclass)) echo $rowclass; ?>"><IMG SRC="<?php echo $GLOBALS['_PJ_image_path'] ?>/abstand.gif" BORDER="0" WIDTH="16" HEIGHT="16" ALIGN="absmiddle">&nbsp;<IMG SRC="<?php echo $GLOBALS['_PJ_image_path'] ?>/abstand.gif" BORDER="0" WIDTH="16" HEIGHT="16" ALIGN="absmiddle">&nbsp;<IMG SRC="<?php echo $GLOBALS['_PJ_image_path'] ?>/abstand.gif" BORDER="0" WIDTH="16" HEIGHT="16" ALIGN="absmiddle">&nbsp;<IMG SRC="<?php if(!empty($GLOBALS['_PJ_icon_path'])) echo $GLOBALS['_PJ_icon_path'] ?>/effort<?php if(!($effort->giveValue('billed') == '' || $effort->giveValue('billed') == '0000-00-00')) print 'b' ?>.gif" BORDER="0" WIDTH="16" HEIGHT="16" ALIGN="absmiddle">&nbsp;<A CLASS="list" HREF="<?= $GLOBALS['_PJ_efforts_inventory_script'] . '?edit=1&eid=' . $effort->giveValue('id') ?>"><?= $effort->giveValue('description') ?></A></TD>
		<TD CLASS="listDetailNumeric<?php if(isset($rowclass)) echo $rowclass; ?>"><?= $agent['firstname'] . ' ' . $agent['lastname']; ?></TD>
		<TD CLASS="listDetailNumeric<?php if(isset($rowclass)) echo $rowclass; ?>"><?= formatNumber($effort->giveValue('days'), true); ?></TD>
		<TD CLASS="listDetailNumeric<?php if(isset($rowclass)) echo $rowclass; ?>"><?php 
			$costs = $effort->giveValue('costs');
			$rate = $effort->giveValue('rate');
			if($costs && $rate > 0) {
				print formatNumber($costs, true) . '&nbsp;' . $GLOBALS['_PJ_currency'];
			} else {
				print 'kein Tarif';
			}
		?></TD>
		<TD CLASS="listSubDetailNumeric<?php if(isset($rowclass)) echo $rowclass; ?>" COLSPAN="2"><?= $effort->formatDate($effort->giveValue('date')); ?>, <?= $effort->formatTime($effort->giveValue('begin'), "H:i"); ?> - <?= $effort->formatTime($effort->giveValue('end'), "H:i"); ?></TD>
	</TR>
<!-- statistic/customer/project/effort/row.ihtml - END -->
