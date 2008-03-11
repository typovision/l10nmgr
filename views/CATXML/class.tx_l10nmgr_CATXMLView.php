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

require_once(t3lib_extMgm::extPath('l10nmgr').'models/tools/class.tx_l10nmgr_xmltools.php');
require_once(t3lib_extMgm::extPath('l10nmgr').'models/tools/class.tx_l10nmgr_utf8tools.php');

/**
 * CATXMLView: Renders the XML for the use for translation agencies
 *
 * @author	Kasper Skaarhoj <kasperYYYY@typo3.com>
 * @author	Daniel Pötzinger <development@aoemedia.de>
 * @package TYPO3
 * @subpackage tx_l10nmgr
 */
class tx_l10nmgr_CATXMLView {


	var $l10ncfgObj;
	var $sysLang;

	function tx_l10nmgr_CATXMLView($l10ncfgObj, $sysLang) {
		global $BACK_PATH;
		$this->sysLang=$sysLang;
		$this->l10ncfgObj=$l10ncfgObj;

		$this->doc = t3lib_div::makeInstance('noDoc');
		$this->doc->backPath = $BACK_PATH;
	}


	/**
	 * Render the simple XML export
	 *
	 * @param	array		Translation data for configuration
	 * @return	string		HTML content
	 */
	function render() {
		$sysLang=$this->sysLang;
		$accumObj=$this->l10ncfgObj->getL10nAccumulatedInformationsObjectForLanguage($sysLang);
		$accum=$accumObj->getInfoArray();

		$errorMessage=array();
		$parseHTML = t3lib_div::makeInstance("t3lib_parseHTML_proc");
		$output = array();

			// Traverse the structure and generate XML output:
		foreach($accum as $pId => $page) {
			$output[]='<PageGrp id="'.$pId.'">'."\n";
			foreach($accum[$pId]['items'] as $table => $elements) {
				foreach($elements as $elementUid => $data) {
					if (!empty($data['ISOcode'])) {
						$targetIso2L = ' targetLang="'.$data['ISOcode'].'"';
					}

					if (is_array($data['fields'])) {
						$fieldsForRecord = array();
						foreach($data['fields'] as $key => $tData) {
							if (is_array($tData)) {
								list(,$uidString,$fieldName) = explode(':',$key); 
								list($uidValue) = explode('/',$uidString);

								$noChangeFlag = !strcmp(trim($tData['diffDefaultValue']),trim($tData['defaultValue']));

								if (!$this->modeOnlyChanged || !$noChangeFlag)	{
									reset($tData['previewLanguageValues']);
									$dataForTranslation=$tData['defaultValue'];
									// Substitutions for XML conformity here
									$_isTranformedXML=FALSE;

									if ($tData['fieldType']=='text' &&  $tData['isRTE']) { // to be substituted with check if field is RTE-enabled ($fieldName == "bodytext")
										$dataForTranslationTranformed = $parseHTML->TS_images_rte($dataForTranslation);
										$dataForTranslationTranformed = $parseHTML->TS_links_rte($dataForTranslationTranformed);
										$dataForTranslationTranformed = $parseHTML->TS_transform_rte($dataForTranslationTranformed,$css=1); // which mode is best?

										//substitute & with &amp;
										$dataForTranslationTranformed=str_replace('&','&amp;',$dataForTranslationTranformed);
										if (tx_l10nmgr_xmltools::isValidXML('<dummy>'.$dataForTranslationTranformed.'</dummy>')) {
											$_isTranformedXML=TRUE;
											$dataForTranslation=$dataForTranslationTranformed;
										}
									}
									if ($_isTranformedXML) {
										$output[]= "\t\t".'<Data table="'.$table.'" key="'.$key.'" transformations="1">'.$dataForTranslation.'</Data>'."\n";
									}
									else {
										$dataForTranslation=tx_l10nmgr_utf8tools::utf8_bad_strip($dataForTranslation);
										if (tx_l10nmgr_xmltools::isValidXML('<test><![CDATA['.$dataForTranslation.']]></test>')) {
											$output[]= "\t\t".'<Data table="'.$table.'" key="'.$key.'"><![CDATA['.$dataForTranslation.']]></Data>'."\n";
										}
										else {
											$errorMessage[]="\t\t".'<InternalMessage><![CDATA['.$elementUid.'/'.$table.'/'.$key.' has invalid characters and cannot be converted to correct XML/utf8]]></InternalMessage>';												
										}
									}
								}
							}
						}
					}
				}
			}
			$output[]='</PageGrp>'."\n";
		}

			// get ISO2L code for source language
		if ($this->l10ncfgObj->getData('sourceLangStaticId') && t3lib_extMgm::isLoaded('static_info_tables'))        {
			$sourceIso2L = '';
			$staticLangArr = t3lib_BEfunc::getRecord('static_languages',$this->l10ncfgObj->getData('sourceLangStaticId'),'lg_iso_2');
			$sourceIso2L = ' sourceLang="'.$staticLangArr['lg_iso_2'].'"';
		}

		$XML = '<?xml version="1.0" encoding="UTF-8"?>' . "\n";
		$XML .= '<TYPO3LOC l10ncfg="' . $this->l10ncfgObj->getData('uid') . '" sysLang="' . $sysLang . '"' . $sourceIso2L . $targetIso2L . '>' . "\n";
		$XML .= implode('', $output) . "\n"; 
		$XML .= "<count>" . count($output) . "</count>\n"; 
		$XML .= "<Internal>" . implode('', $errorMessage) . "</Internal>\n"; 
		$XML .= "</TYPO3LOC>"; 

		return $XML;
	}


	function getFilename() {

		if ($this->l10ncfgObj->getData('sourceLangStaticId') && t3lib_extMgm::isLoaded('static_info_tables'))        {
			$sourceIso2L = '';
			$staticLangArr = t3lib_BEfunc::getRecord('static_languages',$this->l10ncfgObj->getData('sourceLangStaticId'),'lg_iso_2');
			$sourceIso2L = ' sourceLang="'.$staticLangArr['lg_iso_2'].'"';
		}
		// Setting filename:
		$filename = 'xml_export_'.$staticLangArr['lg_iso_2'].'_'.date('dmy-Hi').'.xml';
		return $filename;
	}


	function setModeOnlyChanged() {
		$this->modeOnlyChanged=TRUE;
	}

}
if (defined('TYPO3_MODE') && $TYPO3_CONF_VARS['TYPO3_MODE']['XCLASS']['ext/l10nmgr/views/CATXML/class.tx_l10nmgr_CATXMLView.php'])	{
	include_once($TYPO3_CONF_VARS['TYPO3_MODE']['XCLASS']['ext/l10nmgr/views/CATXML/class.tx_l10nmgr_CATXMLView.php']);
}
?>
