# TimeEffect to Kimai Integration

## Overview

This document describes the complete integration between TimeEffect and Kimai, including:
- CSV export of efforts with billing status
- ImportBundle plugin with enhanced features
- Automatic handling of billing dates and export status

## Architecture

### Components

1. **TimeEffect Export** (`/var/www/timeeffect/inventory/kimai_efforts_export.php`)
   - Web-based interface for exporting efforts
   - Supports customer selection
   - Exports billing status and dates

2. **Export Script** (`/var/www/timeeffect/dev/kimai_efforts_export.sh`)
   - Command-line tool for automated exports
   - Generates timestamped CSV files
   - Includes all effort data with billing information

3. **ImportBundle Plugin** (Branch: `rubo77/time_billed`)
   - Enhanced Kimai import plugin
   - Supports billing date tracking
   - Increased import limits (999999 rows, 16MB file size)

4. **Cleanup Script** (`/var/www/timeeffect/dev/kimai_delete_all_projects.sh`)
   - Safely deletes all projects and related data
   - Includes confirmation prompt

## Installation Status

✅ **Already Installed and Configured**

The ImportBundle is deployed with the `time_billed` branch which includes:
- MAX_ROWS: 999999 (previously 5000)
- MAX_FILESIZE: 16384k / 16MB (previously 4096k / 4MB)
- BilledDate field support as meta field

## Billing Status Mapping

### TimeEffect → Kimai
- **TimeEffect `billed` field**: Date when effort was invoiced (e.g., `2024-05-12`)
- **Kimai `exported` field**: Boolean (0 or 1) indicating if timesheet was included in an invoice
  - `1` = Effort was billed in TimeEffect (exported to invoice)
  - `0` = Effort was not billed in TimeEffect (not exported)
- **Kimai `BilledDate` field**: The actual billing date from TimeEffect (custom meta field)
  - Contains the date when the effort was billed in TimeEffect
  - Empty if the effort has not been billed

### Tracking Billing Status in Kimai
1. **View Exported Status**: In Kimai's Timesheet list, you can filter by "Exported" status
2. **Create Invoices**: Use Kimai's Invoice feature to create invoices from exported timesheets
3. **Track Invoice Status**: Each invoice has a status (`new`, `pending`, `paid`, `canceled`)

### Important Notes
- Kimai does NOT have a direct "billed date" field like TimeEffect
- Use Kimai's Invoice feature to manage billing and payment tracking
- The `exported` field indicates whether a timesheet was included in any invoice
- The `BilledDate` field is imported as a custom meta field (`meta.billedDate`)
  - Stored in Kimai's timesheet metadata for reference
  - Can be viewed and filtered in Kimai's UI if the meta field is configured
  - Supports multiple header variations: `BilledDate`, `Billed_Date`, `Billed Date`

## Usage

### Export Efforts from TimeEffect

#### Web Interface
1. Navigate to `/inventory/kimai_efforts_export.php`
2. Select customers to export
3. Download CSV file

#### Command Line
```bash
cd /var/www/timeeffect/dev
./kimai_efforts_export.sh "1,2,3"
```

### Import into Kimai

1. Go to Kimai Admin → Import
2. Upload the CSV file
3. Review and confirm import
4. Efforts are imported with:
   - Billing status (`Exported` field)
   - Billing date (`BilledDate` as meta field)

### Clean Up Kimai Data

```bash
cd /var/www/timeeffect/dev
./kimai_delete_all_projects.sh
```

## CSV Export Format

The export includes the following columns:

| Column | Description |
|--------|-------------|
| Date | Effort date (YYYY-MM-DD) |
| From | Start time (HH:MM) |
| To | End time (HH:MM) |
| Duration | Duration in seconds |
| Rate | Hourly rate |
| User | Username |
| Email | User email |
| Customer | Customer name |
| Project | Project name (or "Unassigned") |
| Activity | Activity name (default: "global") |
| Description | Effort description |
| Exported | Billing status (1 = billed, 0 = not billed) |
| BilledDate | Date when effort was billed in TimeEffect |
| Tags | Tags (comma-separated) |
| HourlyRate | Hourly rate |
| FixedRate | Fixed rate (0 by default) |
| InternalRate | Internal rate (0 by default) |
| meta.timesheet_foo | Custom meta field placeholder |

## Verification

### Check ImportBundle Installation
```bash
sudo docker exec kimai_app ls -la /var/www/kimai/var/plugins/ImportBundle/
```

### Verify Import Limits
```bash
sudo docker exec kimai_app grep "MAX_ROWS\|MAX_FILESIZE" /var/www/kimai/var/plugins/ImportBundle/Importer/ImporterService.php
```

Expected output:
```
public const MAX_ROWS = 999999;
public const MAX_FILESIZE = '16384k';
```

### Verify BilledDate Support
```bash
sudo docker exec kimai_app grep "billeddate" /var/www/kimai/var/plugins/ImportBundle/Importer/TimesheetImporter.php
```

Expected output:
```
'billeddate' => 'meta.billedDate',
'billed_date' => 'meta.billedDate',
```

## Important Notes

- ✅ ImportBundle is deployed with `time_billed` branch
- ✅ Import limits are configured (999999 rows, 16MB file size)
- ✅ BilledDate field support is active
- The plugin supports billing date tracking from TimeEffect
- All changes are persistent within the Docker container
- Container restart required after plugin updates
