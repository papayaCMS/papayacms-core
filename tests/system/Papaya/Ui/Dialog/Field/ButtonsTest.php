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

require_once __DIR__.'/../../../../../bootstrap.php';

class PapayaUiDialogFieldButtonsTest extends \PapayaTestCase {

  /**
  * @covers \Papaya\Ui\Dialog\Field\Buttons::buttons
  */
  public function testFieldsGetImplicitCreate() {
    $field = new \Papaya\Ui\Dialog\Field\Buttons();
    $this->assertInstanceOf(
      \Papaya\Ui\Dialog\Buttons::class, $field->buttons()
    );
  }

  /**
  * @covers \Papaya\Ui\Dialog\Field\Buttons::buttons
  */
  public function testFieldsGetImplicitCreateWithDialog() {
    $dialog = $this
      ->getMockBuilder(\Papaya\Ui\Dialog::class)
      ->setConstructorArgs(array(new stdClass()))
      ->getMock();
    $field = new \Papaya\Ui\Dialog\Field\Buttons();
    $field->collection($this->getCollectionMock($dialog));
    $this->assertSame(
      $dialog, $field->buttons()->owner()
    );
  }

  /**
  * @covers \Papaya\Ui\Dialog\Field\Buttons::buttons
  */
  public function testFieldsSet() {
    $dialog = $this
      ->getMockBuilder(\Papaya\Ui\Dialog::class)
      ->setConstructorArgs(array(new stdClass()))
      ->getMock();
    $field = new \Papaya\Ui\Dialog\Field\Buttons();
    $field->collection($this->getCollectionMock($dialog));
    $buttons = $this->createMock(\Papaya\Ui\Dialog\Buttons::class);
    $buttons
      ->expects($this->once())
      ->method('owner')
      ->with($this->equalTo($dialog));
    $field->buttons($buttons);
    $this->assertAttributeSame(
      $buttons, '_buttons', $field
    );
  }

  /**
  * @covers \Papaya\Ui\Dialog\Field\Buttons::buttons
  */
  public function testFieldsGetAfterSet() {
    $dialog = $this
      ->getMockBuilder(\Papaya\Ui\Dialog::class)
      ->setConstructorArgs(array(new stdClass()))
      ->getMock();
    $field = new \Papaya\Ui\Dialog\Field\Buttons();
    $field->collection($this->getCollectionMock($dialog));
    $buttons = $this->createMock(\Papaya\Ui\Dialog\Buttons::class);
    $buttons
      ->expects($this->once())
      ->method('owner')
      ->with($this->equalTo($dialog));
    $this->assertSame(
      $buttons, $field->buttons($buttons)
    );
  }

  /**
  * @covers \Papaya\Ui\Dialog\Field\Buttons::validate
  */
  public function testValidateExpectingTrue() {
    $field = new \Papaya\Ui\Dialog\Field\Buttons();
    $this->assertTrue($field->validate());
  }

  /**
  * @covers \Papaya\Ui\Dialog\Field\Buttons::collect
  */
  public function testCollect() {
    $dialog = $this
      ->getMockBuilder(\Papaya\Ui\Dialog::class)
      ->setConstructorArgs(array(new stdClass()))
      ->getMock();
    $buttons = $this->createMock(\Papaya\Ui\Dialog\Buttons::class);
    $buttons
      ->expects($this->once())
      ->method('collect')
      ->will($this->returnValue(TRUE));
    $field = new \Papaya\Ui\Dialog\Field\Buttons();
    $field->collection($this->getCollectionMock($dialog));
    $field->buttons($buttons);
    $this->assertTrue($field->collect());
  }

  /**
  * @covers \Papaya\Ui\Dialog\Field\Buttons::collect
  */
  public function testCollectWithoutDialog() {
    $field = new \Papaya\Ui\Dialog\Field\Buttons();
    $field->collection($this->createMock(\Papaya\Ui\Dialog\Buttons::class));
    $this->assertFalse($field->collect());
  }

  /**
  * @covers \Papaya\Ui\Dialog\Field\Buttons::appendTo
  */
  public function testAppendTo() {
    $buttons = $this->createMock(\Papaya\Ui\Dialog\Buttons::class);
    $buttons
      ->expects($this->once())
      ->method('appendTo')
      ->with($this->isInstanceOf(\Papaya\Xml\Element::class));
    $buttons
      ->expects($this->once())
      ->method('count')
      ->will($this->returnValue(1));
    $field = new \Papaya\Ui\Dialog\Field\Buttons();
    $field->collection($this->createMock(\Papaya\Ui\Dialog\Buttons::class));
    $field->buttons($buttons);
    $this->assertEquals(
      /** @lang XML */
      '<field class="DialogFieldButtons" error="no"><buttons/></field>',
      $field->getXml()
    );
  }

  /**
  * @covers \Papaya\Ui\Dialog\Field\Buttons::appendTo
  */
  public function testAppendToWithId() {
    $buttons = $this->createMock(\Papaya\Ui\Dialog\Buttons::class);
    $buttons
      ->expects($this->once())
      ->method('appendTo')
      ->with($this->isInstanceOf(\Papaya\Xml\Element::class));
    $buttons
      ->expects($this->once())
      ->method('count')
      ->will($this->returnValue(1));
    $field = new \Papaya\Ui\Dialog\Field\Buttons();
    $field->setId('sampleId');
    $field->collection($this->createMock(\Papaya\Ui\Dialog\Buttons::class));
    $field->buttons($buttons);
    $this->assertEquals(
      /** @lang XML */
      '<field class="DialogFieldButtons" error="no" id="sampleId"><buttons/></field>',
      $field->getXml()
    );
  }

  /**
  * @covers \Papaya\Ui\Dialog\Field\Buttons::appendTo
  */
  public function testAppendToWithoutFields() {
    $field = new \Papaya\Ui\Dialog\Field\Buttons();
    $this->assertEquals(
      '',
      $field->getXml()
    );
  }

  /*************************
  * Mocks
  *************************/

  /**
   * @param object|NULL $owner
   * @return \PHPUnit_Framework_MockObject_MockObject|\Papaya\Ui\Dialog\Fields
   */
  public function getCollectionMock($owner = NULL) {
    $collection = $this->createMock(\Papaya\Ui\Dialog\Fields::class);
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
