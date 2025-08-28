<!-- inventory/customer/options/edit.ihtml - START -->
		<TABLE CELLPADDING="0" CELLSPACING="0" BORDER="0" WIDTH="100%">
			<TR VALIGN="center">
				<td class="spacer_before_path"></td>
				<TD class="path"><?php include($GLOBALS['_PJ_root'] . '/templates/shared/path.ihtml.php'); ?></TD>
			</TR>
		</TABLE>
		<TABLE CELLPADDING="0" CELLSPACING="0" BORDER="0" WIDTH="100%" BACKGROUND="<?php if(!empty($GLOBALS['_PJ_image_path'])) echo $GLOBALS['_PJ_image_path'] ?>/option-bg.gif">
			<TR>
				<TD VALIGN="top"><TABLE CELLPADDING="0" CELLSPACING="0" BORDER="0">
					<TR HEIGHT="24">
						<TD WIDTH="40"><IMG SRC="<?php if(!empty($GLOBALS['_PJ_image_path'])) echo $GLOBALS['_PJ_image_path'] ?>/abstand.gif" WIDTH="40" HEIGHT="1" BORDER="0"></TD>
						<TD BACKGROUND="<?php if(!empty($GLOBALS['_PJ_image_path'])) echo $GLOBALS['_PJ_image_path'] ?>/option-sb.gif"><IMG SRC="<?php if(!empty($GLOBALS['_PJ_image_path'])) echo $GLOBALS['_PJ_image_path'] ?>/option-bs.gif" BORDER="0"></TD>
						<TD CLASS="option" BACKGROUND="<?php if(!empty($GLOBALS['_PJ_image_path'])) echo $GLOBALS['_PJ_image_path'] ?>/option-sb.gif">&nbsp;&nbsp;<A CLASS="option" HREF="<?= $GLOBALS['_PJ_customer_inventory_script'] . "?edit=1&basic=1&cid=" . $customer->giveValue('id')?> "><?= $GLOBALS['_PJ_strings']['customer_basic_data']?></A>&nbsp;&nbsp;</TD>
						<TD CLASS="option" BACKGROUND="<?php if(!empty($GLOBALS['_PJ_image_path'])) echo $GLOBALS['_PJ_image_path'] ?>/option-sb.gif">&nbsp;&nbsp;<A CLASS="option" HREF="<?= $GLOBALS['_PJ_customer_inventory_script'] . "?edit=1&rates=1&cid=" . $customer->giveValue('id')?> "><?= $GLOBALS['_PJ_strings']['customer_rates_data']?></A></TD>
						<TD CLASS="option" BACKGROUND="<?php if(!empty($GLOBALS['_PJ_image_path'])) echo $GLOBALS['_PJ_image_path'] ?>/option-sb.gif">&nbsp;&nbsp;<A CLASS="option" HREF="<?= $_PJ_http_root ?>/inventory/contracts.php?customer_id=<?= $customer->giveValue('id') ?>"><?php if(!empty($GLOBALS['_PJ_strings']['contracts'])) echo $GLOBALS['_PJ_strings']['contracts']; else echo 'Contracts'; ?></A></TD>
						<TD CLASS="option" BACKGROUND="<?php if(!empty($GLOBALS['_PJ_image_path'])) echo $GLOBALS['_PJ_image_path'] ?>/option-sb.gif">&nbsp;&nbsp;<A CLASS="option" HREF="<?= $_PJ_http_root ?>/invoice/index.php?customer_id=<?= $customer->giveValue('id') ?>"><?php if(!empty($GLOBALS['_PJ_strings']['invoices'])) echo $GLOBALS['_PJ_strings']['invoices']; else echo 'Invoices'; ?></A></TD>
						<TD>&nbsp;</TD>
					</TR>
				</TABLE></TD>
			</TR>
		</TABLE>
<!-- inventory/customer/options/edit.ihtml - END -->
