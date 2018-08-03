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

class PapayaUiListviewItemNodeTest extends \PapayaTestCase {

  /**
   * @covers \Papaya\Ui\Listview\Item\Node
   */
  public function testConstructor() {
    /** @var \PHPUnit_Framework_MockObject_MockObject|\Papaya\Ui\Listview\Item $item */
    $item = $this
      ->getMockBuilder(\Papaya\Ui\Listview\Item::class)
      ->disableOriginalConstructor()
      ->getMock();
    $node = new \Papaya\Ui\Listview\Item\Node($item);
    $this->assertSame($item, $node->item);
  }

  /**
   * @covers \Papaya\Ui\Listview\Item\Node
   */
  public function testConstructorWithAllArguments() {
    /** @var \PHPUnit_Framework_MockObject_MockObject|\Papaya\Ui\Listview\Item $item */
    $item = $this
      ->getMockBuilder(\Papaya\Ui\Listview\Item::class)
      ->disableOriginalConstructor()
      ->getMock();
    $node = new \Papaya\Ui\Listview\Item\Node($item, \Papaya\Ui\Listview\Item\Node::NODE_EMPTY);
    $this->assertEquals(\Papaya\Ui\Listview\Item\Node::NODE_EMPTY, $node->status);
  }

  /**
   * @covers \Papaya\Ui\Listview\Item\Node::appendTo
   */
  public function testAppendToWithStatusHidden() {
    /** @var \PHPUnit_Framework_MockObject_MockObject|\Papaya\Ui\Listview\Item $item */
    $item = $this
      ->getMockBuilder(\Papaya\Ui\Listview\Item::class)
      ->disableOriginalConstructor()
      ->getMock();
    $node = new \Papaya\Ui\Listview\Item\Node($item);
    $this->assertEquals('', $node->getXml());
  }

  /**
   * @covers \Papaya\Ui\Listview\Item\Node::appendTo
   */
  public function testAppendToWithStatusEmpty() {
    /** @var \PHPUnit_Framework_MockObject_MockObject|\Papaya\Ui\Listview\Item $item */
    $item = $this
      ->getMockBuilder(\Papaya\Ui\Listview\Item::class)
      ->disableOriginalConstructor()
      ->getMock();
    $node = new \Papaya\Ui\Listview\Item\Node($item, \Papaya\Ui\Listview\Item\Node::NODE_EMPTY);
    $this->assertEquals(
      /** @lang XML */'<node status="empty"/>',
      $node->getXml()
    );
  }

  /**
   * @covers \Papaya\Ui\Listview\Item\Node::appendTo
   */
  public function testAppendToWithStatusClosed() {
    /** @var \PHPUnit_Framework_MockObject_MockObject|\Papaya\Ui\Listview\Item $item */
    $item = $this
      ->getMockBuilder(\Papaya\Ui\Listview\Item::class)
      ->disableOriginalConstructor()
      ->getMock();
    $reference = $this->createMock(\PapayaUiReference::class);
    $reference
      ->expects($this->once())
      ->method('__toString')
      ->will($this->returnValue('sample.html'));
    $node = new \Papaya\Ui\Listview\Item\Node($item, \Papaya\Ui\Listview\Item\Node::NODE_CLOSED);
    $node->reference($reference);
    $this->assertEquals(
    /** @lang XML */'<node status="closed" href="sample.html"/>',
      $node->getXml()
    );
  }

  /**
   * @covers \Papaya\Ui\Listview\Item\Node::appendTo
   */
  public function testAppendToWithStatusOpen() {
    /** @var \PHPUnit_Framework_MockObject_MockObject|\Papaya\Ui\Listview\Item $item */
    $item = $this
      ->getMockBuilder(\Papaya\Ui\Listview\Item::class)
      ->disableOriginalConstructor()
      ->getMock();
    $reference = $this->createMock(\PapayaUiReference::class);
    $reference
      ->expects($this->once())
      ->method('__toString')
      ->will($this->returnValue('sample.html'));
    $node = new \Papaya\Ui\Listview\Item\Node($item, \Papaya\Ui\Listview\Item\Node::NODE_OPEN);
    $node->reference($reference);
    $this->assertEquals(
      /** @lang XML */'<node status="open" href="sample.html"/>',
      $node->getXml()
    );
  }

  /**
   * @covers \Papaya\Ui\Listview\Item\Node::reference
   */
  public function testReferenceGetAfterSet() {
    /** @var \PHPUnit_Framework_MockObject_MockObject|\Papaya\Ui\Listview\Item $item */
    $item = $this
      ->getMockBuilder(\Papaya\Ui\Listview\Item::class)
      ->disableOriginalConstructor()
      ->getMock();
    $node = new \Papaya\Ui\Listview\Item\Node($item, \Papaya\Ui\Listview\Item\Node::NODE_OPEN);
    $node->reference($reference = $this->createMock(\PapayaUiReference::class));
    $this->assertSame($reference, $node->reference);
  }

  /**
   * @covers \Papaya\Ui\Listview\Item\Node::reference
   */
  public function testReferenceGetClonedFromItem() {
    /** @var \PHPUnit_Framework_MockObject_MockObject|\Papaya\Ui\Listview\Item $item */
    $item = $this
      ->getMockBuilder(\Papaya\Ui\Listview\Item::class)
      ->disableOriginalConstructor()
      ->getMock();
    $item
      ->expects($this->once())
      ->method('reference')
      ->will($this->returnValue($this->createMock(\PapayaUiReference::class)));
    $node = new \Papaya\Ui\Listview\Item\Node($item);
    $this->assertInstanceOf(\PapayaUiReference::class, $node->reference);
  }
}
