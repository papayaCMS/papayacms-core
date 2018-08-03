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

class PapayaUiDialogFieldGroupButtonsTest extends \PapayaTestCase {

  /**
  * @covers \Papaya\UI\Dialog\Field\Group\Buttons::__construct
  */
  public function testConstructor() {
    $group = new \Papaya\UI\Dialog\Field\Group\Buttons('Group Caption');
    $this->assertEquals(
      'Group Caption', $group->getCaption()
    );
  }

  /**
  * @covers \Papaya\UI\Dialog\Field\Group\Buttons::buttons
  */
  public function testFieldsGetImplicitCreate() {
    $group = new \Papaya\UI\Dialog\Field\Group\Buttons('Group Caption');
    $this->assertInstanceOf(
      \Papaya\UI\Dialog\Buttons::class, $group->buttons()
    );
  }

  /**
  * @covers \Papaya\UI\Dialog\Field\Group\Buttons::buttons
  */
  public function testFieldsGetImplicitCreateWithDialog() {
    $dialog = $this
      ->getMockBuilder(\Papaya\UI\Dialog::class)
      ->setConstructorArgs(array(new stdClass()))
      ->getMock();
    $group = new \Papaya\UI\Dialog\Field\Group\Buttons('Group Caption');
    $group->collection($this->getCollectionMock($dialog));
    $this->assertSame(
      $dialog, $group->buttons()->owner()
    );
  }

  /**
  * @covers \Papaya\UI\Dialog\Field\Group\Buttons::buttons
  */
  public function testFieldsSet() {
    $dialog = $this
      ->getMockBuilder(\Papaya\UI\Dialog::class)
      ->setConstructorArgs(array(new stdClass()))
      ->getMock();
    $group = new \Papaya\UI\Dialog\Field\Group\Buttons('Group Caption');
    $group->collection($this->getCollectionMock($dialog));
    $buttons = $this->createMock(\Papaya\UI\Dialog\Buttons::class);
    $buttons
      ->expects($this->once())
      ->method('owner')
      ->with($this->equalTo($dialog));
    $group->buttons($buttons);
    $this->assertAttributeSame(
      $buttons, '_buttons', $group
    );
  }

  /**
  * @covers \Papaya\UI\Dialog\Field\Group\Buttons::buttons
  */
  public function testFieldsGetAfterSet() {
    $dialog = $this
      ->getMockBuilder(\Papaya\UI\Dialog::class)
      ->setConstructorArgs(array(new stdClass()))
      ->getMock();
    $group = new \Papaya\UI\Dialog\Field\Group\Buttons('Group Caption');
    $group->collection($this->getCollectionMock($dialog));
    $buttons = $this->createMock(\Papaya\UI\Dialog\Buttons::class);
    $buttons
      ->expects($this->once())
      ->method('owner')
      ->with($this->equalTo($dialog));
    $this->assertSame(
      $buttons, $group->buttons($buttons)
    );
  }

  /**
  * @covers \Papaya\UI\Dialog\Field\Group\Buttons::validate
  */
  public function testValidateExpectingTrue() {
    $group = new \Papaya\UI\Dialog\Field\Group\Buttons('Group Caption');
    $this->assertTrue($group->validate());
  }

  /**
  * @covers \Papaya\UI\Dialog\Field\Group\Buttons::validate
  */
  public function testValidateWithoutDialogExpectingFalse() {
    $group = new \Papaya\UI\Dialog\Field\Group\Buttons('Group Caption');
    $this->assertTrue($group->validate());
  }

  /**
  * @covers \Papaya\UI\Dialog\Field\Group\Buttons::collect
  */
  public function testCollect() {
    $dialog = $this
      ->getMockBuilder(\Papaya\UI\Dialog::class)
      ->setConstructorArgs(array(new stdClass()))
      ->getMock();
    $buttons = $this->createMock(\Papaya\UI\Dialog\Buttons::class);
    $buttons
      ->expects($this->once())
      ->method('collect')
      ->will($this->returnValue(TRUE));
    $group = new \Papaya\UI\Dialog\Field\Group\Buttons('Group Caption');
    $group->collection($this->getCollectionMock($dialog));
    $group->buttons($buttons);
    $this->assertTrue($group->collect());
  }

  /**
  * @covers \Papaya\UI\Dialog\Field\Group\Buttons::collect
  */
  public function testCollectWithoutDialog() {
    $group = new \Papaya\UI\Dialog\Field\Group\Buttons('Group Caption');
    $group->collection($this->createMock(\Papaya\UI\Dialog\Buttons::class));
    $this->assertFalse($group->collect());
  }

  /**
  * @covers \Papaya\UI\Dialog\Field\Group\Buttons::appendTo
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
    $group = new \Papaya\UI\Dialog\Field\Group\Buttons('Group Caption');
    $group->collection($this->createMock(\Papaya\UI\Dialog\Buttons::class));
    $group->buttons($buttons);
    $this->assertEquals(
      /** @lang XML */
      '<field-group caption="Group Caption"/>',
      $group->getXML()
    );
  }

  /**
  * @covers \Papaya\UI\Dialog\Field\Group\Buttons::appendTo
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
    $group = new \Papaya\UI\Dialog\Field\Group\Buttons('Group Caption');
    $group->setId('sampleId');
    $group->collection($this->createMock(\Papaya\UI\Dialog\Buttons::class));
    $group->buttons($buttons);
    $this->assertEquals(
      /** @lang XML */
      '<field-group caption="Group Caption" id="sampleId"/>',
      $group->getXML()
    );
  }

  /**
  * @covers \Papaya\UI\Dialog\Field\Group\Buttons::appendTo
  */
  public function testAppendToWithoutFields() {
    $document = new \Papaya\XML\Document();
    $node = $document->createElement('sample');
    $document->appendChild($node);
    $group = new \Papaya\UI\Dialog\Field\Group\Buttons('Group Caption');
    $group->appendTo($node);
    $this->assertEquals(
      /** @lang XML */
      '<sample/>',
      $document->saveXML($node)
    );
  }

  /**
  * @covers \Papaya\UI\Dialog\Field\Group\Buttons::collection
  */
  public function testCollectionGetAfterSet() {
    $owner = $this->createMock(\Papaya\UI\Dialog::class);
    $papaya = $this->mockPapaya()->application();
    $collection = $this->createMock(\Papaya\UI\Control\Collection::class);
    $collection
      ->expects($this->once())
      ->method('papaya')
      ->will($this->returnValue($papaya));
    $collection
      ->expects($this->any())
      ->method('hasOwner')
      ->will($this->returnValue(TRUE));
    $collection
      ->expects($this->any())
      ->method('owner')
      ->will($this->returnValue($owner));
    $buttons = $this->createMock(\Papaya\UI\Dialog\Buttons::class);
    $buttons
      ->expects($this->once())
      ->method('owner')
      ->with($owner);
    $item = new \Papaya\UI\Dialog\Field\Group\Buttons('Group Caption');
    $item->buttons($buttons);
    $this->assertSame(
      $collection, $item->collection($collection)
    );
    $this->assertEquals(
      $papaya, $item->papaya()
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
