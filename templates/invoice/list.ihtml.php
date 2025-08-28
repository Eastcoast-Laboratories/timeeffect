<!-- invoice/list.ihtml - START -->
<TABLE CELLPADDING="0" CELLSPACING="0" BORDER="0" WIDTH="100%">
    <TR VALIGN="center">
        <td class="spacer_before_path"></td>
        <TD class="path"><?php include($GLOBALS['_PJ_root'] . '/templates/shared/path.ihtml.php'); ?></TD>
    </TR>
</TABLE>

<TABLE CELLPADDING="0" CELLSPACING="0" BORDER="0" WIDTH="100%" BACKGROUND="<?php if(!empty($GLOBALS['_PJ_image_path'])) echo $GLOBALS['_PJ_image_path'] ?>/option-bg.gif">
    <TR>
        <TD VALIGN="top">
            <TABLE CELLPADDING="0" CELLSPACING="0" BORDER="0">
                <TR HEIGHT="24">
                    <TD WIDTH="40"><IMG SRC="<?php if(!empty($GLOBALS['_PJ_image_path'])) echo $GLOBALS['_PJ_image_path'] ?>/abstand.gif" WIDTH="40" HEIGHT="1" BORDER="0"></TD>
                    <TD BACKGROUND="<?php if(!empty($GLOBALS['_PJ_image_path'])) echo $GLOBALS['_PJ_image_path'] ?>/option-sb.gif"><IMG SRC="<?php if(!empty($GLOBALS['_PJ_image_path'])) echo $GLOBALS['_PJ_image_path'] ?>/option-bs.gif" BORDER="0"></TD>
                    <TD CLASS="option" BACKGROUND="<?php if(!empty($GLOBALS['_PJ_image_path'])) echo $GLOBALS['_PJ_image_path'] ?>/option-sb.gif">&nbsp;&nbsp;<A CLASS="option" HREF="create.php"><?php if(!empty($GLOBALS['_PJ_strings']['new_invoice'])) echo $GLOBALS['_PJ_strings']['new_invoice']; else echo 'New Invoice'; ?></A>&nbsp;&nbsp;</TD>
                    <TD CLASS="option" BACKGROUND="<?php if(!empty($GLOBALS['_PJ_image_path'])) echo $GLOBALS['_PJ_image_path'] ?>/option-sb.gif">&nbsp;&nbsp;<A CLASS="option" HREF="../index.php"><?php if(!empty($GLOBALS['_PJ_strings']['back_to_main'])) echo $GLOBALS['_PJ_strings']['back_to_main']; else echo 'Back to Main'; ?></A>&nbsp;&nbsp;</TD>
                    <TD>&nbsp;</TD>
                </TR>
            </TABLE>
        </TD>
    </TR>
</TABLE>

<TABLE CELLPADDING="0" CELLSPACING="0" BORDER="0" WIDTH="100%">
    <TR>
        <TD WIDTH="40"><IMG SRC="<?php if(!empty($GLOBALS['_PJ_image_path'])) echo $GLOBALS['_PJ_image_path'] ?>/abstand.gif" WIDTH="40" HEIGHT="1" BORDER="0"></TD>
        <TD VALIGN="top">

            <!-- Filters -->
            <TABLE CELLPADDING="0" CELLSPACING="0" BORDER="<?php print($_PJ_inner_frame_border); ?>" WIDTH="100%" CLASS="form">
                <TR>
                    <TD CLASS="FormTitle" COLSPAN="6"><?php if(!empty($GLOBALS['_PJ_strings']['filter_invoices'])) echo $GLOBALS['_PJ_strings']['filter_invoices']; else echo 'Filter Invoices'; ?></TD>
                </TR>
                <FORM METHOD="GET">
                    <TR>
                        <TD CLASS="FormFieldName"><?php if(!empty($GLOBALS['_PJ_strings']['customer'])) echo $GLOBALS['_PJ_strings']['customer']; else echo 'Customer'; ?>:</TD>
                        <TD CLASS="FormField">
                            <SELECT NAME="customer_id" CLASS="FormSelect">
                                <OPTION VALUE=""><?php if(!empty($GLOBALS['_PJ_strings']['all_customers'])) echo $GLOBALS['_PJ_strings']['all_customers']; else echo 'All Customers'; ?></OPTION>
                                <?php foreach ($customers as $customer): ?>
                                    <OPTION VALUE="<?php echo $customer['id']; ?>" 
                                            <?php echo (isset($_GET['customer_id']) && $_GET['customer_id'] == $customer['id']) ? 'selected' : ''; ?>>
                                        <?php echo htmlspecialchars($customer['name']); ?>
                                    </OPTION>
                                <?php endforeach; ?>
                            </SELECT>
                        </TD>
                        <TD CLASS="FormFieldName"><?php if(!empty($GLOBALS['_PJ_strings']['status'])) echo $GLOBALS['_PJ_strings']['status']; else echo 'Status'; ?>:</TD>
                        <TD CLASS="FormField">
                            <SELECT NAME="status" CLASS="FormSelect">
                                <OPTION VALUE=""><?php if(!empty($GLOBALS['_PJ_strings']['all_status'])) echo $GLOBALS['_PJ_strings']['all_status']; else echo 'All Status'; ?></OPTION>
                                <OPTION VALUE="draft" <?php echo (isset($_GET['status']) && $_GET['status'] == 'draft') ? 'selected' : ''; ?>><?php if(!empty($GLOBALS['_PJ_strings']['draft'])) echo $GLOBALS['_PJ_strings']['draft']; else echo 'Draft'; ?></OPTION>
                                <OPTION VALUE="sent" <?php echo (isset($_GET['status']) && $_GET['status'] == 'sent') ? 'selected' : ''; ?>><?php if(!empty($GLOBALS['_PJ_strings']['sent'])) echo $GLOBALS['_PJ_strings']['sent']; else echo 'Sent'; ?></OPTION>
                                <OPTION VALUE="paid" <?php echo (isset($_GET['status']) && $_GET['status'] == 'paid') ? 'selected' : ''; ?>><?php if(!empty($GLOBALS['_PJ_strings']['paid'])) echo $GLOBALS['_PJ_strings']['paid']; else echo 'Paid'; ?></OPTION>
                                <OPTION VALUE="cancelled" <?php echo (isset($_GET['status']) && $_GET['status'] == 'cancelled') ? 'selected' : ''; ?>><?php if(!empty($GLOBALS['_PJ_strings']['cancelled'])) echo $GLOBALS['_PJ_strings']['cancelled']; else echo 'Cancelled'; ?></OPTION>
                            </SELECT>
                        </TD>
                        <TD CLASS="FormFieldName"><?php if(!empty($GLOBALS['_PJ_strings']['from'])) echo $GLOBALS['_PJ_strings']['from']; else echo 'From'; ?>:</TD>
                        <TD CLASS="FormField">
                            <INPUT TYPE="date" NAME="date_from" CLASS="FormInput" 
                                   VALUE="<?php echo htmlspecialchars($_GET['date_from'] ?? ''); ?>">
                        </TD>
                    </TR>
                    <TR>
                        <TD CLASS="FormFieldName"><?php if(!empty($GLOBALS['_PJ_strings']['to'])) echo $GLOBALS['_PJ_strings']['to']; else echo 'To'; ?>:</TD>
                        <TD CLASS="FormField">
                            <INPUT TYPE="date" NAME="date_to" CLASS="FormInput" 
                                   VALUE="<?php echo htmlspecialchars($_GET['date_to'] ?? ''); ?>">
                        </TD>
                        <TD CLASS="FormFieldName">&nbsp;</TD>
                        <TD CLASS="FormField">
                            <INPUT TYPE="submit" VALUE="<?php if(!empty($GLOBALS['_PJ_strings']['filter'])) echo $GLOBALS['_PJ_strings']['filter']; else echo 'Filter'; ?>" CLASS="FormButton">
                            <INPUT TYPE="button" VALUE="<?php if(!empty($GLOBALS['_PJ_strings']['clear'])) echo $GLOBALS['_PJ_strings']['clear']; else echo 'Clear'; ?>" CLASS="FormButton" ONCLICK="window.location.href='index.php'">
                        </TD>
                        <TD COLSPAN="2">&nbsp;</TD>
                    </TR>
                </FORM>
            </TABLE>
            <BR>

            <!-- Invoice List -->
            <TABLE CELLPADDING="0" CELLSPACING="0" BORDER="<?php print($_PJ_inner_frame_border); ?>" WIDTH="100%" CLASS="list">
                <TR>
                    <TD CLASS="ListTitle" COLSPAN="8"><?php if(!empty($GLOBALS['_PJ_strings']['invoice_management'])) echo $GLOBALS['_PJ_strings']['invoice_management']; else echo 'Invoice Management'; ?></TD>
                </TR>

                <?php if (empty($invoices)): ?>
                    <TR>
                        <TD CLASS="ListContent" COLSPAN="8" ALIGN="center" STYLE="padding: 40px;">
                            <P><?php if(!empty($GLOBALS['_PJ_strings']['no_invoices_found'])) echo $GLOBALS['_PJ_strings']['no_invoices_found']; else echo 'No invoices found.'; ?></P>
                            <A HREF="create.php" CLASS="FormButton"><?php if(!empty($GLOBALS['_PJ_strings']['create_first_invoice'])) echo $GLOBALS['_PJ_strings']['create_first_invoice']; else echo 'Create your first invoice'; ?></A>
                        </TD>
                    </TR>
                <?php else: ?>
                    <TR CLASS="ListHeader">
                        <TD CLASS="ListHeader"><?php if(!empty($GLOBALS['_PJ_strings']['invoice_number'])) echo $GLOBALS['_PJ_strings']['invoice_number']; else echo 'Invoice #'; ?></TD>
                        <TD CLASS="ListHeader"><?php if(!empty($GLOBALS['_PJ_strings']['customer'])) echo $GLOBALS['_PJ_strings']['customer']; else echo 'Customer'; ?></TD>
                        <TD CLASS="ListHeader"><?php if(!empty($GLOBALS['_PJ_strings']['project'])) echo $GLOBALS['_PJ_strings']['project']; else echo 'Project'; ?></TD>
                        <TD CLASS="ListHeader"><?php if(!empty($GLOBALS['_PJ_strings']['date'])) echo $GLOBALS['_PJ_strings']['date']; else echo 'Date'; ?></TD>
                        <TD CLASS="ListHeader"><?php if(!empty($GLOBALS['_PJ_strings']['period'])) echo $GLOBALS['_PJ_strings']['period']; else echo 'Period'; ?></TD>
                        <TD CLASS="ListHeader"><?php if(!empty($GLOBALS['_PJ_strings']['amount'])) echo $GLOBALS['_PJ_strings']['amount']; else echo 'Amount'; ?></TD>
                        <TD CLASS="ListHeader"><?php if(!empty($GLOBALS['_PJ_strings']['status'])) echo $GLOBALS['_PJ_strings']['status']; else echo 'Status'; ?></TD>
                        <TD CLASS="ListHeader"><?php if(!empty($GLOBALS['_PJ_strings']['actions'])) echo $GLOBALS['_PJ_strings']['actions']; else echo 'Actions'; ?></TD>
                    </TR>
                    <?php foreach ($invoices as $inv): ?>
                        <TR CLASS="ListContent status-<?php echo $inv['status']; ?>">
                            <TD CLASS="ListContent">
                                <A HREF="view.php?id=<?php echo $inv['id']; ?>">
                                    <?php echo htmlspecialchars($inv['invoice_number']); ?>
                                </A>
                            </TD>
                            <TD CLASS="ListContent"><?php echo htmlspecialchars($inv['customer_name']); ?></TD>
                            <TD CLASS="ListContent"><?php echo htmlspecialchars($inv['project_name'] ?? '-'); ?></TD>
                            <TD CLASS="ListContent"><?php echo date('d.m.Y', strtotime($inv['invoice_date'])); ?></TD>
                            <TD CLASS="ListContent">
                                <?php echo date('d.m.Y', strtotime($inv['period_start'])); ?> - 
                                <?php echo date('d.m.Y', strtotime($inv['period_end'])); ?>
                            </TD>
                            <TD CLASS="ListContent">
                                <?php echo number_format($inv['gross_amount'], 2); ?>€
                            </TD>
                            <TD CLASS="ListContent">
                                <SPAN CLASS="status-badge status-<?php echo $inv['status']; ?>">
                                    <?php echo ucfirst($inv['status']); ?>
                                </SPAN>
                            </TD>
                            <TD CLASS="ListContent">
                                <A HREF="view.php?id=<?php echo $inv['id']; ?>" CLASS="ActionLink" TITLE="<?php if(!empty($GLOBALS['_PJ_strings']['view'])) echo $GLOBALS['_PJ_strings']['view']; else echo 'View'; ?>"><?php if(!empty($GLOBALS['_PJ_strings']['view'])) echo $GLOBALS['_PJ_strings']['view']; else echo 'View'; ?></A>
                                <?php if ($inv['status'] === 'draft'): ?>
                                    <A HREF="edit.php?id=<?php echo $inv['id']; ?>" CLASS="ActionLink" TITLE="<?php if(!empty($GLOBALS['_PJ_strings']['edit'])) echo $GLOBALS['_PJ_strings']['edit']; else echo 'Edit'; ?>"><?php if(!empty($GLOBALS['_PJ_strings']['edit'])) echo $GLOBALS['_PJ_strings']['edit']; else echo 'Edit'; ?></A>
                                <?php endif; ?>
                                <A HREF="pdf.php?id=<?php echo $inv['id']; ?>" CLASS="ActionLink" TITLE="<?php if(!empty($GLOBALS['_PJ_strings']['download_pdf'])) echo $GLOBALS['_PJ_strings']['download_pdf']; else echo 'Download PDF'; ?>"><?php if(!empty($GLOBALS['_PJ_strings']['pdf'])) echo $GLOBALS['_PJ_strings']['pdf']; else echo 'PDF'; ?></A>
                            </TD>
                        </TR>
                    <?php endforeach; ?>
                <?php endif; ?>
            </TABLE>

            <?php if (!empty($invoices)): ?>
                <BR>
                <!-- Summary -->
                <TABLE CELLPADDING="0" CELLSPACING="0" BORDER="<?php print($_PJ_inner_frame_border); ?>" WIDTH="100%" CLASS="form">
                    <TR>
                        <TD CLASS="FormTitle" COLSPAN="4"><?php if(!empty($GLOBALS['_PJ_strings']['summary'])) echo $GLOBALS['_PJ_strings']['summary']; else echo 'Summary'; ?></TD>
                    </TR>
                    <?php
                    $total_amount = array_sum(array_column($invoices, 'gross_amount'));
                    $status_counts = array_count_values(array_column($invoices, 'status'));
                    ?>
                    <TR>
                        <TD CLASS="FormFieldName"><?php if(!empty($GLOBALS['_PJ_strings']['total_invoices'])) echo $GLOBALS['_PJ_strings']['total_invoices']; else echo 'Total Invoices'; ?>:</TD>
                        <TD CLASS="FormField"><?php echo count($invoices); ?></TD>
                        <TD CLASS="FormFieldName"><?php if(!empty($GLOBALS['_PJ_strings']['total_amount'])) echo $GLOBALS['_PJ_strings']['total_amount']; else echo 'Total Amount'; ?>:</TD>
                        <TD CLASS="FormField"><?php echo number_format($total_amount, 2); ?>€</TD>
                    </TR>
                    <TR>
                        <TD CLASS="FormFieldName"><?php if(!empty($GLOBALS['_PJ_strings']['status'])) echo $GLOBALS['_PJ_strings']['status']; else echo 'Status'; ?>:</TD>
                        <TD CLASS="FormField" COLSPAN="3">
                            <?php if(!empty($GLOBALS['_PJ_strings']['draft'])) echo $GLOBALS['_PJ_strings']['draft']; else echo 'Draft'; ?>: <?php echo $status_counts['draft'] ?? 0; ?> |
                            <?php if(!empty($GLOBALS['_PJ_strings']['sent'])) echo $GLOBALS['_PJ_strings']['sent']; else echo 'Sent'; ?>: <?php echo $status_counts['sent'] ?? 0; ?> |
                            <?php if(!empty($GLOBALS['_PJ_strings']['paid'])) echo $GLOBALS['_PJ_strings']['paid']; else echo 'Paid'; ?>: <?php echo $status_counts['paid'] ?? 0; ?>
                        </TD>
                    </TR>
                </TABLE>
            <?php endif; ?>

        </TD>
    </TR>
</TABLE>

<STYLE>
.status-badge {
    padding: 2px 6px;
    border-radius: 3px;
    font-size: 11px;
    font-weight: bold;
    text-transform: uppercase;
}

.status-draft {
    background-color: #ffc107;
    color: black;
}

.status-sent {
    background-color: #17a2b8;
    color: white;
}

.status-paid {
    background-color: #28a745;
    color: white;
}

.status-cancelled {
    background-color: #dc3545;
    color: white;
}

.ActionLink {
    color: #007bff;
    text-decoration: underline;
    cursor: pointer;
    margin-right: 10px;
}

.ActionLink:hover {
    color: #0056b3;
}
</STYLE>

<!-- invoice/list.ihtml - END -->
