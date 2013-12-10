<?php

class VES_PdfPro_Model_Processors_Easypdf extends Mage_Core_Model_Abstract
{
	public function process($apiKey, $datas, $type){
		$pdfPro = new PdfPro($apiKey);
		return $pdfPro->getPDF($datas,$type);
	}
}