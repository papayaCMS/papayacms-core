<?php
/**
 * papaya CMS
 *
 * @copyright 2000-2018 by papayaCMS project - All rights reserved.
 * @link http://www.papaya-cms.com/
 * @license http://www.gnu.org/licenses/old-licenses/gpl-2.0.html GNU General Public License, version 2
 *
 *  You can redistribute and/or modify this script under the terms of the GNU General Public
 *  License (GPL) version 2, provided that the copyright and license notes, including these
 *  lines, remain unmodified. papaya is distributed in the hope that it will be useful, but
 *  WITHOUT ANY WARRANTY; without even the implied warranty of MERCHANTABILITY or FITNESS
 *  FOR A PARTICULAR PURPOSE.
 */

require_once __DIR__.'/../../../../../../bootstrap.php';

class PapayaUiDialogFieldSelectCheckboxesTest extends PapayaTestCase {

  /**
  * @covers PapayaUiDialogFieldSelectCheckboxes::_isOptionSelected
  * @covers PapayaUiDialogFieldSelectCheckboxes::_createFilter
  */
  public function testAppendTo() {
    $document = new PapayaXmlDocument();
    $node = $document->createElement('sample');
    $document->appendChild($node);
    $select = new PapayaUiDialogFieldSelectCheckboxes(
      'Caption', 'name', array(1 => 'One', 2 => 'Two')
    );
    $select->setMandatory(FALSE);
    $select->papaya($this->mockPapaya()->application());
    $select->appendTo($node);
    $this->assertXmlStringEqualsXmlString(
      /** @lang XML */
      '<field caption="Caption" class="DialogFieldSelectCheckboxes" error="no">
        <select name="name" type="checkboxes">
          <option value="1">One</option>
          <option value="2">Two</option>
        </select>
      </field>',
      $document->saveXML($node->firstChild)
    );
  }

  /**
  * @covers PapayaUiDialogFieldSelectCheckboxes::_isOptionSelected
  * @covers PapayaUiDialogFieldSelectCheckboxes::_createFilter
  */
  public function testAppendToWithSelectedElements() {
    $document = new PapayaXmlDocument();
    $node = $document->createElement('sample');
    $document->appendChild($node);
    $select = new PapayaUiDialogFieldSelectCheckboxes(
      'Caption', 'name', array(1 => 'One', 2 => 'Two')
    );
    $select->setDefaultValue(array(1, 2));
    $select->papaya($this->mockPapaya()->application());
    $select->appendTo($node);
    $this->assertXmlStringEqualsXmlString(
      /** @lang XML */
      '<field caption="Caption" class="DialogFieldSelectCheckboxes" error="no" mandatory="yes">
        <select name="name" type="checkboxes">
          <option value="1" selected="selected">One</option>
          <option value="2" selected="selected">Two</option>
        </select>
      </field>',
      $document->saveXML($node->firstChild)
    );
  }

  /**
  * @covers PapayaUiDialogFieldSelectCheckboxes::_isOptionSelected
  * @covers PapayaUiDialogFieldSelectCheckboxes::_createFilter
  */
  public function testAppendToWithIterator() {
    $select = new PapayaUiDialogFieldSelectCheckboxes(
      'Caption', 'name', new ArrayIterator(array(1 => 'One', 2 => 'Two'), TRUE)
    );
    $select->setDefaultValue(array(1, 2));
    $select->papaya($this->mockPapaya()->application());
    $this->assertXmlStringEqualsXmlString(
      /** @lang XML */
      '<field caption="Caption" class="DialogFieldSelectCheckboxes" error="no" mandatory="yes">
        <select name="name" type="checkboxes">
          <option value="1" selected="selected">One</option>
          <option value="2" selected="selected">Two</option>
        </select>
      </field>',
      $select->getXml()
    );
  }

  /**
  * @covers PapayaUiDialogFieldSelectCheckboxes::getCurrentValue
  */
  public function testGetCurrentValueFromDialogParameters() {
    $dialog = $this
      ->getMockBuilder(PapayaUiDialog::class)
      ->setConstructorArgs(array(new stdClass()))
      ->getMock();
    $dialog
      ->expects($this->exactly(2))
      ->method('parameters')
      ->will($this->returnValue(new PapayaRequestParameters(array('name' => array(1, 2)))));
    $select = new PapayaUiDialogFieldSelectCheckboxes(
      'Caption', 'name', array(1 => 'One', 2 => 'Two')
    );
    $select->collection($this->getCollectionMock($dialog));
    $this->assertEquals(array(1, 2), $select->getCurrentValue());
  }

  /**
  * @covers PapayaUiDialogFieldSelectCheckboxes::getCurrentValue
  */
  public function testGetCurrentValueFromSubmittedDialog() {
    $dialog = $this
      ->getMockBuilder(PapayaUiDialog::class)
      ->setConstructorArgs(array(new stdClass()))
      ->getMock();
    $dialog
      ->expects($this->once())
      ->method('isSubmitted')
      ->will($this->returnValue(TRUE));
    $dialog
      ->expects($this->any())
      ->method('parameters')
      ->will($this->returnValue(new PapayaRequestParameters()));
    $select = new PapayaUiDialogFieldSelectCheckboxes(
      'Caption', 'name', array(1 => 'One', 2 => 'Two')
    );
    $select->collection($this->getCollectionMock($dialog));
    $this->assertEquals(array(), $select->getCurrentValue());
  }

  /*************************
  * Mocks
  *************************/

  /**
   * @param object|NULL $owner
   * @return PHPUnit_Framework_MockObject_MockObject|PapayaUiDialogFields
   */
  public function getCollectionMock($owner = NULL) {
    $collection = $this->createMock(PapayaUiDialogFields::class);
    if ($owner) {
      $collection
        ->expects($this->any())
        ->method('hasOwner')
        ->will($this->returnValue(TRUE));
      $collection
        ->expects($this->any())
        ->method('owner')
        ->will($this->returnValue($owner));
    } else {
      $collection
        ->expects($this->any())
        ->method('hasOwner')
        ->will($this->returnValue(FALSE));
    }
    return $collection;
  }
}
