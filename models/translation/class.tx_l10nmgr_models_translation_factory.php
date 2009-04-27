<?php
/***************************************************************
 *  Copyright notice
 *
 *  Copyright (c) 2009, AOE media GmbH <dev@aoemedia.de>
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

require_once t3lib_extMgm::extPath('l10nmgr') . 'models/translation/class.tx_l10nmgr_models_translation_data.php';

/**
 * Factory to build the translation object
 *
 * class.tx_l10nmgr_models_translation_factory.php
 *
 * @author Michael Klapper <klapper@aoemedia.de>
 * @copyright Copyright (c) 2009, AOE media GmbH <dev@aoemedia.de>
 * @license http://www.gnu.org/copyleft/gpl.html GNU General Public License
 * @version $Id$
 * @date $Date$
 * @since 24.04.2009 - 11:39:25
 * @package TYPO3
 * @subpackage tx_l10nmgr
 * @access public
 */
class tx_l10nmgr_models_translation_factory {

	/**
	 * @var tx_l10nmgr_models_translation_data
	 */
	protected $TranslationData = null;

	/**
	 * Build a translation data object from given XML data structure
	 *
	 * @param string $fullQualifiedFileName
	 * @access public
	 * @return tx_l10nmgr_models_translation_data
	 */
	public function create($fullQualifiedFileName) {

		if (! tx_mvc_validator_factory::getFileValidator()->isValid($fullQualifiedFileName)) {
			throw new tx_mvc_exception_fileNotFound('The given filename: "' . var_export($fullQualifiedFileName, true) . '" not found!');
		}

		$TranslationXML = @simplexml_load_file($fullQualifiedFileName, 'SimpleXMLElement', LIBXML_NOCDATA);
		if (! $TranslationXML instanceof SimpleXMLElement ) {
			throw new tx_mvc_exception_invalidContent('The file : "' . (string)$fullQualifiedFileName . '" contains no valid XML structure!');
		}

		$this->TranslationData = new tx_l10nmgr_models_translation_data();

		$this->extractMetaData($TranslationXML->head);
		$this->exractTranslation($TranslationXML->pageGrp);

		return $this->TranslationData;
	}

	/**
	 * Extract the page data from the XML import file into the tx_l10nmgr_models_translation_pageCollection object
	 *
	 * @param SimpleXMLElement $Page
	 * @access private
	 * @return void
	 */
	private function exractTranslation(SimpleXMLElement $Pagerows) {
		$PageCollection = new tx_l10nmgr_models_translation_pageCollection();

		foreach ($Pagerows as $pagerow) {
			$Page = new tx_l10nmgr_models_translation_page();
			$Page->setUid((int)$pagerow['id']);

			//each page has one element collection				
			$ElementCollection = new tx_l10nmgr_models_translation_elementCollection();
							
			foreach ($pagerow->children() as $field) {
				$table 		= (string)$field['table'];
				$uid 		= (int)$field['elementUid'];
			
				$Element 	= $this->createOrGetElementFromElementCollection($ElementCollection,$table,$uid);
				$Field 		= new tx_l10nmgr_models_translation_field();
				
				$Field->setFieldPath((string)$field['key']);
				$Field->setContent((string)$field);
				$Element->getFieldCollection()->append($Field);
			}
			
			
			$Page->setElementCollection($ElementCollection);
			$PageCollection->offsetSet((int)$pagerow['id'], $Page);
		}
			echo "Debug".__FILE__." ".__LINE__;
			print('<pre>');
			print_r($PageCollection);					
			print('</pre>');
			
		$this->TranslationData->setPagesCollection($PageCollection);
	}

	/**
	 * Creates a new Element instance if no one exists for this combination of table and uid,
	 * or return an existing instance.
	 *
	 * @param string $table
	 * @param int $uid
	 * @return tx_l10nmgr_models_translation_element
	 */
	protected function createOrGetElementFromElementCollection($ElementCollection,$table,$uid){
		//here we need to decide if an element is allready in the collection of not. This is
		//necessary because each child of the page is a field and there are multiple fields per element
		if($ElementCollection->hasElementWithTableAndUid($table,$uid)){
			//retrieve element
			$Element = $ElementCollection->getElementByTableAndUid($table,$uid);
		}else{
			//create element
			$Element = new tx_l10nmgr_models_translation_element();
			$Element->setTableName($table);
			$Element->setUid($uid);
			$ElementCollection->append($Element);
					
			$FieldCollection = new tx_l10nmgr_models_translation_fieldCollection();
			$Element->setFieldCollection($FieldCollection);				
		}
		
		return $Element;
	}
	
	/**
	 * Extract the meta information of the import XML file into the tx_l10nmgr_models_translation_data object
	 *
	 * @param SimpleXMLElement $Head
	 * @access private
	 * @return void
	 */
	private function extractMetaData(SimpleXMLElement $Head) {

		foreach ($Head as $metaData) {
			$this->TranslationData->setL10ncfgUid((int)$metaData->t3_l10ncfg);
			$this->TranslationData->setSysLanguageUid((int)$metaData->t3_sysLang);
			$this->TranslationData->setTargetLanguageUid((int)$metaData->t3_targeLang);
			$this->TranslationData->setSourceLanguageISOcode((string)$metaData->t3_sourceLang);
			$this->TranslationData->setBaseUrl((string)$metaData->baseURL);
			$this->TranslationData->setWorkspaceId((int)$metaData->t3_workspaceId);
			$this->TranslationData->setFieldCount((int)$metaData->t3_count);
			$this->TranslationData->setWordCount((int)$metaData->t3_wordCount);
			$this->TranslationData->setFormatVersion((float)$metaData->t3_formatVersion);

			foreach ($metaData->t3_internal as $messageIndes => $message) {
				//!TODO redefine the message point (alias "t3_internal")
//				$this->TranslationData->setMessages();
			}
		}
	}
}

if (defined('TYPO3_MODE') && $TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/l10nmgr/models/translation/class.tx_l10nmgr_models_translation_factory.php']) {
	include_once($TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/l10nmgr/models/translation/class.tx_l10nmgr_models_translation_factory.php']);
}

?>