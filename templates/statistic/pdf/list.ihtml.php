<?php
// Fix: Initialize variables to prevent undefined variable warnings
$year = isset($year) ? $year : '';
$month = isset($month) ? $month : '';
$syear = isset($syear) ? $syear : '';
$smonth = isset($smonth) ? $smonth : '';
$sday = isset($sday) ? $sday : '';
$eyear = isset($eyear) ? $eyear : '';
$emonth = isset($emonth) ? $emonth : '';
$eday = isset($eday) ? $eday : '';
$mode = isset($mode) ? $mode : '';
$users = isset($users) ? $users : '';
// Fix: Initialize sum variables to prevent undefined variable warnings
$effort_sum = 0;
$price_sum = 0;
// Initialize arrays and flags to prevent undefined variable warnings
$foot_notes = array();
$foot_note = '';
$filled = false;

function documentHead() {
	global $pdf, $customer, $project, $year, $month, $_PJ_auth;
	global $syear, $smonth, $sday, $eyear, $emonth, $eday, $mode;

	$pdf->SetY($GLOBALS['_PJ_pdf_top_margin']);

	$pdf->SetFont($GLOBALS['_PJ_pdf_font_face'],'B',$GLOBALS['_PJ_pdf_header_font_size']);
	$y_line = $pdf->GetY();
	$pdf->SetX($GLOBALS['_PJ_pdf_left_margin']-3);
	$head = unhtmlentities($GLOBALS['_PJ_strings']['accounting']);
	$pdf->Cell($GLOBALS['_PJ_pdf_head_right'], $GLOBALS['_PJ_pdf_header_font_size']+2, $head, 0, 1, 'L', 0);
	$pdf->SetFont($GLOBALS['_PJ_pdf_font_face'],'',$GLOBALS['_PJ_pdf_header_font_size']);

	if($customer->giveValue("customer_name")) {
		$pdf->SetFont($GLOBALS['_PJ_pdf_font_face'],'B',$GLOBALS['_PJ_pdf_header_font_size']);
		$y_line = $pdf->GetY();
		$pdf->SetX($GLOBALS['_PJ_pdf_left_margin']-3);
		$head = unhtmlentities($GLOBALS['_PJ_strings']['customer']) . ": ";
		$pdf->Cell($GLOBALS['_PJ_pdf_head_right'], $GLOBALS['_PJ_pdf_header_font_size']+2, $head, 0, 0, 'L', 0);
		$pdf->SetFont($GLOBALS['_PJ_pdf_font_face'],'',$GLOBALS['_PJ_pdf_header_font_size']);
		$pdf->Cell($pdf->GetStringWidth($customer->giveValue("customer_name")), $GLOBALS['_PJ_pdf_header_font_size']+2, $customer->giveValue("customer_name"), 0, 1, 'L', 0);
	}

	if($project->giveValue("project_name")) {
		$pdf->SetX($GLOBALS['_PJ_pdf_left_margin']-3);
		$head = unhtmlentities($GLOBALS['_PJ_strings']['project']) . ": ";
		$pdf->SetFont($GLOBALS['_PJ_pdf_font_face'],'B',$GLOBALS['_PJ_pdf_header_font_size']);
		$pdf->Cell($GLOBALS['_PJ_pdf_head_right'], $GLOBALS['_PJ_pdf_header_font_size']+2, $head, 0, 0, 'L', 0);
		$pdf->SetFont($GLOBALS['_PJ_pdf_font_face'],'',$GLOBALS['_PJ_pdf_header_font_size']);
	
		$x_align = $pdf->GetX();
		$pdf->Cell($GLOBALS['_PJ_pdf_head_right'], $GLOBALS['_PJ_pdf_header_font_size']+2, $project->giveValue("project_name"), '', 1, 'L', 0);
		$pdf->SetX($x_align);
		if($project->giveValue("project_desc")) {
			$pdf->SetFont($GLOBALS['_PJ_pdf_font_face'],'',$GLOBALS['_PJ_pdf_small_font_size']-1);
			// Fix: Use GetPageWidth() method instead of protected property $w
			$pdf->MultiCell($pdf->GetPageWidth() - $GLOBALS['_PJ_pdf_left_margin'] - $GLOBALS['_PJ_pdf_right_margin'], $GLOBALS['_PJ_pdf_header_font_size']+2, "(" . $project->giveValue("project_desc") . ")", 0, "LT", 0);
		}
	}

	if(intval($year) && intval($month)) {
		$pdf->SetX($GLOBALS['_PJ_pdf_left_margin']-3);
		$head = unhtmlentities($GLOBALS['_PJ_strings']['period']) . ": ";
		$pdf->SetFont($GLOBALS['_PJ_pdf_font_face'],'B',$GLOBALS['_PJ_pdf_header_font_size']);
		$pdf->Cell($GLOBALS['_PJ_pdf_head_right'], $GLOBALS['_PJ_pdf_header_font_size']+2, $head, 0, 0, 'L', 0);
		$pdf->SetFont($GLOBALS['_PJ_pdf_font_face'],'',$GLOBALS['_PJ_pdf_header_font_size']);
		$pdf->Cell($GLOBALS['_PJ_pdf_head_right'], $GLOBALS['_PJ_pdf_header_font_size']+2, $GLOBALS['_PJ_months'][$month] . " $year", 0, 1, 'L', 0);
	}

	if(intval($syear) && intval($eyear)) {
		$pdf->SetX($GLOBALS['_PJ_pdf_left_margin']-3);
		$head = unhtmlentities($GLOBALS['_PJ_strings']['period']) . ": ";
		$pdf->SetFont($GLOBALS['_PJ_pdf_font_face'],'B',$GLOBALS['_PJ_pdf_header_font_size']);
		$pdf->Cell($GLOBALS['_PJ_pdf_head_right'], $GLOBALS['_PJ_pdf_header_font_size']+2, $head, 0, 0, 'L', 0);
		$pdf->SetFont($GLOBALS['_PJ_pdf_font_face'],'',$GLOBALS['_PJ_pdf_header_font_size']);
		$pdf->Cell($GLOBALS['_PJ_pdf_head_right'], $GLOBALS['_PJ_pdf_header_font_size']+2, "$sday. " . $GLOBALS['_PJ_months'][$smonth] . " $syear - $eday. " . $GLOBALS['_PJ_months'][$emonth] . " $eyear", 0, 1, 'L', 0);
	}

	$pdf->Ln();
}

function tableHead() {
	global $pdf;

	$pdf->SetY($pdf->GetY() + $GLOBALS['_PJ_pdf_table_cell_spacing']);
	$pdf->SetFillColor($GLOBALS['_PJ_pdf_table_head_bg_r'], $GLOBALS['_PJ_pdf_table_head_bg_g'], $GLOBALS['_PJ_pdf_table_head_bg_b']);
	$pdf->SetTextColor($GLOBALS['_PJ_pdf_table_head_fg_r'], $GLOBALS['_PJ_pdf_table_head_fg_g'], $GLOBALS['_PJ_pdf_table_head_fg_b']);
	$pdf->SetFont($GLOBALS['_PJ_pdf_font_face'],'B',$GLOBALS['_PJ_pdf_small_font_size']);
	if(isset($GLOBALS['fields']) && is_array($GLOBALS['fields'])) {
		foreach($GLOBALS['fields'] as $name => $string) {
			if($GLOBALS['field_widths'][$name] == 0) {
				continue;
			}
			$pdf->SetX($GLOBALS['field_lefts'][$name]);
			$pdf->Cell($GLOBALS['field_widths'][$name], $GLOBALS['_PJ_pdf_small_font_size']+2, $string, 0, 0, $GLOBALS['field_aligns'][$name], 1);
		}
	}
	reset($GLOBALS['fields']);
	$pdf->Ln();
	$pdf->SetY($pdf->GetY() + $GLOBALS['_PJ_pdf_table_cell_spacing']);
}

$GLOBALS['fields'] = array(
//		<field name>	=> <string>
		'count'			=> unhtmlentities($GLOBALS['_PJ_strings']['numbershort']),
		'customer'		=> unhtmlentities($GLOBALS['_PJ_strings']['customer']),
		'project'		=> unhtmlentities($GLOBALS['_PJ_strings']['project']),
		'agent'			=> unhtmlentities($GLOBALS['_PJ_strings']['agent']),
		'date'			=> unhtmlentities($GLOBALS['_PJ_strings']['date']),
		'billed'		=> unhtmlentities($GLOBALS['_PJ_strings']['billed']),
		'time' 			=> unhtmlentities($GLOBALS['_PJ_strings']['from_to']),
		'description'	=> unhtmlentities($GLOBALS['_PJ_strings']['description']),
		'effort'		=> unhtmlentities($GLOBALS['_PJ_strings']['hours_short']),
		'price'			=> unhtmlentities($GLOBALS['_PJ_strings']['costs'])
);

$GLOBALS['field_widths'] = $GLOBALS['_PJ_pdf_field_widths'];
$GLOBALS['field_aligns'] = $GLOBALS['_PJ_pdf_field_aligns'];

$rates		= new Rates();

$r_count	= $rates->giveCount();
if(intval($year) && intval($month)) {
	$statistic	= new Statistics($_PJ_auth, false, $customer, $project, $users, $mode);
	$statistic->loadMonth($year, $month, $mode);
} elseif(intval($syear) && intval($eyear)) {
	if(empty($smonth)) {
		$smonth = '01';
	}
	if(empty($sday)) {
		$sday = '01';
	}
	if(empty($emonth)) {
		$emonth = date('m');
	}
	if(empty($eday)) {
		$eday = date('d');
	}
	$statistic	= new Statistics($_PJ_auth, false, $customer, $project, $users, $mode);
	$statistic->loadTime("$syear-$smonth-$sday", "$eyear-$emonth-$eday", $mode);
} else {
	$statistic	= new Statistics($_PJ_auth, true, $customer, $project, $users, $mode);
}
$menge		= $statistic->effortCount();

$pdf = new PJPDF('L', 'pt');
// Fix: Open() method removed in modern FPDF - not needed
$pdf->AliasNbPages();
$pdf->AddPage();
$pdf->SetAutoPageBreak(false, 0);
if(!empty($cid)) {
	$GLOBALS['field_widths']['customer'] = 0;
}
if(!empty($pid)) {
	$GLOBALS['field_widths']['project'] = 0;
}
if($mode != 'billed') {
	$GLOBALS['field_widths']['billed'] = 0;
}

$GLOBALS['field_lefts'] = $pdf->calculateLeft($GLOBALS['field_widths']);

documentHead();
$GLOBALS['_PJ_pdf_table_top'] = $pdf->GetY();
tableHead();

$i = 0;
$note_count = 0;
$e_count = $statistic->count(($mode == 'billed'));
if(($mode == 'billed')) {
	$e_count += $statistic->count(true);
}
for($Fiii = 0; $Fiii < 1; $Fiii++) {
$statistic->reset();
while($statistic->nextEffort()) {
	$i++;
	$effort = $statistic->giveEffort();
	$foot_note_nmb = '';
	if(($effort->giveValue('note') != '')) {
		$foot_note_nmb = ++$note_count;
		$string = $effort->giveValue('note');
		$string = preg_replace("/<br>/", "", $string);
		$string = preg_replace("/<li>/", " - ", $string);
		$string = preg_replace("/\r/", "", $string);
		$string = preg_replace("/<[^>]+>\n/", "", $string);
		$string = preg_replace("/<[^>]+>/", "", $string);
		$foot_note .= "$foot_note_nmb) " . $string . "\n";
		$foot_notes[$foot_note_nmb] = $string;
	}

	if(!empty($filled)) {
		$pdf->SetFillColor($GLOBALS['_PJ_pdf_table_row0_bg_r'], $GLOBALS['_PJ_pdf_table_row0_bg_g'], $GLOBALS['_PJ_pdf_table_row0_bg_b']);
		$pdf->SetTextColor($GLOBALS['_PJ_pdf_table_row0_fg_r'], $GLOBALS['_PJ_pdf_table_row0_fg_g'], $GLOBALS['_PJ_pdf_table_row0_fg_b']);
		$filled = false;
	} else {
		$pdf->SetFillColor($GLOBALS['_PJ_pdf_table_row1_bg_r'], $GLOBALS['_PJ_pdf_table_row1_bg_g'], $GLOBALS['_PJ_pdf_table_row1_bg_b']);
		$pdf->SetTextColor($GLOBALS['_PJ_pdf_table_row1_fg_r'], $GLOBALS['_PJ_pdf_table_row1_fg_g'], $GLOBALS['_PJ_pdf_table_row1_fg_b']);
		$filled = true;
	}
	$pdf->SetFont($GLOBALS['_PJ_pdf_font_face'],'',$GLOBALS['_PJ_pdf_small_font_size']);
	$y_line = $pdf->GetY();
		
	$string = preg_replace("/<br>/", "", $effort->giveValue('description'));
	$string = preg_replace("/<li>/", " - ", $string);
	$string = preg_replace("/<[^>]+>/", " - ", $string);
	if(!empty($foot_note_nmb)) {
		$string .= " $foot_note_nmb)";
	}
	$pdf->SetX($GLOBALS['field_lefts']['description']);
	$pdf->MultiCell($GLOBALS['field_widths']['description'], $GLOBALS['_PJ_pdf_small_font_size']+2, $string, 0, "LT", 1);
	$y_next_line = $pdf->GetY();
	$pdf->SetY($y_line);

	if(empty($cid)) {
		$pdf->SetX($GLOBALS['field_lefts']['customer']);
		$pdf->Cell($GLOBALS['field_widths']['customer'], ($y_next_line-$y_line), $effort->giveValue('customer_name'), 0, 0, $GLOBALS['field_aligns']['customer'], 1);
		$pdf->SetY($y_line);
	}

	if($GLOBALS['field_widths']['count']) {
		$pdf->SetX($GLOBALS['field_lefts']['count']);
		$pdf->Cell($GLOBALS['field_widths']['count'], ($y_next_line-$y_line), "$i.", 0, 0, $GLOBALS['field_aligns']['count'], 1);
		$pdf->SetY($y_line);
	}

	if(empty($pid)) {
		$pdf->SetX($GLOBALS['field_lefts']['project']);
		$pdf->Cell($GLOBALS['field_widths']['project'], ($y_next_line-$y_line), $effort->giveValue('project_name'), 0, 0, $GLOBALS['field_aligns']['project'], 1);
		$pdf->SetY($y_line);
	}

	$pdf->SetX($GLOBALS['field_lefts']['agent']);
	$agent = $_PJ_auth->giveUserById($effort->giveValue('user'));
	$pdf->Cell($GLOBALS['field_widths']['agent'], ($y_next_line-$y_line), trim($agent['firstname'] . ' ' . $agent['lastname']), 0, 0, $GLOBALS['field_aligns']['agent'], 1);
	$pdf->SetY($y_line);

	$pdf->SetX($GLOBALS['field_lefts']['date']);
	$pdf->Cell($GLOBALS['field_widths']['date'], ($y_next_line-$y_line), formatDate($effort->giveValue('date'), $GLOBALS['_PJ_format_date']), 0, 0, $GLOBALS['field_aligns']['date'], 1);
	$pdf->SetY($y_line);

	$pdf->SetX($GLOBALS['field_lefts']['time']);
	$pdf->Cell($GLOBALS['field_widths']['time'], ($y_next_line-$y_line), formatTime($effort->giveValue('begin'), "H:i") . " - " . formatTime($effort->giveValue('end'), "H:i"), 0, 0, $GLOBALS['field_aligns']['time'], 1);
	$pdf->SetY($y_line);

	if(!empty($mode) and $mode == 'billed') {
		if($effort->giveValue('billed')) {
			$formatted_billed = formatDate($effort->giveValue('billed'), $GLOBALS['_PJ_format_date']);
		} else {
			$formatted_billed = '';
		}
		$pdf->SetX($GLOBALS['field_lefts']['billed']);
		$pdf->Cell($GLOBALS['field_widths']['billed'], ($y_next_line-$y_line), $formatted_billed, 0, 0, $GLOBALS['field_aligns']['billed'], 1);
		$pdf->SetY($y_line);
	}

	$pdf->SetX($GLOBALS['field_lefts']['effort']);
	$pdf->Cell($GLOBALS['field_widths']['effort'], ($y_next_line-$y_line), formatNumber($effort->giveValue('hours'), true), 0, 0, $GLOBALS['field_aligns']['effort'], 1);
	$effort_sum += $effort->giveValue('hours');
	$pdf->SetY($y_line);

	$pdf->SetX($GLOBALS['field_lefts']['price']);
	$price = $effort->giveValue("costs");
	$price_sum += $price;
	$pdf->Cell($GLOBALS['field_widths']['price'], ($y_next_line-$y_line), formatNumber($price, true) . " " . $GLOBALS['_PJ_currency'], 0, 0, $GLOBALS['field_aligns']['price'], 1);

	$pdf->SetY($y_next_line + $GLOBALS['_PJ_pdf_table_cell_spacing']);

	// end of page?
	// Fix: Use GetPageHeight() method instead of protected property $h
	if($pdf->GetY() >= ($pdf->GetPageHeight() - $GLOBALS['_PJ_pdf_bottom_margin'] - ($GLOBALS['_PJ_pdf_small_font_size']+2)*2)) {
		$pdf->AddPage();
		$pdf->SetY($GLOBALS['_PJ_pdf_top_margin']);
		// more rows to be printed left?
		if($i+1 < $e_count) {
			tableHead();
		}
		// restart with initial filling
		$filled = false;
	}
}
}
$pdf->SetTextColor($GLOBALS['_PJ_pdf_table_sum_fg_r'], $GLOBALS['_PJ_pdf_table_sum_fg_g'], $GLOBALS['_PJ_pdf_table_sum_fg_b']);
$y_line = $pdf->GetY();
$y_line += $GLOBALS['_PJ_pdf_sum_spacing'];
$pdf->SetY($y_line);

$pdf->SetFillColor($GLOBALS['_PJ_pdf_table_sum_bg_r'], $GLOBALS['_PJ_pdf_table_sum_bg_g'], $GLOBALS['_PJ_pdf_table_sum_bg_b']);
$pdf->SetFont($GLOBALS['_PJ_pdf_font_face'],'B',$GLOBALS['_PJ_pdf_small_font_size']);
$pdf->SetX($GLOBALS['_PJ_pdf_left_margin'] + $GLOBALS['_PJ_pdf_table_cell_spacing']);
$sum_width = $GLOBALS['field_lefts']['effort'] - $GLOBALS['_PJ_pdf_left_margin'] - $GLOBALS['_PJ_pdf_table_cell_spacing']*2;
$pdf->Cell($sum_width, $GLOBALS['_PJ_pdf_small_font_size']+2, unhtmlentities($GLOBALS['_PJ_strings']['sum']) . ":", 0, 0, 'R', 1);
$y_next_line = $pdf->GetY();
$pdf->SetY($y_line);


$pdf->SetX($GLOBALS['field_lefts']['effort']);
$pdf->Cell($GLOBALS['field_widths']['effort'], $GLOBALS['_PJ_pdf_small_font_size']+2, formatNumber($effort_sum, true), 0, 0, $GLOBALS['field_aligns']['effort'], 1);

$pdf->SetX($GLOBALS['field_lefts']['price']);
$pdf->Cell($GLOBALS['field_widths']['price'], $GLOBALS['_PJ_pdf_small_font_size']+2, formatNumber($price_sum, true) . " " . $GLOBALS['_PJ_currency'], 0, 0, $GLOBALS['field_aligns']['price'], 1);

if(is_array($foot_notes) && count($foot_notes)) {
	$pdf->SetTextColor($GLOBALS['_PJ_pdf_footnote_fg_r'], $GLOBALS['_PJ_pdf_footnote_fg_g'], $GLOBALS['_PJ_pdf_footnote_fg_b']);
	$pdf->SetFillColor($GLOBALS['_PJ_pdf_footnote_bg_r'], $GLOBALS['_PJ_pdf_footnote_bg_g'], $GLOBALS['_PJ_pdf_footnote_bg_b']);
	$pdf->SetAutoPageBreak(true, $GLOBALS['_PJ_pdf_bottom_margin']);
	$pdf->Ln();
	$pdf->Ln();
	$pdf->Ln();
	$pdf->SetFont($GLOBALS['_PJ_pdf_font_face'],'',$GLOBALS['_PJ_pdf_mini_font_size']);
	foreach($foot_notes as $foot_note_nmb => $foot_note) {
		if($pdf->GetY() >= ($pdf->h - $GLOBALS['_PJ_pdf_bottom_margin'] - ($GLOBALS['_PJ_pdf_small_font_size']+2)*2)) {
			$pdf->AddPage();
			$pdf->SetY($GLOBALS['_PJ_pdf_top_margin']);
		}
		$pdf->SetX($GLOBALS['_PJ_pdf_left_margin'] + $GLOBALS['_PJ_pdf_table_cell_spacing']);
		$pdf->Cell($pdf->GetStringWidth("$foot_note_nmb) $foot_note"), $GLOBALS['_PJ_pdf_mini_font_size']+2, "$foot_note_nmb) $foot_note", 0, 1, 'L', 0);
	}
}

if($project->giveValue('project_name')) {
	$pdf->Output('I', str_replace(' ', '_', $customer->giveValue('customer_name') . "-" . $project->giveValue('project_name') . ".pdf"));
} else if($customer->giveValue('customer_name')){
	$pdf->Output('I', str_replace(' ', '_', $customer->giveValue('customer_name') . ".pdf"));
} else {
	$pdf->Output('I', "effort.pdf");
}
?>