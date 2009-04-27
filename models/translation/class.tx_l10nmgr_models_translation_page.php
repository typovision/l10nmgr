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

require_once t3lib_extMgm::extPath('l10nmgr') . 'models/translation/class.tx_l10nmgr_models_translation_elementCollection.php';

/**
 * Business object of an page which contains tx_l10nmgr_models_tranlation_elementCollection
 *
 * class.tx_l10nmgr_models_tranlation_page.php
 *
 * @author Michael Klapper <klapper@aoemedia.de>
 * @copyright Copyright (c) 2009, AOE media GmbH <dev@aoemedia.de>
 * @license http://www.gnu.org/copyleft/gpl.html GNU General Public License
 * @version $Id$
 * @date $Date$
 * @since 24.04.2009 - 14:04:05
 * @package TYPO3
 * @subpackage tx_l10nmgr
 * @access public
 */
class tx_l10nmgr_models_translation_page {

	/**
	 * Uid of the entity page eq database table record
	 *
	 * @var integer
	 */
	protected $uid = 0;

	/**
	 * Holds all related items of the current page
	 *
	 * @var tx_l10nmgr_models_tranlation_elementCollection
	 */
	protected $ElementCollection = null;

	/**
	 * @access public
	 * @return tx_l10nmgr_models_tranlation_elementCollection
	 */
	public function getElementCollection() {
		return $this->ElementCollection;
	}

	/**
	 * @return integer
	 * @access public
	 * @return integer
	 */
	public function getUid() {
		return $this->uid;
	}

	/**
	 * @param tx_l10nmgr_models_tranlation_elementCollection $ElementCollection
	 * @access public
	 * @return void
	 */
	public function setElementCollection($ElementCollection) {
		$this->ElementCollection = $ElementCollection;
	}

	/**
	 * @param integer $uid
	 * @access public
	 * @return void
	 */
	public function setUid($uid) {
		$this->uid = $uid;
	}
}

if (defined('TYPO3_MODE') && $TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/l10nmgr/models/translation/class.tx_l10nmgr_models_translation_page.php']) {
	include_once($TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/l10nmgr/models/translation/class.tx_l10nmgr_models_translation_page.php']);
}

?>