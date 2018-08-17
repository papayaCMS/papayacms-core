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

namespace Papaya\UI\Dialog\Field\Select;

require_once __DIR__.'/../../../../../../bootstrap.php';

class BitmaskTest extends \PapayaTestCase {

  /**
   * @covers \Papaya\UI\Dialog\Field\Select\Bitmask::_createFilter
   */
  public function testConstructorInitializesFilter() {
    $select = new Bitmask(
      'Caption', 'name', array(1 => 'One', 2 => 'Two')
    );
    $this->assertEquals(
      new \Papaya\Filter\Bitmask(array(1, 2)), $select->getFilter()
    );
  }

  /**
   * @covers \Papaya\UI\Dialog\Field\Select\Bitmask::_createFilter
   */
  public function testConstructorInitializesFilterFromIterator() {
    $select = new Bitmask(
      'Caption', 'name', new \ArrayIterator(array(1 => 'One', 2 => 'Two'))
    );
    $this->assertEquals(
      new \Papaya\Filter\Bitmask(array(1, 2)), $select->getFilter()
    );
  }

  /**
   * @covers \Papaya\UI\Dialog\Field\Select\Bitmask::_createFilter
   */
  public function testConstructorInitializesFilterFromRecursiveIterator() {
    $select = new Bitmask(
      'Caption',
      'name',
      new \RecursiveArrayIterator(array('group' => array(1 => 'One', 2 => 'Two')))
    );
    $this->assertEquals(
      new \Papaya\Filter\Bitmask(array(1, 2)), $select->getFilter()
    );
  }

  /**
   * @covers \Papaya\UI\Dialog\Field\Select\Bitmask::getDefaultValue
   */
  public function testGetDefaultValue() {
    $select = new Bitmask(
      'Caption', 'name', array(1 => 'One', 2 => 'Two')
    );
    $select->setDefaultValue('1');
    $this->assertSame(1, $select->getDefaultValue());
  }

  /**
   * @covers \Papaya\UI\Dialog\Field\Select\Bitmask::_isOptionSelected
   */
  public function testAppendTo() {
    $select = new Bitmask(
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
      $select->getXML()
    );
  }

  /**
   * @covers \Papaya\UI\Dialog\Field\Select\Bitmask::_isOptionSelected
   */
  public function testAppendToWithSelectedElements() {
    $select = new Bitmask(
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
      $select->getXML()
    );
  }

  /**
   * @covers \Papaya\UI\Dialog\Field\Select\Bitmask::getCurrentValue
   */
  public function testGetCurrentValueFromDialogParameters() {
    $dialog = $this
      ->getMockBuilder(\Papaya\UI\Dialog::class)
      ->setConstructorArgs(array(new \stdClass()))
      ->getMock();
    $dialog
      ->expects($this->exactly(2))
      ->method('parameters')
      ->will($this->returnValue(new \Papaya\Request\Parameters(array('name' => array(1, 2)))));
    $select = new Bitmask(
      'Caption', 'name', array(1 => 'One', 2 => 'Two')
    );
    $select->collection($this->getCollectionMock($dialog));
    $this->assertEquals(3, $select->getCurrentValue());
  }

  /**
   * @covers \Papaya\UI\Dialog\Field\Select\Bitmask::getCurrentValue
   */
  public function testGetCurrentValueWhileDialogWasSendButNoOptionSelected() {
    $dialog = $this
      ->getMockBuilder(\Papaya\UI\Dialog::class)
      ->setConstructorArgs(array(new \stdClass()))
      ->getMock();
    $dialog
      ->expects($this->once())
      ->method('parameters')
      ->will($this->returnValue(new \Papaya\Request\Parameters(array())));
    $dialog
      ->expects($this->once())
      ->method('isSubmitted')
      ->will($this->returnValue(TRUE));
    $select = new Bitmask(
      'Caption', 'name', array(1 => 'One', 2 => 'Two')
    );
    $select->collection($this->getCollectionMock($dialog));
    $this->assertEquals(0, $select->getCurrentValue());
  }

  /**
   * @covers \Papaya\UI\Dialog\Field\Select\Bitmask::getCurrentValue
   */
  public function testGetCurrentValueWhileDialogWasNotSend() {
    $dialog = $this
      ->getMockBuilder(\Papaya\UI\Dialog::class)
      ->setConstructorArgs(array(new \stdClass()))
      ->getMock();
    $dialog
      ->expects($this->any())
      ->method('parameters')
      ->will($this->returnValue(new \Papaya\Request\Parameters(array())));
    $dialog
      ->expects($this->any())
      ->method('data')
      ->will($this->returnValue(new \Papaya\Request\Parameters(array())));
    $dialog
      ->expects($this->once())
      ->method('isSubmitted')
      ->will($this->returnValue(FALSE));
    $select = new Bitmask(
      'Caption', 'name', array(1 => 'One', 2 => 'Two')
    );
    $select->collection($this->getCollectionMock($dialog));
    $this->assertEquals(0, $select->getCurrentValue());
  }

  /**
   * @covers \Papaya\UI\Dialog\Field\Select\Bitmask::getCurrentValue
   */
  public function testGetCurrentValueFromDefaultValue() {
    $select = new Bitmask(
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
   * @return \PHPUnit_Framework_MockObject_MockObject|\Papaya\UI\Dialog\Fields
   */
  public function getCollectionMock($owner = NULL) {
    $collection = $this->createMock(\Papaya\UI\Dialog\Fields::class);
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
