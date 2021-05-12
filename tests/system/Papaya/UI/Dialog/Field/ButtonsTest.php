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

namespace Papaya\UI\Dialog\Field;
require_once __DIR__.'/../../../../../bootstrap.php';

class ButtonsTest extends \Papaya\TestCase {

  /**
   * @covers \Papaya\UI\Dialog\Field\Buttons::buttons
   */
  public function testFieldsGetImplicitCreate() {
    $field = new Buttons();
    $this->assertInstanceOf(
      \Papaya\UI\Dialog\Buttons::class, $field->buttons()
    );
  }

  /**
   * @covers \Papaya\UI\Dialog\Field\Buttons::buttons
   */
  public function testFieldsGetImplicitCreateWithDialog() {
    $dialog = $this
      ->getMockBuilder(\Papaya\UI\Dialog::class)
      ->setConstructorArgs(array(new \stdClass()))
      ->getMock();
    $field = new Buttons();
    $field->collection($this->getCollectionMock($dialog));
    $this->assertSame(
      $dialog, $field->buttons()->owner()
    );
  }

  /**
   * @covers \Papaya\UI\Dialog\Field\Buttons::buttons
   */
  public function testFieldsGetAfterSet() {
    $dialog = $this
      ->getMockBuilder(\Papaya\UI\Dialog::class)
      ->setConstructorArgs(array(new \stdClass()))
      ->getMock();
    $field = new Buttons();
    $field->collection($this->getCollectionMock($dialog));
    $buttons = $this->createMock(\Papaya\UI\Dialog\Buttons::class);
    $buttons
      ->expects($this->once())
      ->method('owner')
      ->with($this->equalTo($dialog));
    $this->assertSame(
      $buttons, $field->buttons($buttons)
    );
  }

  /**
   * @covers \Papaya\UI\Dialog\Field\Buttons::validate
   */
  public function testValidateExpectingTrue() {
    $field = new Buttons();
    $this->assertTrue($field->validate());
  }

  /**
   * @covers \Papaya\UI\Dialog\Field\Buttons::collect
   */
  public function testCollect() {
    $dialog = $this
      ->getMockBuilder(\Papaya\UI\Dialog::class)
      ->setConstructorArgs(array(new \stdClass()))
      ->getMock();
    $buttons = $this->createMock(\Papaya\UI\Dialog\Buttons::class);
    $buttons
      ->expects($this->once())
      ->method('collect')
      ->will($this->returnValue(TRUE));
    $field = new Buttons();
    $field->collection($this->getCollectionMock($dialog));
    $field->buttons($buttons);
    $this->assertTrue($field->collect());
  }

  /**
   * @covers \Papaya\UI\Dialog\Field\Buttons::collect
   */
  public function testCollectWithoutDialog() {
    $field = new Buttons();
    $field->collection($this->createMock(\Papaya\UI\Dialog\Buttons::class));
    $this->assertFalse($field->collect());
  }

  /**
   * @covers \Papaya\UI\Dialog\Field\Buttons::appendTo
   */
  public function testAppendTo() {
    $buttons = $this->createMock(\Papaya\UI\Dialog\Buttons::class);
    $buttons
      ->expects($this->once())
      ->method('appendTo')
      ->with($this->isInstanceOf(\Papaya\XML\Element::class));
    $buttons
      ->expects($this->once())
      ->method('count')
      ->will($this->returnValue(1));
    $field = new Buttons();
    $field->collection($this->createMock(\Papaya\UI\Dialog\Buttons::class));
    $field->buttons($buttons);
    $this->assertEquals(
    /** @lang XML */
      '<field class="DialogFieldButtons" error="no"><buttons/></field>',
      $field->getXML()
    );
  }

  /**
   * @covers \Papaya\UI\Dialog\Field\Buttons::appendTo
   */
  public function testAppendToWithId() {
    $buttons = $this->createMock(\Papaya\UI\Dialog\Buttons::class);
    $buttons
      ->expects($this->once())
      ->method('appendTo')
      ->with($this->isInstanceOf(\Papaya\XML\Element::class));
    $buttons
      ->expects($this->once())
      ->method('count')
      ->will($this->returnValue(1));
    $field = new Buttons();
    $field->setId('sampleId');
    $field->collection($this->createMock(\Papaya\UI\Dialog\Buttons::class));
    $field->buttons($buttons);
    $this->assertEquals(
    /** @lang XML */
      '<field class="DialogFieldButtons" error="no" id="sampleId"><buttons/></field>',
      $field->getXML()
    );
  }

  /**
   * @covers \Papaya\UI\Dialog\Field\Buttons::appendTo
   */
  public function testAppendToWithoutFields() {
    $field = new Buttons();
    $this->assertEquals(
      '',
      $field->getXML()
    );
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
