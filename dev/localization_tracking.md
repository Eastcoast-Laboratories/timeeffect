# Localization Progress Log - Files since commit 0b3fe983

benutze möglichst besteehende lokalize strings (z.b. "Customer" ist ja schon )

achte darauf, dasss die language dateien geladen werden: schau dir funktionierende dateien an, wie z.b. @form.ihtml.php#L120 
mache alles genau so wie dort in den neuen dateien seit dem commit

keine fallbacks!!!


## Files to Localize (Status: ✅ = Completed, ⏳ = In Progress, ❌ = Pending)

### PHP Files with User-Facing Text:
- ✅ inventory/contracts.php
- ❌ inventory/projects.php  
- ❌ invoice/create.php
- ❌ invoice/edit.php
- ❌ invoice/index.php
- ❌ invoice/reminders.php
- ❌ invoice/view.php
- ❌ user/settings.php

### Template Files:
- ✅ templates/inventory/customer/contracts.ihtml.php
- ❌ templates/invoice/edit_form.ihtml.php
- ❌ templates/invoice/form.ihtml.php
- ❌ templates/invoice/list.ihtml.php
- ❌ templates/invoice/reminders.ihtml.php
- ❌ templates/invoice/view.ihtml.php
- ❌ templates/report/list.ihtml.php
- ❌ templates/user/form.ihtml.php

### AJAX Files:
- ❌ invoice/ajax/preview.php
- ❌ invoice/ajax/reminder_preview.php
- ❌ invoice/ajax/schedule_reminders.php

### Test Files (Lower Priority):
not needed

### Files Already Localized or No Text:
- ✅ dev/mysql.php (no user-facing text)
- ✅ invoice/debug_pdf.php (debug only)
- ✅ invoice/pdf.php (PDF generation)
- ✅ include/* files (backend logic)
- ✅ sql/* files (database schemas)

## Progress: 0/24 files completed
