# TimeEffect Layout Template Guide

## Standard Layout für neue PHP Seiten

Alle neuen PHP Seiten sollen das einheitliche TimeEffect Layout verwenden mit Navigation, Header und Footer.

### Template Structure

```
/templates/
├── list.ihtml.php           # Main layout template
├── shared/
│   ├── header.ihtml.php     # HTML head section
│   ├── left.ihtml.php       # Left navigation
│   ├── top.ihtml.php        # Top header bar
│   └── layout_template.php  # Helper include
└── [module]/
    └── [page]/
        └── list.ihtml.php   # Page content
```

### Implementation Pattern

#### 1. PHP Controller (z.B. `inventory/contracts.php`)

```php
<?php
require_once(__DIR__ . "/../bootstrap.php");
include_once(__DIR__ . "/../include/config.inc.php");
include_once($GLOBALS['_PJ_include_path'] . '/scripts.inc.php');

// Check authentication
if (!$_PJ_auth->giveValue('id')) {
    header('Location: ../index.php');
    exit;
}

// Your business logic here...
// Process forms, database queries, etc.

// Set template variables for layout
$center_template = "inventory/customer/contracts";  // Path to your template
$center_title = 'Contract Management - ' . $customer_data['name'];

// Include unified layout
include("$_PJ_root/templates/list.ihtml.php");
include_once("$_PJ_include_path/degestiv.inc.php");
?>
```

#### 2. Template File (z.B. `templates/inventory/customer/contracts/list.ihtml.php`)

```html
<!-- inventory/customer/contracts/list.ihtml - START -->
<TABLE CELLPADDING="0" CELLSPACING="0" BORDER="0" WIDTH="100%">
    <TR VALIGN="center">
        <td class="spacer_before_path"></td>
        <TD class="path"><?php include($GLOBALS['_PJ_root'] . '/templates/shared/path.ihtml.php'); ?></TD>
    </TR>
</TABLE>

<!-- Navigation Options -->
<TABLE CELLPADDING="0" CELLSPACING="0" BORDER="0" WIDTH="100%" BACKGROUND="<?php echo $GLOBALS['_PJ_image_path'] ?>/option-bg.gif">
    <TR>
        <TD VALIGN="top">
            <TABLE CELLPADDING="0" CELLSPACING="0" BORDER="0">
                <TR HEIGHT="24">
                    <TD WIDTH="40"><IMG SRC="<?php echo $GLOBALS['_PJ_image_path'] ?>/abstand.gif" WIDTH="40" HEIGHT="1" BORDER="0"></TD>
                    <TD BACKGROUND="<?php echo $GLOBALS['_PJ_image_path'] ?>/option-sb.gif"><IMG SRC="<?php echo $GLOBALS['_PJ_image_path'] ?>/option-bs.gif" BORDER="0"></TD>
                    <TD CLASS="option" BACKGROUND="<?php echo $GLOBALS['_PJ_image_path'] ?>/option-sb.gif">
                        &nbsp;&nbsp;<A CLASS="option" HREF="back_link.php">Back</A>&nbsp;&nbsp;
                    </TD>
                    <TD CLASS="option" BACKGROUND="<?php echo $GLOBALS['_PJ_image_path'] ?>/option-sb.gif">
                        &nbsp;&nbsp;<A CLASS="option" HREF="new_item.php">New Item</A>&nbsp;&nbsp;
                    </TD>
                    <TD>&nbsp;</TD>
                </TR>
            </TABLE>
        </TD>
    </TR>
</TABLE>

<!-- Main Content -->
<TABLE CELLPADDING="0" CELLSPACING="0" BORDER="0" WIDTH="100%">
    <TR>
        <TD WIDTH="40"><IMG SRC="<?php echo $GLOBALS['_PJ_image_path'] ?>/abstand.gif" WIDTH="40" HEIGHT="1" BORDER="0"></TD>
        <TD VALIGN="top">
            <!-- Your content here -->
        </TD>
    </TR>
</TABLE>
<!-- inventory/customer/contracts/list.ihtml - END -->
```

### Key Variables

- `$center_template`: Path to your template file (without `.ihtml.php`)
- `$center_title`: Page title for browser and header
- `$_PJ_root`: Root path to TimeEffect installation
- `$_PJ_include_path`: Path to include directory
- `$_PJ_image_path`: Path to images

### CSS Classes

- `.option`: Navigation option buttons
- `.FormTitle`: Form section headers
- `.FormFieldName`: Form field labels
- `.FormField`: Form field containers
- `.FormInput`: Input fields
- `.FormButton`: Buttons
- `.ListTitle`: List section headers
- `.ListHeader`: Table headers
- `.ListContent`: Table content cells

### Examples

1. **Contract Management**: `/inventory/contracts.php` → `/templates/inventory/customer/contracts/list.ihtml.php`
2. **Invoice Management**: `/invoice/index.php` → `/templates/invoice/list.ihtml.php`
3. **User Settings**: `/user/settings.php` → `/templates/user/settings/list.ihtml.php`

### Migration Checklist

Für bestehende Seiten:

- [ ] Remove standalone HTML structure (`<html>`, `<body>`, etc.)
- [ ] Set `$center_template` and `$center_title` variables
- [ ] Replace custom layout with `include("$_PJ_root/templates/list.ihtml.php")`
- [ ] Move content to appropriate template directory
- [ ] Use TimeEffect CSS classes
- [ ] Test navigation and layout consistency
