<?php include_once(__DIR__ . '/shared/header.ihtml.php'); ?>

<div class="container" style="max-width: 800px; margin: 0 auto; padding: 20px;">
	<h1 style="margin-bottom: 20px;"><?= htmlspecialchars($center_title) ?></h1>
	
	<div class="alert alert-success" style="background-color: #d4edda; color: #155724; padding: 1rem; border-radius: 0.5rem; margin-bottom: 20px;">
		<h3 style="margin: 0 0 0.5rem 0;">✅ Export Successful</h3>
		<p style="margin: 0;">
			Successfully exported <?= htmlspecialchars($export_count) ?> customer(s) with their projects.
		</p>
	</div>
	
	<p style="margin-bottom: 20px;">
		Download the CSV files and import them into Kimai using the following commands:
	</p>
	
	<pre style="background-color: #f5f5f5; padding: 15px; border-radius: 5px; margin-bottom: 20px; overflow-x: auto;">
bin/console kimai:import:customer kimai_export_customers.csv --reader=csv
bin/console kimai:import:project kimai_export_projects.csv --reader=csv
	</pre>
	
	<div style="display: flex; gap: 20px; margin-bottom: 30px;">
		<a href="kimai_export.php?download=1&file=customers" class="btn" style="background-color: #007bff; color: white; padding: 15px 30px; border: none; border-radius: 5px; text-decoration: none; display: inline-block; text-align: center;">
			📥 Download Customers CSV
		</a>
		<a href="kimai_export.php?download=1&file=projects" class="btn" style="background-color: #28a745; color: white; padding: 15px 30px; border: none; border-radius: 5px; text-decoration: none; display: inline-block; text-align: center;">
			📥 Download Projects CSV
		</a>
	</div>
	
	<div style="border: 1px solid #ddd; border-radius: 5px; padding: 20px; background-color: #f9f9f9;">
		<h3 style="margin-top: 0;">Import Instructions</h3>
		<ol style="margin: 0; padding-left: 20px;">
			<li style="margin-bottom: 10px;">Download the Customers CSV file</li>
			<li style="margin-bottom: 10px;">Import customers into Kimai: <code>bin/console kimai:import:customer kimai_export_customers.csv --reader=csv</code></li>
			<li style="margin-bottom: 10px;">Download the Projects CSV file</li>
			<li style="margin-bottom: 10px;">Import projects into Kimai: <code>bin/console kimai:import:project kimai_export_projects.csv --reader=csv</code></li>
		</ol>
		<p style="margin-top: 15px; margin-bottom: 0;">
			<strong>Important:</strong> Import customers first, then projects. Projects reference customers by name.
		</p>
	</div>
	
	<div style="margin-top: 30px;">
		<a href="kimai_export.php" style="color: #007bff; text-decoration: none;">← Export More Customers</a>
		<span style="margin: 0 10px;">|</span>
		<a href="efforts.php" style="color: #007bff; text-decoration: none;">Back to Efforts</a>
	</div>
</div>
