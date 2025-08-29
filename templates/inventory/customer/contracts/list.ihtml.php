<!-- inventory/customer/contracts/list.ihtml - START -->
<?php
// Function to translate contract type from database value to localized string
function translateContractType($type) {
    switch($type) {
        case 'hourly':
            return !empty($GLOBALS['_PJ_strings']['hourly']) ? $GLOBALS['_PJ_strings']['hourly'] : 'Hourly';
        case 'fixed_monthly':
            return !empty($GLOBALS['_PJ_strings']['fixed_monthly']) ? $GLOBALS['_PJ_strings']['fixed_monthly'] : 'Fixed Monthly';
        default:
            return ucfirst(str_replace('_', ' ', $type));
    }
}
?>
<TABLE CELLPADDING="0" CELLSPACING="0" BORDER="0" WIDTH="100%">
    <TR VALIGN="center">
        <td class="spacer_before_path"></td>
        <TD class="path"><?php include($GLOBALS['_PJ_root'] . '/templates/shared/path.ihtml.php'); ?></TD>
    </TR>
</TABLE>

<TABLE CELLPADDING="0" CELLSPACING="0" BORDER="0" WIDTH="100%" BACKGROUND="<?php echo $GLOBALS['_PJ_image_path'] ?>/option-bg.gif">
    <TR>
        <TD VALIGN="top">
            <TABLE CELLPADDING="0" CELLSPACING="0" BORDER="0">
                <TR HEIGHT="24">
                    <TD WIDTH="40"><IMG SRC="<?php echo $GLOBALS['_PJ_image_path'] ?>/abstand.gif" WIDTH="40" HEIGHT="1" BORDER="0"></TD>
                    <TD BACKGROUND="<?php echo $GLOBALS['_PJ_image_path'] ?>/option-sb.gif"><IMG SRC="<?php echo $GLOBALS['_PJ_image_path'] ?>/option-bs.gif" BORDER="0"></TD>
                    <TD CLASS="option" BACKGROUND="<?php echo $GLOBALS['_PJ_image_path'] ?>/option-sb.gif">&nbsp;&nbsp;<A CLASS="option" HREF="<?= $GLOBALS['_PJ_customer_inventory_script'] ?>"><?php if(!empty($GLOBALS['_PJ_strings']['back'])) echo $GLOBALS['_PJ_strings']['back'] ?> <?php if(!empty($GLOBALS['_PJ_strings']['to'])) echo $GLOBALS['_PJ_strings']['to'] ?> <?php if(!empty($GLOBALS['_PJ_strings']['customers'])) echo $GLOBALS['_PJ_strings']['customers'] ?></A>&nbsp;&nbsp;</TD>
                    <TD CLASS="option" BACKGROUND="<?php echo $GLOBALS['_PJ_image_path'] ?>/option-sb.gif">&nbsp;&nbsp;<A CLASS="option" HREF="contracts.php?customer_id=<?= $customer_id ?>&action=create"><?php if(!empty($GLOBALS['_PJ_strings']['new_contract'])) echo $GLOBALS['_PJ_strings']['new_contract'] ?></A>&nbsp;&nbsp;</TD>
                    <TD>&nbsp;</TD>
                </TR>
            </TABLE>
        </TD>
    </TR>
</TABLE>

<TABLE CELLPADDING="0" CELLSPACING="0" BORDER="0" WIDTH="100%">
    <TR>
        <TD WIDTH="40"><IMG SRC="<?php echo $GLOBALS['_PJ_image_path'] ?>/abstand.gif" WIDTH="40" HEIGHT="1" BORDER="0"></TD>
        <TD VALIGN="top">

            <?php if (!empty($errors)): ?>
                <TABLE CELLPADDING="5" CELLSPACING="0" BORDER="0" WIDTH="100%" CLASS="error">
                    <TR>
                        <TD CLASS="error">
                            <UL>
                                <?php foreach ($errors as $error): ?>
                                    <LI><?php echo htmlspecialchars($error); ?></LI>
                                <?php endforeach; ?>
                            </UL>
                        </TD>
                    </TR>
                </TABLE>
                <BR>
            <?php endif; ?>

            <?php if (isset($_GET['success'])): ?>
                <TABLE CELLPADDING="5" CELLSPACING="0" BORDER="0" WIDTH="100%" CLASS="success">
                    <TR>
                        <TD CLASS="success">
                            <?php if(!empty($GLOBALS['_PJ_strings']['contract'])) echo $GLOBALS['_PJ_strings']['contract'] ?> <?php echo htmlspecialchars($_GET['success']); ?> <?php if(!empty($GLOBALS['_PJ_strings']['successfully'])) echo $GLOBALS['_PJ_strings']['successfully'] ?>!
                        </TD>
                    </TR>
                </TABLE>
                <BR>
            <?php endif; ?>

            <!-- Contract Form -->
            <?php if ($action === 'create' || $action === 'edit'): ?>
                <TABLE CELLPADDING="0" CELLSPACING="0" BORDER="<?php print($_PJ_inner_frame_border); ?>" WIDTH="100%" CLASS="form">
                    <TR>
                        <TD CLASS="FormTitle" COLSPAN="2">
                            <?php echo $action === 'create' ? ((!empty($GLOBALS['_PJ_strings']['create']) ? $GLOBALS['_PJ_strings']['create'] : '') . ' ' . (!empty($GLOBALS['_PJ_strings']['new_contract']) ? $GLOBALS['_PJ_strings']['new_contract'] : '')) : ((!empty($GLOBALS['_PJ_strings']['edit']) ? $GLOBALS['_PJ_strings']['edit'] : '') . ' ' . (!empty($GLOBALS['_PJ_strings']['contract']) ? $GLOBALS['_PJ_strings']['contract'] : '')); ?> - <?= htmlspecialchars($customer_data['name']) ?>
                        </TD>
                    </TR>
                    
                    <FORM METHOD="POST">
                        <INPUT TYPE="hidden" NAME="action" VALUE="<?php echo $action; ?>">
                        <INPUT TYPE="hidden" NAME="customer_id" VALUE="<?php echo $customer_id; ?>">
                        <?php if ($action === 'edit'): ?>
                            <INPUT TYPE="hidden" NAME="id" VALUE="<?php echo $contract_id; ?>">
                        <?php endif; ?>
                        
                        <TR>
                            <TD CLASS="FormFieldName"><?php if(!empty($GLOBALS['_PJ_strings']['project'])) echo $GLOBALS['_PJ_strings']['project'] ?>:</TD>
                            <TD CLASS="FormField">
                                <SELECT NAME="project_id" CLASS="FormSelect">
                                    <OPTION VALUE=""><?php if(!empty($GLOBALS['_PJ_strings']['all'])) echo $GLOBALS['_PJ_strings']['all'] ?> <?php if(!empty($GLOBALS['_PJ_strings']['projects'])) echo $GLOBALS['_PJ_strings']['projects'] ?></OPTION>
                                    <?php foreach ($projects as $project): ?>
                                        <OPTION VALUE="<?php echo $project['id']; ?>" 
                                                <?php echo (isset($contract_data) && $contract_data['project_id'] == $project['id']) ? 'selected' : ''; ?>>
                                            <?php echo htmlspecialchars($project['name']); ?>
                                        </OPTION>
                                    <?php endforeach; ?>
                                </SELECT>
                            </TD>
                        </TR>

                        <TR>
                            <TD CLASS="FormFieldName"><?php if(!empty($GLOBALS['_PJ_strings']['contract'])) echo $GLOBALS['_PJ_strings']['contract'] ?> <?php if(!empty($GLOBALS['_PJ_strings']['type'])) echo $GLOBALS['_PJ_strings']['type'] ?> *:</TD>
                            <TD CLASS="FormField">
                                <SELECT NAME="contract_type" CLASS="FormSelect" ONCHANGE="toggleContractFields()">
                                    <OPTION VALUE="hourly" <?php echo (isset($contract_data) && $contract_data['contract_type'] === 'hourly') ? 'selected' : ''; ?>><?php if(!empty($GLOBALS['_PJ_strings']['hourly'])) echo $GLOBALS['_PJ_strings']['hourly'] ?></OPTION>
                                    <OPTION VALUE="fixed_monthly" <?php echo (isset($contract_data) && $contract_data['contract_type'] === 'fixed_monthly') ? 'selected' : ''; ?>><?php if(!empty($GLOBALS['_PJ_strings']['fixed_monthly'])) echo $GLOBALS['_PJ_strings']['fixed_monthly'] ?></OPTION>
                                </SELECT>
                            </TD>
                        </TR>

                        <TR ID="hourly_fields">
                            <TD CLASS="FormFieldName"><?php if(!empty($GLOBALS['_PJ_strings']['hourly_rate'])) echo $GLOBALS['_PJ_strings']['hourly_rate'] ?> (€) *:</TD>
                            <TD CLASS="FormField">
                                <INPUT TYPE="number" STEP="0.01" NAME="hourly_rate" CLASS="FormInput"
                                       VALUE="<?php echo isset($contract_data) ? number_format($contract_data['hourly_rate'], 2, '.', '') : ''; ?>">
                            </TD>
                        </TR>

                        <TR ID="fixed_amount_field" STYLE="display: none;">
                            <TD CLASS="FormFieldName"><?php if(!empty($GLOBALS['_PJ_strings']['fixed_amount'])) echo $GLOBALS['_PJ_strings']['fixed_amount'] ?> (€) *:</TD>
                            <TD CLASS="FormField">
                                <INPUT TYPE="number" STEP="0.01" NAME="fixed_amount" CLASS="FormInput"
                                       VALUE="<?php echo isset($contract_data) ? number_format($contract_data['fixed_amount'], 2, '.', '') : ''; ?>">
                            </TD>
                        </TR>

                        <TR ID="fixed_hours_field" STYLE="display: none;">
                            <TD CLASS="FormFieldName"><?php if(!empty($GLOBALS['_PJ_strings']['fixed_hours'])) echo $GLOBALS['_PJ_strings']['fixed_hours'] ?> *:</TD>
                            <TD CLASS="FormField">
                                <INPUT TYPE="number" STEP="0.01" NAME="fixed_hours" CLASS="FormInput"
                                       VALUE="<?php echo isset($contract_data) ? number_format($contract_data['fixed_hours'], 2, '.', '') : ''; ?>">
                            </TD>
                        </TR>

                        <TR>
                            <TD CLASS="FormFieldName"><?php if(!empty($GLOBALS['_PJ_strings']['start_date'])) echo $GLOBALS['_PJ_strings']['start_date'] ?> *:</TD>
                            <TD CLASS="FormField">
                                <INPUT TYPE="date" NAME="start_date" CLASS="FormInput" REQUIRED
                                       VALUE="<?php echo isset($contract_data) ? $contract_data['start_date'] : ''; ?>">
                            </TD>
                        </TR>

                        <TR>
                            <TD CLASS="FormFieldName"><?php if(!empty($GLOBALS['_PJ_strings']['end_date'])) echo $GLOBALS['_PJ_strings']['end_date'] ?>:</TD>
                            <TD CLASS="FormField">
                                <INPUT TYPE="date" NAME="end_date" CLASS="FormInput"
                                       VALUE="<?php echo isset($contract_data) ? $contract_data['end_date'] : ''; ?>">
                            </TD>
                        </TR>

                        <TR>
                            <TD CLASS="FormFieldName"><?php if(!empty($GLOBALS['_PJ_strings']['description'])) echo $GLOBALS['_PJ_strings']['description'] ?>:</TD>
                            <TD CLASS="FormField">
                                <TEXTAREA NAME="description" CLASS="FormTextarea" ROWS="3"><?php echo isset($contract_data) ? htmlspecialchars($contract_data['description']) : ''; ?></TEXTAREA>
                            </TD>
                        </TR>

                        <TR>
                            <TD CLASS="FormFieldName"><?php if(!empty($GLOBALS['_PJ_strings']['active'])) echo $GLOBALS['_PJ_strings']['active'] ?>:</TD>
                            <TD CLASS="FormField">
                                <INPUT TYPE="checkbox" NAME="active" VALUE="1" 
                                       <?php echo (isset($contract_data) && $contract_data['active']) || !isset($contract_data) ? 'checked' : ''; ?>>
                            </TD>
                        </TR>

                        <TR>
                            <TD CLASS="FormFieldName">&nbsp;</TD>
                            <TD CLASS="FormField">
                                <INPUT TYPE="submit" VALUE="<?php echo $action === 'create' ? (!empty($GLOBALS['_PJ_strings']['create']) ? $GLOBALS['_PJ_strings']['create'] : 'Create') . ' ' . (!empty($GLOBALS['_PJ_strings']['contract']) ? $GLOBALS['_PJ_strings']['contract'] : 'Contract') : (!empty($GLOBALS['_PJ_strings']['update']) ? $GLOBALS['_PJ_strings']['update'] : 'Update') . ' ' . (!empty($GLOBALS['_PJ_strings']['contract']) ? $GLOBALS['_PJ_strings']['contract'] : 'Contract'); ?>" CLASS="FormButton">
                                <INPUT TYPE="button" VALUE="<?php if(!empty($GLOBALS['_PJ_strings']['cancel'])) echo $GLOBALS['_PJ_strings']['cancel'] ?>" CLASS="FormButton" ONCLICK="window.location.href='contracts.php?customer_id=<?php echo $customer_id; ?>'">
                            </TD>
                        </TR>
                    </FORM>
                </TABLE>
            <?php else: ?>
                <!-- Contract List -->
                <TABLE CELLPADDING="0" CELLSPACING="0" BORDER="<?php print($_PJ_inner_frame_border); ?>" WIDTH="100%" CLASS="list">
                    <TR>
                        <TD CLASS="ListTitle" COLSPAN="6">
                            <?php if(!empty($GLOBALS['_PJ_strings']['contracts'])) echo $GLOBALS['_PJ_strings']['contracts'] ?> <?php if(!empty($GLOBALS['_PJ_strings']['for'])) echo $GLOBALS['_PJ_strings']['for'] ?> <?= htmlspecialchars($customer_data['name']) ?>
                        </TD>
                    </TR>

                    <?php if (empty($contracts)): ?>
                        <TR>
                            <TD CLASS="ListContent" COLSPAN="6" ALIGN="center" STYLE="padding: 40px;">
                                <P><?php if(!empty($GLOBALS['_PJ_strings']['no_contracts_found'])) echo $GLOBALS['_PJ_strings']['no_contracts_found'] ?>.</P>
                                <A HREF="contracts.php?customer_id=<?php echo $customer_id; ?>&action=create" CLASS="FormButton"><?php if(!empty($GLOBALS['_PJ_strings']['create_first_contract'])) echo $GLOBALS['_PJ_strings']['create_first_contract'] ?></A>
                            </TD>
                        </TR>
                    <?php else: ?>
                        <TR CLASS="ListHeader">
                            <TD CLASS="ListHeader"><?php if(!empty($GLOBALS['_PJ_strings']['project'])) echo $GLOBALS['_PJ_strings']['project'] ?></TD>
                            <TD CLASS="ListHeader"><?php if(!empty($GLOBALS['_PJ_strings']['type'])) echo $GLOBALS['_PJ_strings']['type'] ?></TD>
                            <TD CLASS="ListHeader"><?php if(!empty($GLOBALS['_PJ_strings']['rate_amount'])) echo $GLOBALS['_PJ_strings']['rate_amount'] ?></TD>
                            <TD CLASS="ListHeader"><?php if(!empty($GLOBALS['_PJ_strings']['period'])) echo $GLOBALS['_PJ_strings']['period'] ?></TD>
                            <TD CLASS="ListHeader"><?php if(!empty($GLOBALS['_PJ_strings']['status'])) echo $GLOBALS['_PJ_strings']['status'] ?></TD>
                            <TD CLASS="ListHeader"><?php if(!empty($GLOBALS['_PJ_strings']['actions'])) echo $GLOBALS['_PJ_strings']['actions'] ?></TD>
                        </TR>
                        <?php foreach ($contracts as $contract_item): ?>
                            <TR CLASS="ListContent <?php echo $contract_item['active'] ? 'active' : 'inactive'; ?>">
                                <TD CLASS="ListContent"><?php echo htmlspecialchars($contract_item['project_name'] ?? ((!empty($GLOBALS['_PJ_strings']['all']) ? $GLOBALS['_PJ_strings']['all'] : '') . ' ' . (!empty($GLOBALS['_PJ_strings']['projects']) ? $GLOBALS['_PJ_strings']['projects'] : ''))); ?></TD>
                                <TD CLASS="ListContent"><?php echo translateContractType($contract_item['contract_type']); ?></TD>
                                <TD CLASS="ListContent">
                                    <?php if ($contract_item['contract_type'] === 'fixed_monthly'): ?>
                                        <?php echo number_format($contract_item['fixed_amount'], 2); ?>€ 
                                        (<?php echo number_format($contract_item['fixed_hours'], 2); ?>h)
                                    <?php else: ?>
                                        <?php echo number_format($contract_item['hourly_rate'], 2); ?>€/h
                                    <?php endif; ?>
                                </TD>
                                <TD CLASS="ListContent">
                                    <?php echo date('d.m.Y', strtotime($contract_item['start_date'])); ?>
                                    <?php if ($contract_item['end_date']): ?>
                                        - <?php echo date('d.m.Y', strtotime($contract_item['end_date'])); ?>
                                    <?php else: ?>
                                        - <?php if(!empty($GLOBALS['_PJ_strings']['ongoing'])) echo $GLOBALS['_PJ_strings']['ongoing'] ?>
                                    <?php endif; ?>
                                </TD>
                                <TD CLASS="ListContent">
                                    <SPAN CLASS="status-badge <?php echo $contract_item['active'] ? 'status-active' : 'status-inactive'; ?>">
                                        <?php echo $contract_item['active'] ? (!empty($GLOBALS['_PJ_strings']['active']) ? $GLOBALS['_PJ_strings']['active'] : '') : (!empty($GLOBALS['_PJ_strings']['inactive']) ? $GLOBALS['_PJ_strings']['inactive'] : ''); ?>
                                    </SPAN>
                                </TD>
                                <TD CLASS="ListContent">
                                    <A HREF="contracts.php?customer_id=<?php echo $customer_id; ?>&action=edit&id=<?php echo $contract_item['id']; ?>" 
                                       CLASS="ActionLink"><?php if(!empty($GLOBALS['_PJ_strings']['edit'])) echo $GLOBALS['_PJ_strings']['edit'] ?></A>
                                    <?php if ($contract_item['active']): ?>
                                        <FORM METHOD="POST" STYLE="display: inline; margin-left: 10px;">
                                            <INPUT TYPE="hidden" NAME="action" VALUE="deactivate">
                                            <INPUT TYPE="hidden" NAME="customer_id" VALUE="<?php echo $customer_id; ?>">
                                            <INPUT TYPE="hidden" NAME="id" VALUE="<?php echo $contract_item['id']; ?>">
                                            <INPUT TYPE="submit" VALUE="<?php if(!empty($GLOBALS['_PJ_strings']['deactivate'])) echo $GLOBALS['_PJ_strings']['deactivate'] ?>" CLASS="ActionLink" 
                                                    ONCLICK="return confirm('<?php if(!empty($GLOBALS['_PJ_strings']['confirm_deactivate_contract'])) echo $GLOBALS['_PJ_strings']['confirm_deactivate_contract'] ?>')" STYLE="background: none; border: none; color: #dc3545; cursor: pointer; text-decoration: underline;">
                                        </FORM>
                                    <?php endif; ?>
                                </TD>
                            </TR>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </TABLE>
            <?php endif; ?>

        </TD>
    </TR>
</TABLE>

<SCRIPT>
function toggleContractFields() {
    const contractType = document.getElementsByName('contract_type')[0].value;
    const hourlyFields = document.getElementById('hourly_fields');
    const fixedAmountField = document.getElementById('fixed_amount_field');
    const fixedHoursField = document.getElementById('fixed_hours_field');
    
    if (contractType === 'fixed_monthly') {
        hourlyFields.style.display = 'none';
        fixedAmountField.style.display = 'table-row';
        fixedHoursField.style.display = 'table-row';
    } else {
        hourlyFields.style.display = 'table-row';
        fixedAmountField.style.display = 'none';
        fixedHoursField.style.display = 'none';
    }
}

// Initialize on page load
document.addEventListener('DOMContentLoaded', function() {
    toggleContractFields();
});
</SCRIPT>

<STYLE>
.status-badge {
    padding: 2px 6px;
    border-radius: 3px;
    font-size: 11px;
    font-weight: bold;
    text-transform: uppercase;
}

.status-active {
    background-color: #28a745;
    color: white;
}

.status-inactive {
    background-color: #6c757d;
    color: white;
}

.ActionLink {
    color: #007bff;
    text-decoration: underline;
    cursor: pointer;
}

.ActionLink:hover {
    color: #0056b3;
}

TR.inactive {
    opacity: 0.6;
}
</STYLE>

<!-- inventory/customer/contracts/list.ihtml - END -->
