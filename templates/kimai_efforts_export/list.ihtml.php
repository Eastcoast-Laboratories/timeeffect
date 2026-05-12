<?php include_once(__DIR__ . '/../shared/header.ihtml.php'); ?>

<div class="container" style="max-width: 800px; margin: 0 auto; padding: 20px;">
	<h1 style="margin-bottom: 20px;"><?= htmlspecialchars($center_title) ?></h1>
	
	<p style="margin-bottom: 20px;">
		Select the customers whose efforts you want to export to Kimai. The export will generate a CSV file compatible with Kimai's Importer Bundle.
	</p>
	
	<form method="post" action="kimai_efforts_export.php">
		<input type="hidden" name="export_efforts" value="1">
		
		<div style="margin-bottom: 20px;">
			<button type="submit" style="background-color: #007bff; color: white; padding: 10px 20px; border: none; border-radius: 5px; cursor: pointer;">
				Export Selected Efforts
			</button>
			<a href="/admin/pdflayout.php" style="margin-left: 10px; color: #007bff; text-decoration: none;">Cancel</a>
		</div>
		
		<div style="border: 1px solid #ddd; border-radius: 5px; padding: 15px;">
			<div style="margin-bottom: 10px;">
				<input type="checkbox" id="select_all" onclick="toggleAll(this)">
				<label for="select_all" style="font-weight: bold; margin-left: 5px;">Select All</label>
			</div>
			
			<?php if(empty($customers)): ?>
				<p>No customers found or no access to customers.</p>
			<?php else: ?>
				<?php foreach($customers as $customer): ?>
					<div style="padding: 8px 0; border-bottom: 1px solid #eee;">
						<input type="checkbox" name="customer_ids[]" value="<?= htmlspecialchars($customer['id']) ?>" id="customer_<?= htmlspecialchars($customer['id']) ?>" class="customer-checkbox">
						<label for="customer_<?= htmlspecialchars($customer['id']) ?>" style="margin-left: 5px;">
							<?= htmlspecialchars($customer['name']) ?>
							<?php if($customer['active'] === 'no'): ?>
								<span style="color: #999; font-size: 0.9em;">(inactive)</span>
							<?php endif; ?>
						</label>
					</div>
				<?php endforeach; ?>
			<?php endif; ?>
		</div>
		
		<div style="margin-top: 20px;">
			<button type="submit" style="background-color: #007bff; color: white; padding: 10px 20px; border: none; border-radius: 5px; cursor: pointer;">
				Export Selected Efforts
			</button>
			<a href="/admin/pdflayout.php" style="margin-left: 10px; color: #007bff; text-decoration: none;">Cancel</a>
		</div>
	</form>
	
	<div style="margin-top: 30px; padding: 15px; background-color: #f5f5f5; border-radius: 5px;">
		<h3 style="margin-top: 0;">Import Instructions</h3>
		<ol>
			<li>Download the CSV file</li>
			<li>Log in to Kimai: <a href="http://localhost:8351" target="_blank">http://localhost:8351</a></li>
			<li>Go to Administration → Import</li>
			<li>Select "Timesheet" as import type</li>
			<li>Upload the CSV file</li>
			<li>Click "Import"</li>
		</ol>
		<p><strong>Note:</strong> Make sure users exist in Kimai before importing timesheets.</p>
	</div>
</div>

<script>
function toggleAll(checkbox) {
	var checkboxes = document.querySelectorAll('.customer-checkbox');
	checkboxes.forEach(function(cb) {
		cb.checked = checkbox.checked;
	});
}
</script>
