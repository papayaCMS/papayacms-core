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

class PapayaUiDialogFieldSelectBitmaskTest extends PapayaTestCase {

  /**
  * @covers \PapayaUiDialogFieldSelectBitmask::_createFilter
  */
  public function testConstructorInitializesFilter() {
    $select = new \PapayaUiDialogFieldSelectBitmask(
      'Caption', 'name', array(1 => 'One', 2 => 'Two')
    );
    $this->assertEquals(
      new \PapayaFilterBitmask(array(1, 2)), $select->getFilter()
    );
  }

  /**
  * @covers \PapayaUiDialogFieldSelectBitmask::_createFilter
  */
  public function testConstructorInitializesFilterFromIterator() {
    $select = new \PapayaUiDialogFieldSelectBitmask(
      'Caption', 'name', new ArrayIterator(array(1 => 'One', 2 => 'Two'))
    );
    $this->assertEquals(
      new \PapayaFilterBitmask(array(1, 2)), $select->getFilter()
    );
  }

  /**
  * @covers \PapayaUiDialogFieldSelectBitmask::_createFilter
  */
  public function testConstructorInitializesFilterFromRecursiveIterator() {
    $select = new \PapayaUiDialogFieldSelectBitmask(
      'Caption',
      'name',
      new RecursiveArrayIterator(array('group' => array(1 => 'One', 2 => 'Two')))
    );
    $this->assertEquals(
      new \PapayaFilterBitmask(array(1, 2)), $select->getFilter()
    );
  }

  /**
  * @covers \PapayaUiDialogFieldSelectBitmask::getDefaultValue
  */
  public function testGetDefaultValue() {
    $select = new \PapayaUiDialogFieldSelectBitmask(
      'Caption', 'name', array(1 => 'One', 2 => 'Two')
    );
    $select->setDefaultValue('1');
    $this->assertSame(1, $select->getDefaultValue());
  }

  /**
  * @covers \PapayaUiDialogFieldSelectBitmask::_isOptionSelected
  */
  public function testAppendTo() {
    $select = new \PapayaUiDialogFieldSelectBitmask(
      'Caption', 'name', array(1 => 'One', 2 => 'Two')
    );
    $select->papaya($this->mockPapaya()->application());
    $this->assertXmlStringEqualsXmlString(
      /** @lang XML */
      '<field caption="Caption" class="DialogFieldSelectBitmask" error="no" mandatory="yes">
        <select name="name" type="checkboxes">
          <option value="1">One</option>
          <option value="2">Two</option>
        </select>
      </field>',
      $select->getXml()
    );
  }

  /**
  * @covers \PapayaUiDialogFieldSelectBitmask::_isOptionSelected
  */
  public function testAppendToWithSelectedElements() {
    $select = new \PapayaUiDialogFieldSelectBitmask(
      'Caption', 'name', array(1 => 'One', 2 => 'Two')
    );
    $select->setDefaultValue(3);
    $select->papaya($this->mockPapaya()->application());
    $this->assertXmlStringEqualsXmlString(
      /** @lang XML */
      '<field caption="Caption" class="DialogFieldSelectBitmask" error="no" mandatory="yes">
        <select name="name" type="checkboxes">
          <option value="1" selected="selected">One</option>
          <option value="2" selected="selected">Two</option>
        </select>
      </field>',
      $select->getXml()
    );
  }

  /**
  * @covers \PapayaUiDialogFieldSelectBitmask::getCurrentValue
  */
  public function testGetCurrentValueFromDialogParameters() {
    $dialog = $this
      ->getMockBuilder(PapayaUiDialog::class)
      ->setConstructorArgs(array(new stdClass()))
      ->getMock();
    $dialog
      ->expects($this->exactly(2))
      ->method('parameters')
      ->will($this->returnValue(new \PapayaRequestParameters(array('name' => array(1, 2)))));
    $select = new \PapayaUiDialogFieldSelectBitmask(
      'Caption', 'name', array(1 => 'One', 2 => 'Two')
    );
    $select->collection($this->getCollectionMock($dialog));
    $this->assertEquals(3, $select->getCurrentValue());
  }

  /**
  * @covers \PapayaUiDialogFieldSelectBitmask::getCurrentValue
  */
  public function testGetCurrentValueWhileDialogWasSendButNoOptionSelected() {
    $dialog = $this
      ->getMockBuilder(PapayaUiDialog::class)
      ->setConstructorArgs(array(new stdClass()))
      ->getMock();
    $dialog
      ->expects($this->once())
      ->method('parameters')
      ->will($this->returnValue(new \PapayaRequestParameters(array())));
    $dialog
      ->expects($this->once())
      ->method('isSubmitted')
      ->will($this->returnValue(TRUE));
    $select = new \PapayaUiDialogFieldSelectBitmask(
      'Caption', 'name', array(1 => 'One', 2 => 'Two')
    );
    $select->collection($this->getCollectionMock($dialog));
    $this->assertEquals(0, $select->getCurrentValue());
  }

  /**
  * @covers \PapayaUiDialogFieldSelectBitmask::getCurrentValue
  */
  public function testGetCurrentValueWhileDialogWasNotSend() {
    $dialog = $this
      ->getMockBuilder(PapayaUiDialog::class)
      ->setConstructorArgs(array(new stdClass()))
      ->getMock();
    $dialog
      ->expects($this->any())
      ->method('parameters')
      ->will($this->returnValue(new \PapayaRequestParameters(array())));
    $dialog
      ->expects($this->any())
      ->method('data')
      ->will($this->returnValue(new \PapayaRequestParameters(array())));
    $dialog
      ->expects($this->once())
      ->method('isSubmitted')
      ->will($this->returnValue(FALSE));
    $select = new \PapayaUiDialogFieldSelectBitmask(
      'Caption', 'name', array(1 => 'One', 2 => 'Two')
    );
    $select->collection($this->getCollectionMock($dialog));
    $this->assertEquals(0, $select->getCurrentValue());
  }

  /**
  * @covers \PapayaUiDialogFieldSelectBitmask::getCurrentValue
  */
  public function testGetCurrentValueFromDefaultValue() {
    $select = new \PapayaUiDialogFieldSelectBitmask(
      'Caption', 'name', array(1 => 'One', 2 => 'Two')
    );
    $select->setDefaultValue(3);
    $this->assertEquals(3, $select->getCurrentValue());
  }

  /*************************
  * Mocks
  *************************/

  /**
   * @param object|NULL $owner
   * @return \PHPUnit_Framework_MockObject_MockObject|\PapayaUiDialogFields
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
