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

class CheckboxesTest extends \Papaya\TestCase {

  /**
   * @covers \Papaya\UI\Dialog\Field\Select\Checkboxes::_isOptionSelected
   * @covers \Papaya\UI\Dialog\Field\Select\Checkboxes::_createFilter
   */
  public function testAppendTo() {
    $document = new \Papaya\XML\Document();
    $node = $document->createElement('sample');
    $document->appendChild($node);
    $select = new Checkboxes(
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
   * @covers \Papaya\UI\Dialog\Field\Select\Checkboxes::_isOptionSelected
   * @covers \Papaya\UI\Dialog\Field\Select\Checkboxes::_createFilter
   */
  public function testAppendToWithSelectedElements() {
    $document = new \Papaya\XML\Document();
    $node = $document->createElement('sample');
    $document->appendChild($node);
    $select = new Checkboxes(
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
   * @covers \Papaya\UI\Dialog\Field\Select\Checkboxes::_isOptionSelected
   * @covers \Papaya\UI\Dialog\Field\Select\Checkboxes::_createFilter
   */
  public function testAppendToWithIterator() {
    $select = new Checkboxes(
      'Caption', 'name', new \ArrayIterator(array(1 => 'One', 2 => 'Two'), TRUE)
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
      $select->getXML()
    );
  }

  /**
   * @covers \Papaya\UI\Dialog\Field\Select\Checkboxes::getCurrentValue
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
    $select = new Checkboxes(
      'Caption', 'name', array(1 => 'One', 2 => 'Two')
    );
    $select->collection($this->getCollectionMock($dialog));
    $this->assertEquals(array(1, 2), $select->getCurrentValue());
  }

  /**
   * @covers \Papaya\UI\Dialog\Field\Select\Checkboxes::getCurrentValue
   */
  public function testGetCurrentValueFromSubmittedDialog() {
    $dialog = $this
      ->getMockBuilder(\Papaya\UI\Dialog::class)
      ->setConstructorArgs(array(new \stdClass()))
      ->getMock();
    $dialog
      ->expects($this->once())
      ->method('isSubmitted')
      ->will($this->returnValue(TRUE));
    $dialog
      ->expects($this->any())
      ->method('parameters')
      ->will($this->returnValue(new \Papaya\Request\Parameters()));
    $select = new Checkboxes(
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
