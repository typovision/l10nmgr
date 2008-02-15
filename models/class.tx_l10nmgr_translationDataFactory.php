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

require_once(t3lib_extMgm::extPath('l10nmgr').'models/class.tx_l10nmgr_translationData.php');


/**
 * Returns initialised TranslationData Objects
 * This is used to get TranslationData out of the import files
 *
 * @author	Kasper Skaarhoj <kasperYYYY@typo3.com>
 * @package TYPO3
 * @subpackage tx_l10nmgr
 */
class tx_l10nmgr_translationDataFactory {
	
	/**
	* public Factory method to get initialised tranlationData Object from the passed XML
	* 
	* @param path to the XML file
	* @return translationData Object with data
	**/
	function getTranslationDataFromCATXMLFile($xmlFile) {
		$fileContent = t3lib_div::getUrl($xmlFile);
		$data= $this->_getParsedCATXML($fileContent);
		if ($data ===false) {
			die($this->_errorMsg);
		}
		
		$translationData=t3lib_div::makeInstance('tx_l10nmgr_translationData');		
		$translationData->setTranslationData($data);
			
		return $translationData;
		
	}
	
	/**
	* public Factory method to get initialised tranlationData Object from the passed XML
	* 
	* @param path to the XML file
	* @return translationData Object with data
	**/
	function getTranslationDataFromExcelXMLFile($xmlFile) {
		$fileContent = t3lib_div::getUrl($xmlFile);
		
		$data= $this->_getParsedExcelXML($fileContent);
		if ($data ===false) {
			die($this->_errorMsg);
		}
		
		$translationData=t3lib_div::makeInstance('tx_l10nmgr_translationData');		
		$translationData->setTranslationData($data);
			
		return $translationData;
	}
	
	/** 
	* private internal fuction to parse the excel import XML format.
	* TODO: possibly make seperate class for this.
	*
	* @param String with XML
	* @return array with translated informations
	**/
	function _getParsedExcelXML( $fileContent ) {
			// Parse XML in a rude fashion:
		$xmlNodes = t3lib_div::xml2tree(str_replace('&nbsp;',' ',$fileContent));	// For some reason PHP chokes on incoming &nbsp; in XML!
		$translation = array();
		
		if (!is_array($xmlNodes)) {
			$this->_errorMsg.=$xmlNodes;
			return false;
		}

			// At least OpenOfficeOrg Calc changes the worksheet identifier. For now we better check for this, otherwise we cannot import translations edited with OpenOfficeOrg Calc.
		if ( isset( $xmlNodes['Workbook'][0]['ch']['Worksheet'] ) ) {
			$worksheetIdentifier = 'Worksheet';
		}
		if ( isset( $xmlNodes['Workbook'][0]['ch']['ss:Worksheet'] ) ) {
			$worksheetIdentifier = 'ss:Worksheet';
		}

			// OK, this method of parsing the XML really sucks, but it was 4:04 in the night and ... I have no clue to make it better on PHP4. Anyway, this will work for now. But is probably unstable in case a user puts formatting in the content of the translation! (since only the first CData chunk will be found!)
		if (is_array($xmlNodes['Workbook'][0]['ch'][$worksheetIdentifier][0]['ch']['Table'][0]['ch']['Row']))	{
			foreach($xmlNodes['Workbook'][0]['ch'][$worksheetIdentifier][0]['ch']['Table'][0]['ch']['Row'] as $row)	{
				if (!isset($row['ch']['Cell'][0]['attrs']['ss:Index']))	{
					list($Ttable, $Tuid, $Tkey) = explode('][',substr(trim($row['ch']['Cell'][0]['ch']['Data'][0]['values'][0]),12,-1));
					$translation[$Ttable][$Tuid][$Tkey] = $row['ch']['Cell'][3]['ch']['Data'][0]['values'][0];
				}
			}
		}
		return $translation;
	}
	
	

	/**
	* @param String with XML
	* @return array with translated informations
	**/
	function _getParsedCATXML($fileContent) {

		$parseHTML = t3lib_div::makeInstance("t3lib_parseHTML_proc");

		$xmlNodes = t3lib_div::xml2tree(str_replace('&nbsp;',' ',$fileContent));	// For some reason PHP chokes on incoming &nbsp; in XML!
		if (!is_array($xmlNodes)) {
			$this->_errorMsg.=$xmlNodes;
			return false;
		}
				$translation = array();
	
					// OK, this method of parsing the XML really sucks, but it was 4:04 in the night and ... I have no clue to make it better on PHP4. Anyway, this will work for now. But is probably unstable in case a user puts formatting in the content of the translation! (since only the first CData chunk will be found!)
				if (is_array($xmlNodes['TYPO3LOC'][0]['ch']['Data']))	{
					foreach($xmlNodes['TYPO3LOC'][0]['ch']['Data'] as $row)	{
						$attrs=$row['attrs'];
						
						list(,$uidString,$fieldName) = explode(':',$attrs['key']); 
						if ($attrs['transformations']=='1') { //substitute check with rte enabled fields from TCA
							$translationValue =$this->_getXMLFromTreeArray($row['ch']);							
							//$translationValue = $row['values'][0];
							$translationValue = $parseHTML->TS_transform_db($translationValue,$css=0); // removes links from content if not called first!
							$translationValue = $parseHTML->TS_images_db($translationValue);
							$translationValue = $parseHTML->TS_links_db($translationValue);
							//substitute & with &amp;
							$translationValue=str_replace('&amp;','&',$translationValue);
							$translation[$attrs['table']][$attrs['elementUid']][$attrs['key']] = $translationValue;						
						} else {
							$translation[$attrs['table']][$attrs['elementUid']][$attrs['key']] = $row['values'][0];						
						}
					}
				}
			return $translation;
	}
	
	function _getXMLFromTreeArray($array) {	
		if (is_array($array))	 {
			foreach ($array as $k=>$v) {
				
				if ($k=='ch') {								
						$xml.=$this->_getXMLFromTreeArray($v);												
				}
				elseif ($k=='values') {				
					$xml.=$v[0];
				}
				else {	
					if (is_array($v))	{
						foreach ($v as $k2=>$v2) {
							$xml.='<'.$k.'>';
							$xml.=$this->_getXMLFromTreeArray($v2);
							$xml.='</'.$k.'>';
						}
					}
				}		
			}
			return $xml;
		}
	}
	
}




if (defined('TYPO3_MODE') && $TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/l10nmgr/models/class.tx_l10nmgr_translationDataFactory.php'])	{
	include_once($TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/l10nmgr/models/class.tx_l10nmgr_translationDataFactory.php']);
}


?>
