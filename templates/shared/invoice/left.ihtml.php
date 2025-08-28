<!-- shared/invoice/left.ihtml - START -->

<?php

	$max_length	= 17;
	$nav_width = 120;

?>

<!-- Modern Navigation with Pastel Design for Invoice View -->
<div class="modern-nav animate-float">
	<!-- Logo Section -->
	<div style="padding: 1.5rem; text-align: center; border-bottom: 1px solid #e5e7eb; margin-bottom: 1rem;">
		<img src="<?php if(!empty($GLOBALS['_PJ_image_path'])) echo $GLOBALS['_PJ_image_path'] ?>/logo_te_150.png" width="120" height="15" border="0" class="animate-glow" style="filter: brightness(1.1);">
	</div>
	
	<!-- Invoice Context Navigation -->
	<div style="padding: 0 0.5rem;">
		<?php if(isset($invoice_data) && $invoice_data): ?>
		<!-- Current Invoice Info -->
		<div style="margin-bottom: 1rem; padding: 1rem; background: rgba(99, 102, 241, 0.05); border-radius: 0.5rem; border-left: 3px solid var(--primary-color);">
			<h4 style="color: var(--text-secondary); font-size: 0.75rem; font-weight: 600; text-transform: uppercase; letter-spacing: 0.05em; margin: 0 0 0.5rem 0; opacity: 0.7;">
				Current Invoice
			</h4>
			<div style="font-size: 0.875rem; color: var(--text-primary); font-weight: 500;">
				#<?= $invoice_data['invoice_number'] ?>
			</div>
			<div style="font-size: 0.75rem; color: var(--text-secondary); margin-top: 0.25rem;">
				<?= date('d.m.Y', strtotime($invoice_data['invoice_date'])) ?>
			</div>
		</div>

		<!-- Customer Navigation -->
		<?php if($invoice_data['customer_id']): ?>
		<a href="<?= $GLOBALS['_PJ_customer_inventory_script'] ?>?edit=1&cid=<?= $invoice_data['customer_id'] ?>" class="nav-item" style="display: flex; align-items: center; gap: 0.75rem; margin-bottom: 0.5rem;">
			<svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
				<path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"/>
				<circle cx="12" cy="7" r="4"/>
			</svg>
			<span>View Customer</span>
		</a>

		<!-- Customer Report -->
		<a href="<?= $GLOBALS['_PJ_reports_script'] ?>?cid=<?= $invoice_data['customer_id'] ?>&syear=<?= date('Y', strtotime($invoice_data['period_start'])) ?>&smonth=<?= date('n', strtotime($invoice_data['period_start'])) ?>&sday=<?= date('j', strtotime($invoice_data['period_start'])) ?>&eyear=<?= date('Y', strtotime($invoice_data['period_end'])) ?>&emonth=<?= date('n', strtotime($invoice_data['period_end'])) ?>&eday=<?= date('j', strtotime($invoice_data['period_end'])) ?>" class="nav-item" style="display: flex; align-items: center; gap: 0.75rem; margin-bottom: 0.5rem;">
			<svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
				<path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"/>
				<polyline points="14,2 14,8 20,8"/>
				<line x1="16" y1="13" x2="8" y2="13"/>
				<line x1="16" y1="17" x2="8" y2="17"/>
				<polyline points="10,9 9,9 8,9"/>
			</svg>
			<span>Customer Report</span>
		</a>

		<!-- Project Navigation (if project is set) -->
		<?php if($invoice_data['project_id']): ?>
		<a href="<?= $GLOBALS['_PJ_projects_inventory_script'] ?>?edit=1&pid=<?= $invoice_data['project_id'] ?>" class="nav-item" style="display: flex; align-items: center; gap: 0.75rem; margin-bottom: 0.5rem;">
			<svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
				<rect x="3" y="3" width="18" height="18" rx="2" ry="2"/>
				<rect x="9" y="9" width="6" height="6"/>
			</svg>
			<span>View Project</span>
		</a>
		<?php endif; ?>

		<!-- Efforts for this period -->
		<a href="<?= $GLOBALS['_PJ_efforts_inventory_script'] ?>?cid=<?= $invoice_data['customer_id'] ?><?= $invoice_data['project_id'] ? '&pid=' . $invoice_data['project_id'] : '' ?>&syear=<?= date('Y', strtotime($invoice_data['period_start'])) ?>&smonth=<?= date('n', strtotime($invoice_data['period_start'])) ?>&sday=<?= date('j', strtotime($invoice_data['period_start'])) ?>&eyear=<?= date('Y', strtotime($invoice_data['period_end'])) ?>&emonth=<?= date('n', strtotime($invoice_data['period_end'])) ?>&eday=<?= date('j', strtotime($invoice_data['period_end'])) ?>" class="nav-item" style="display: flex; align-items: center; gap: 0.75rem; margin-bottom: 0.5rem;">
			<svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
				<circle cx="12" cy="12" r="10"/>
				<polyline points="12,6 12,12 16,14"/>
			</svg>
			<span>Period Efforts</span>
		</a>
		<?php endif; ?>
		<?php endif; ?>

		<!-- Divider -->
		<div style="margin: 1rem 0; border-top: 1px solid rgba(99, 102, 241, 0.1);"></div>

		<!-- General Navigation -->
		<a href="<?= $GLOBALS['_PJ_customer_inventory_script'] ?><?= (isset($invoice_data) && $invoice_data['customer_id']) ? '?cid=' . $invoice_data['customer_id'] : '' ?>" class="nav-item" style="display: flex; align-items: center; gap: 0.75rem;">
			<svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
				<path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"/>
				<circle cx="12" cy="7" r="4"/>
			</svg>
			<span><?php if(!empty($GLOBALS['_PJ_strings']['customers'])) echo $GLOBALS['_PJ_strings']['customers'] ?></span>
		</a>
		
		<a href="<?= $GLOBALS['_PJ_projects_inventory_script'] ?><?= (isset($invoice_data) && $invoice_data['customer_id']) ? '?cid=' . $invoice_data['customer_id'] : '' ?><?= (isset($invoice_data) && $invoice_data['project_id']) ? '&pid=' . $invoice_data['project_id'] : '' ?>" class="nav-item" style="display: flex; align-items: center; gap: 0.75rem;">
			<svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
				<rect x="3" y="3" width="18" height="18" rx="2" ry="2"/>
				<rect x="9" y="9" width="6" height="6"/>
			</svg>
			<span><?php if(!empty($GLOBALS['_PJ_strings']['projects'])) echo $GLOBALS['_PJ_strings']['projects'] ?></span>
		</a>
		
		<a href="<?= $GLOBALS['_PJ_efforts_inventory_script'] ?><?= (isset($invoice_data) && $invoice_data['customer_id']) ? '?cid=' . $invoice_data['customer_id'] : '' ?><?= (isset($invoice_data) && $invoice_data['project_id']) ? '&pid=' . $invoice_data['project_id'] : '' ?>" class="nav-item" style="display: flex; align-items: center; gap: 0.75rem;">
			<svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
				<circle cx="12" cy="12" r="10"/>
				<polyline points="12,6 12,12 16,14"/>
			</svg>
			<span><?php if(!empty($GLOBALS['_PJ_strings']['efforts'])) echo $GLOBALS['_PJ_strings']['efforts'] ?></span>
		</a>
		
		<a href="<?= $GLOBALS['_PJ_reports_script'] ?><?= (isset($invoice_data) && $invoice_data['customer_id']) ? '?cid=' . $invoice_data['customer_id'] : '' ?><?= (isset($invoice_data) && $invoice_data['project_id']) ? '&pid=' . $invoice_data['project_id'] : '' ?>" class="nav-item" style="display: flex; align-items: center; gap: 0.75rem;">
			<svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
				<path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"/>
				<polyline points="14,2 14,8 20,8"/>
			</svg>
			<span><?php if(!empty($GLOBALS['_PJ_strings']['reports'])) echo $GLOBALS['_PJ_strings']['reports'] ?></span>
		</a>
	</div>
	
	<!-- Footer Section -->
	<div style="position: absolute; bottom: 0; left: 0; right: 0; padding: 1rem; text-align: center; border-top: 1px solid rgba(99, 102, 241, 0.1); background: linear-gradient(180deg, transparent 0%, rgba(99, 102, 241, 0.02) 100%);">
		<a href="https://github.com/rubo77/timeeffect" target="_blank" style="color: var(--text-secondary); text-decoration: none; font-size: 0.75rem; opacity: 0.8; transition: var(--transition-normal);" onmouseover="this.style.opacity='1'; this.style.color='var(--primary-color)'" onmouseout="this.style.opacity='0.8'; this.style.color='var(--text-secondary)'">
			TIMEEFFECT on GitHub
		</a>
		<?php
			if($GLOBALS['_PJ_session_length']) {
				$timeout = (int)$GLOBALS['_PJ_session_timeout'];
		?>
		<div style="margin-top: 0.5rem; font-size: 0.7rem; color: var(--text-secondary); opacity: 0.6;">
			<?php if(!empty($GLOBALS['_PJ_strings']['session_timeout'])) echo $GLOBALS['_PJ_strings']['session_timeout'] ?>: 
			<?php printf("%dm %02ds", (($timeout-($timeout%60))/60), ($timeout%60)); ?>
		</div>
		<?php
			}
		?>
	</div>
</div>

<!-- shared/invoice/left.ihtml - END -->
