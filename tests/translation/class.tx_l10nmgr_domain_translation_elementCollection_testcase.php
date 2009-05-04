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

require_once t3lib_extMgm::extPath('l10nmgr') . 'domain/translation/class.tx_l10nmgr_domain_translation_elementCollection.php';

/**
 * Verify that the fieldCollection works as expected
 *
 * class.tx_l10nmgr_domain_translation_elementCollection_testcase.php
 *
 * {@inheritdoc}
 *
 * @author Michael Klapper <klapper@aoemedia.de>
 * @copyright Copyright (c) 2009, AOE media GmbH <dev@aoemedia.de>
 * @license http://www.gnu.org/copyleft/gpl.html GNU General Public License
 * @version $Id$
 * @date $Date$
 * @since 04.05.2009 - 12:02:16
 * @see tx_phpunit_testcase
 * @package TYPO3
 * @subpackage tx_l10nmgr
 * @access public
 */
class tx_l10nmgr_domain_translation_elementCollection_testcase extends tx_phpunit_testcase {

	/**
	 * @var tx_l10nmgr_domain_translation_elementCollection
	 */
	protected $ElementCollection = null;

	/**
	 * Initialize a fresh instance of the tx_l10nmgr_domain_translation_elementCollection object
	 *
	 * @access public
	 * @author Michael Klapper <michael.klapper@aoemedia.de>
	 * @return void
	 */
	public function setUp() {
		$this->ElementCollection = new tx_l10nmgr_domain_translation_elementCollection();
	}

	/**
	 * Reset the tx_l10nmgr_domain_translation_elementCollection object
	 *
	 * @access public
	 * @author Michael Klapper <michael.klapper@aoemedia.de>
	 * @return void
	 */
	public function tearDown() {
		$this->ElementCollection = null;
	}

	/**
	 * Retrieve a field for testing.
	 *
	 * The field is not marked as imported or skipped.
	 *
	 * @access protected
	 * @author Michael Klapper <michael.klapper@aoemedia.de>
	 * @return tx_l10nmgr_domain_translation_field
	 */
	protected function fixtureField() {

		$Field = new tx_l10nmgr_domain_translation_field();
		$Field->setContent('Test content');
		$Field->setFieldPath('tt_content:1:title');
		$Field->setTransformation(false);

		return $Field;
	}

	/**
	 * Retrieve a fieldCollection for testing
	 *
	 * Containing three fields with the index key:
	 * - first
	 * - second
	 * - third
	 *
	 * @access protected
	 * @author Michael Klapper <michael.klapper@aoemedia.de>
	 * @return tx_l10nmgr_domain_translation_fieldCollection
	 */
	protected function fixtureFieldCollection() {

		$FieldCollection = new tx_l10nmgr_domain_translation_fieldCollection();
		$FieldCollection->offsetSet('first', $this->fixtureField());
		$FieldCollection->offsetSet('second', $this->fixtureField());
		$FieldCollection->offsetSet('third', $this->fixtureField());

		return $FieldCollection;
	}

	/**
	 * Retrieve a element for testing issues
	 *
	 * The Element contains:
	 * - FieldCollection
	 * - Three Fields which are located into the FieldCollection
	 *
	 * @access protected
	 * @author Michael Klapper <michael.klapper@aoemedia.de>
	 * @return tx_l10nmgr_domain_translation_element
	 */
	protected function fixtureElement() {

		$Element = new tx_l10nmgr_domain_translation_element();
		$Element->setUid(111);
		$Element->setTableName('tt_content');
		$Element->setFieldCollection($this->fixtureFieldCollection());

		return $Element;
	}

	/**
	 * Verify the instanceof field is of type "tx_l10nmgr_domain_translation_elementCollection"
	 *
	 * @access public
	 * @author Michael Klapper <michael.klapper@aoemedia.de>
	 * @return void
	 */
	public function test_rightInstanceOf() {
		$this->assertTrue (
			($this->ElementCollection instanceof tx_l10nmgr_domain_translation_elementCollection),
			'Object of wrong class'
		);
	}

	/**
	 * Verify that an empty fieldCollection indicate the isImported state as boolean true.
	 *
	 * If not the tx_l10nmgr_service_translationImport can not be finished while the empty fieldCollection never indicate the imported state as true.
	 *
	 * @access public
	 * @author Michael Klapper <michael.klapper@aoemedia.de>
	 * @return void
	 */
	public function test_indicateRightImportedStateOnEmptyElementCollection() {
		$this->assertTrue (
			($this->ElementCollection->isImported()),
			'tx_l10nmgr_domain_translation_elementCollection contains the wrong imported state on empty fieldCollection.'
		);
	}

	/**
	 * Verify that the fieldCollection retrieve the right import state if the containing field is and is not marked as imported.
	 *
	 * @access public
	 * @author Michael Klapper <michael.klapper@aoemedia.de>
	 * @return void
	 */
	public function test_indicateRightImportStateOnImportedElementsContainingTheElementCollection() {

		$this->ElementCollection->offsetSet('first', $this->fixtureElement());

		$this->assertFalse (
			($this->ElementCollection->isImported()),
			'tx_l10nmgr_domain_translation_elementCollection contains the wrong imported state on unprocessed field containing the collection.'
		);

		$this->ElementCollection->offsetGet('first')->getFieldCollection()->offsetGet('first')->markImported();
		$this->ElementCollection->offsetGet('first')->getFieldCollection()->offsetGet('second')->markImported();

		$this->assertFalse (
			($this->ElementCollection->isImported()),
			'tx_l10nmgr_domain_translation_elementCollection contains the wrong imported state on unprocessed field containing the collection.'
		);

		$this->ElementCollection->offsetGet('first')->getFieldCollection()->offsetGet('third')->markImported();
		$this->assertTrue (
			($this->ElementCollection->isImported()),
			'tx_l10nmgr_domain_translation_elementCollection contains the wrong imported state on unprocessed field containing the collection.'
		);

		$this->ElementCollection->offsetSet('second', $this->fixtureElement());
		$this->assertFalse (
			($this->ElementCollection->isImported()),
			'tx_l10nmgr_domain_translation_elementCollection contains the wrong imported state on unprocessed field containing the collection.'
		);

		$this->ElementCollection->offsetGet('second')->getFieldCollection()->offsetGet('first')->markImported();
		$this->ElementCollection->offsetGet('second')->getFieldCollection()->offsetGet('second')->markImported();
		$this->ElementCollection->offsetGet('second')->getFieldCollection()->offsetGet('third')->markImported();
		$this->assertTrue (
			($this->ElementCollection->isImported()),
			'tx_l10nmgr_domain_translation_elementCollection contains the wrong imported state on unprocessed field containing the collection.'
		);
	}

	/**
	 * Verify that the fieldCollection retrieve the right isImported state for tx_l10nmgr_domain_translation_field
	 * The advantage of this test is the mixed appearance of fields with an isImported flag and one with an isSkipped flag.
	 *
	 * Expected is an true isImported state of the ElementCollection
	 *
	 * @access public
	 * @author Michael Klapper <michael.klapper@aoemedia.de>
	 * @return unknown
	 */
	public function test_indicateRightImportStateOnSkippedElemnetsContainingTheElementCollection() {

		$this->ElementCollection->offsetSet('first', $this->fixtureElement());

		$this->assertFalse (
			($this->ElementCollection->isImported()),
			'tx_l10nmgr_domain_translation_elementCollection contains the wrong imported state on unprocessed field containing the collection.'
		);

		$this->ElementCollection->offsetGet('first')->getFieldCollection()->offsetGet('first')->markImported();
		$this->ElementCollection->offsetGet('first')->getFieldCollection()->offsetGet('second')->markImported();

		$this->assertFalse (
			($this->ElementCollection->isImported()),
			'tx_l10nmgr_domain_translation_elementCollection contains the wrong imported state on unprocessed field containing the collection.'
		);

		try {

			$this->ElementCollection->offsetGet('first')->getFieldCollection()->offsetGet('third')->markSkipped('Skipped while testing it.');

		} catch (tx_mvc_exception_skipped $e) {

			$this->assertTrue (
				($this->ElementCollection->isImported()),
				'tx_l10nmgr_domain_translation_elementCollection contains the wrong imported state on unprocessed field containing the collection.'
			);

			$this->ElementCollection->offsetSet('second', $this->fixtureElement());
			$this->assertFalse (
				($this->ElementCollection->isImported()),
				'tx_l10nmgr_domain_translation_elementCollection contains the wrong imported state on unprocessed field containing the collection.'
			);

			$this->ElementCollection->offsetGet('second')->getFieldCollection()->offsetGet('first')->markImported();
			$this->ElementCollection->offsetGet('second')->getFieldCollection()->offsetGet('second')->markImported();
			$this->ElementCollection->offsetGet('second')->getFieldCollection()->offsetGet('third')->markImported();
			$this->assertTrue (
				($this->ElementCollection->isImported()),
				'tx_l10nmgr_domain_translation_elementCollection contains the wrong imported state on unprocessed field containing the collection.'
			);

			return null;
		}

		$this->fail('tx_l10nmgr_domain_translation_field can not be marked as isImported.');
	}

	/**
	 * Verify that an exception is thrown when the wrong type is given to the offsetSet method
	 *
	 * @access public
	 * @expectedException InvalidArgumentException
	 * @author Michael Klapper <michael.klapper@aoemedia.de>
	 * @return void
	 */
	public function test_throwsExceptionOnWrongTypeGivenToTheElementCollectionUsingOffsetSet() {
		$this->ElementCollection->offsetSet('first', new stdClass());
	}

	/**
	 * Verify that an exception is thrown when the wrong type is given to the append method
	 *
	 * @access public
	 * @expectedException InvalidArgumentException
	 * @author Michael Klapper <michael.klapper@aoemedia.de>
	 * @return void
	 */
	public function test_throwsExceptionOnWrongTypeGivenToTheElementCollectionUsingAppend() {
		$this->ElementCollection->append(new stdClass());
	}

	/**
	 * Verify that the returned value by offsetGet are of the right type.
	 *
	 * @access public
	 * @author Michael Klapper <michael.klapper@aoemedia.de>
	 * @return void
	 */
	public function test_retriveRightTypeOfObjectFromTheElementCollectionUsingOffsetGet() {
		$this->ElementCollection->append($this->fixtureElement());

		$this->assertType (
			'tx_l10nmgr_domain_translation_element',
			$this->ElementCollection->offsetGet(0),
			'Object of wrong type returned using the offsetGet method of the tx_l10nmgr_domain_translaiton_elementCollection.'
		);
	}
}

if (defined('TYPO3_MODE') && $TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/l10nmgr/tests/translation/class.tx_l10nmgr_domain_translation_elementCollection_testcase.php']) {
	include_once($TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/l10nmgr/tests/translation/class.tx_l10nmgr_domain_translation_elementCollection_testcase.php']);
}

?>