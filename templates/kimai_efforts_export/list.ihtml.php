<!-- kimai_efforts_export.ihtml - START -->
<div class="container">
	<h2><?php echo $center_title; ?></h2>
	<p>Export all efforts from TimeEffect to Kimai CSV format.</p>
	
	<div class="card">
		<div class="card-body">
			<form method="post" action="">
				<input type="hidden" name="export" value="1">
				<button type="submit" class="btn btn-primary">
					Export All Efforts to CSV
				</button>
			</form>
		</div>
	</div>
	
	<div class="mt-4">
		<h3>Import Instructions</h3>
		<ol>
			<li>Download the CSV file</li>
			<li>Log in to Kimai: <a href="http://localhost:8351" target="_blank">http://localhost:8351</a></li>
			<li>Go to Administration → Import</li>
			li>Select "Timesheet" as import type</li>
			<li>Upload the CSV file</li>
			<li>Click "Import"</li>
		</ol>
		<p><strong>Note:</strong> Make sure users exist in Kimai before importing timesheets.</p>
	</div>
</div>
<!-- kimai_efforts_export.ihtml - END -->
