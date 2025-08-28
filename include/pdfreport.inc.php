<?php
include_once($_PJ_include_path . '/cezpdf.inc.php');

$_PJ_pdf_fonts = array(
					'b'=>'Lucida-Sans-Bold.afm',
					'i'=>'Lucida-Sans-Oblique.afm',
					'bi'=>'Lucida-Sans-BoldOblique.afm',
					'ib'=>'Lucida-Sans-BoldOblique.afm',
					'bb'=>'Lucida-Sans-Bold.afm'
				);

class PDFReport extends Cezpdf {
	var $footnotes		= array();
	var $footnote_count	= 0;

	function PDFReport($paper = 'a4', $orientation = 'landscape') {
		// Initialize ez array first
		if (!isset($this->ez)) {
			$this->ez = array('fontSize' => 10);
		}
		
		$this->Cezpdf($paper, $orientation);
		
		// Force initialization of all required margins and page dimensions
		$this->ez['pageWidth'] = isset($this->ez['pageWidth']) ? $this->ez['pageWidth'] : 595;
		$this->ez['pageHeight'] = isset($this->ez['pageHeight']) ? $this->ez['pageHeight'] : 842;
		$this->ez['leftMargin'] = isset($this->ez['leftMargin']) ? $this->ez['leftMargin'] : 30;
		$this->ez['rightMargin'] = isset($this->ez['rightMargin']) ? $this->ez['rightMargin'] : 30;
		$this->ez['topMargin'] = isset($this->ez['topMargin']) ? $this->ez['topMargin'] : 30;
		$this->ez['bottomMargin'] = isset($this->ez['bottomMargin']) ? $this->ez['bottomMargin'] : 30;
		
		// Set current writing position
		$this->y = $this->ez['pageHeight'] - $this->ez['topMargin'];
		
		$this->setFontFamily('Lucida-Sans.afm', $GLOBALS['_PJ_pdf_fonts']);
		$this->selectFont($GLOBALS['_PJ_include_path'] . '/font/Lucida-Sans.afm');
	}

	function getPageInnerMetrics() {
		return array($this->ez['pageWidth'] - $this->ez['leftMargin'] - $this->ez['rightMargin'], $this->ez['pageHeight'] - $this->ez['topMargin'] - $this->ez['bottomMargin']);
	}

	function getTopLeft() {
		return array($this->ez['leftMargin'], $this->ez['pageHeight'] - $this->ez['topMargin']);
	}

	function getBottomLeft() {
		return array($this->ez['leftMargin'], $this->ez['bottomMargin']);
	}

	function getFootnotes() {
		return $this->footnotes;
	}

	function footnote($info) {
		$this->addText($info['x'], $info['y'] - $info['decender'], $info['height'] - 4, ++$this->footnote_count . ")");
		$this->footnotes[$this->footnote_count] = urldecode($info['p']);
	}

	function small($info) {
		list($font_size, $text)	= explode('~', $info['p']);
		if($font_size == '' || $text == '') {
			return;
		}
		$xpos	= $info['x'];
		$ypos	= $info['y'];
		$this->addText($info['x'], $info['y'] - $info['decender'], $font_size, urldecode($text));
	}
}
?>