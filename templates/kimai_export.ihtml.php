<?php include_once(__DIR__ . '/shared/header.ihtml.php'); ?>

<div class="container" style="max-width: 800px; margin: 0 auto; padding: 20px;">
	<h1 style="margin-bottom: 20px;"><?= htmlspecialchars($center_title) ?></h1>
	
	<p style="margin-bottom: 20px;">
		Select the customers you want to export to Kimai. The export will generate a CSV file compatible with Kimai's import command:
	</p>
	
	<pre style="background-color: #f5f5f5; padding: 15px; border-radius: 5px; margin-bottom: 20px; overflow-x: auto;">
bin/console kimai:import:customer kimai_export_customers.csv --reader=csv
bin/console kimai:import:project kimai_export_projects.csv --reader=csv
	</pre>
	
	<form method="post" action="kimai_export.php">
		<input type="hidden" name="export_customers" value="1">
		
		<div style="margin-bottom: 20px;">
			<button type="submit" style="background-color: #007bff; color: white; padding: 10px 20px; border: none; border-radius: 5px; cursor: pointer;">
				Export Selected Customers
			</button>
			<a href="efforts.php" style="margin-left: 10px; color: #007bff; text-decoration: none;">Cancel</a>
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
				Export Selected Customers
			</button>
			<a href="efforts.php" style="margin-left: 10px; color: #007bff; text-decoration: none;">Cancel</a>
		</div>
	</form>
</div>

<script>
function toggleAll(checkbox) {
	var checkboxes = document.querySelectorAll('.customer-checkbox');
	checkboxes.forEach(function(cb) {
		cb.checked = checkbox.checked;
	});
}
</script>
