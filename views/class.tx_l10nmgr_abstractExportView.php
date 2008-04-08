<?php
/***************************************************************
*  Copyright notice
*
*  (c) 2006 Kasper Skårhøj <kasperYYYY@typo3.com>
*  All rights reserved
*
*  This script is part of the TYPO3 project. The TYPO3 project is
*  free software; you can redistribute it and/or modify
*  it under the terms of the GNU General Public License as published by
*  the Free Software Foundation; either version 2 of the License, or
*  (at your option) any later version.
*
*  The GNU General Public License can be found at
*  http://www.gnu.org/copyleft/gpl.html.
*
*  This script is distributed in the hope that it will be useful,
*  but WITHOUT ANY WARRANTY; without even the implied warranty of
*  MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
*  GNU General Public License for more details.
*
*  This copyright notice MUST APPEAR in all copies of the script!
***************************************************************/

class tx_l10nmgr_abstractExportView {

	/**
	 * @var	tx_l10nmgr_l10nConfiguration		$l10ncfgObj		The language configuration object
	 */
	var $l10ncfgObj;

	/**
	 * @var	integer		$sysLang		The sys_language_uid of language to export
	 */
	var $sysLang;

	function setModeOnlyChanged() {
		
		$this->modeOnlyChanged=TRUE;
	}
	
	// save the information of the export in the database table 'tx_l10nmgr_sava_data'
	function saveExportInformation($accumObj,$accum){
			
		// information for database 
		$getInforamtionExportData = array();
		$getInforamtionExportData = $this->l10ncfgObj->l10ncfg;
		$sysLang = $this->sysLang;
		// get current date
		$date = time();
		
		// query to insert the data in the database
		$field_values = array('sys_language_uid' => $getInforamtionExportData['ncfcewithdefaultlanguage'],'translation_lang' => $sysLang,'crdate' => $date,'tstamp' => $date,'l10ncfg_id' => $getInforamtionExportData['uid'],'pid' => $getInforamtionExportData['pid'],'tablelist' => $getInforamtionExportData['tablelist'],'title' => $getInforamtionExportData['title'],'cruser_id' => $getInforamtionExportData['cruser_id']);
		$res = $GLOBALS['TYPO3_DB']->exec_INSERTquery('tx_l10nmgr_exportdata', $field_values);

		#t3lib_div::debug($GLOBALS['TYPO3_DB']->exec_INSERTquery('tx_l10nmgr_exportdata', $field_values));
		return $res;
	}
	
	// create a filename to save the File
	function getLocalFilename(){
		
	}
	
	// save the exported files in the file /uploads/tx_10lnmgr/saved_files/
	function saveFile(){
		
	}
	
}
	
if (defined('TYPO3_MODE') && $TYPO3_CONF_VARS['TYPO3_MODE']['XCLASS']['ext/l10nmgr/views/class.tx_l10nmgr_abstractExportView.php'])	{
	include_once($TYPO3_CONF_VARS['TYPO3_MODE']['XCLASS']['ext/l10nmgr/views/class.tx_l10nmgr_abstractExportView.php']);
}
?>