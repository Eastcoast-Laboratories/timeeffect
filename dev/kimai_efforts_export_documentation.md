# Kimai Importer Plugin Modification

## Overview
This document describes the modification made to the Kimai Importer Bundle plugin to increase the maximum number of rows allowed per import.

## Problem
The Kimai Importer Bundle plugin has a default limit of 5000 rows per import. This limit prevents importing large datasets from TimeEffect when exporting all efforts for multiple customers.

## Solution
Modified the ImporterService.php file to increase the MAX_ROWS constant from 5000 to 999999999.

## File Modified
**File:** `/opt/kimai/var/plugins/ImportBundle/Importer/ImporterService.php`

## Change Made
Changed line 30 from:
```php
public const MAX_ROWS = 5000;
```

to:
```php
public const MAX_ROWS = 999999;
```

Changed line 31 from:
```php
public const MAX_FILESIZE = '4096k';
```

to:
```php
public const MAX_FILESIZE = '102400k';
```

## Steps to Apply

### 1. Access Kimai Docker Container
```bash
sudo docker exec kimai_app bash
```

### 2. Edit the File
```bash
sed -i 's/public const MAX_ROWS = 5000;/public const MAX_ROWS = 999999;/' /opt/kimai/var/plugins/ImportBundle/Importer/ImporterService.php
sed -i "s/public const MAX_FILESIZE = '4096k';/public const MAX_FILESIZE = '102400k';/" /opt/kimai/var/plugins/ImportBundle/Importer/ImporterService.php
```

### 3. Apply Changes
You have two options to apply the changes:

**Option A: Clear Kimai Cache (Recommended)**
```bash
sudo docker exec kimai_app php bin/console cache:clear --env=prod
```

**Option B: Restart Kimai Container**
```bash
sudo docker restart kimai_app
```

## Verification
To verify the change was applied successfully:
```bash
sudo docker exec kimai_app grep "MAX_ROWS" /opt/kimai/var/plugins/ImportBundle/Importer/ImporterService.php
```

Expected output:
```
public const MAX_ROWS = 999999999;
```

## Important Notes
- This change affects all imports in Kimai, not just TimeEffect exports
- The limit is now effectively unlimited for practical purposes
- After modifying the plugin, the Kimai cache must be cleared or the container restarted
- This modification will be lost if the plugin is updated or reinstalled
