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

namespace Papaya\UI\Listview\Item;
require_once __DIR__.'/../../../../../bootstrap.php';

class NodeTest extends \Papaya\TestCase {

  /**
   * @covers \Papaya\UI\Listview\Item\Node
   */
  public function testConstructor() {
    /** @var \PHPUnit_Framework_MockObject_MockObject|\Papaya\UI\Listview\Item $item */
    $item = $this
      ->getMockBuilder(\Papaya\UI\Listview\Item::class)
      ->disableOriginalConstructor()
      ->getMock();
    $node = new Node($item);
    $this->assertSame($item, $node->item);
  }

  /**
   * @covers \Papaya\UI\Listview\Item\Node
   */
  public function testConstructorWithAllArguments() {
    /** @var \PHPUnit_Framework_MockObject_MockObject|\Papaya\UI\Listview\Item $item */
    $item = $this
      ->getMockBuilder(\Papaya\UI\Listview\Item::class)
      ->disableOriginalConstructor()
      ->getMock();
    $node = new Node($item, Node::NODE_EMPTY);
    $this->assertEquals(Node::NODE_EMPTY, $node->status);
  }

  /**
   * @covers \Papaya\UI\Listview\Item\Node::appendTo
   */
  public function testAppendToWithStatusHidden() {
    /** @var \PHPUnit_Framework_MockObject_MockObject|\Papaya\UI\Listview\Item $item */
    $item = $this
      ->getMockBuilder(\Papaya\UI\Listview\Item::class)
      ->disableOriginalConstructor()
      ->getMock();
    $node = new Node($item);
    $this->assertEquals('', $node->getXML());
  }

  /**
   * @covers \Papaya\UI\Listview\Item\Node::appendTo
   */
  public function testAppendToWithStatusEmpty() {
    /** @var \PHPUnit_Framework_MockObject_MockObject|\Papaya\UI\Listview\Item $item */
    $item = $this
      ->getMockBuilder(\Papaya\UI\Listview\Item::class)
      ->disableOriginalConstructor()
      ->getMock();
    $node = new Node($item, Node::NODE_EMPTY);
    $this->assertEquals(
    /** @lang XML */
      '<node status="empty"/>',
      $node->getXML()
    );
  }

  /**
   * @covers \Papaya\UI\Listview\Item\Node::appendTo
   */
  public function testAppendToWithStatusClosed() {
    /** @var \PHPUnit_Framework_MockObject_MockObject|\Papaya\UI\Listview\Item $item */
    $item = $this
      ->getMockBuilder(\Papaya\UI\Listview\Item::class)
      ->disableOriginalConstructor()
      ->getMock();
    $reference = $this->createMock(\Papaya\UI\Reference::class);
    $reference
      ->expects($this->once())
      ->method('__toString')
      ->will($this->returnValue('sample.html'));
    $node = new Node($item, Node::NODE_CLOSED);
    $node->reference($reference);
    $this->assertEquals(
    /** @lang XML */
      '<node status="closed" href="sample.html"/>',
      $node->getXML()
    );
  }

  /**
   * @covers \Papaya\UI\Listview\Item\Node::appendTo
   */
  public function testAppendToWithStatusOpen() {
    /** @var \PHPUnit_Framework_MockObject_MockObject|\Papaya\UI\Listview\Item $item */
    $item = $this
      ->getMockBuilder(\Papaya\UI\Listview\Item::class)
      ->disableOriginalConstructor()
      ->getMock();
    $reference = $this->createMock(\Papaya\UI\Reference::class);
    $reference
      ->expects($this->once())
      ->method('__toString')
      ->will($this->returnValue('sample.html'));
    $node = new Node($item, Node::NODE_OPEN);
    $node->reference($reference);
    $this->assertEquals(
    /** @lang XML */
      '<node status="open" href="sample.html"/>',
      $node->getXML()
    );
  }

  /**
   * @covers \Papaya\UI\Listview\Item\Node::reference
   */
  public function testReferenceGetAfterSet() {
    /** @var \PHPUnit_Framework_MockObject_MockObject|\Papaya\UI\Listview\Item $item */
    $item = $this
      ->getMockBuilder(\Papaya\UI\Listview\Item::class)
      ->disableOriginalConstructor()
      ->getMock();
    $node = new Node($item, Node::NODE_OPEN);
    $node->reference($reference = $this->createMock(\Papaya\UI\Reference::class));
    $this->assertSame($reference, $node->reference);
  }

  /**
   * @covers \Papaya\UI\Listview\Item\Node::reference
   */
  public function testReferenceGetClonedFromItem() {
    /** @var \PHPUnit_Framework_MockObject_MockObject|\Papaya\UI\Listview\Item $item */
    $item = $this
      ->getMockBuilder(\Papaya\UI\Listview\Item::class)
      ->disableOriginalConstructor()
      ->getMock();
    $item
      ->expects($this->once())
      ->method('reference')
      ->will($this->returnValue($this->createMock(\Papaya\UI\Reference::class)));
    $node = new Node($item);
    $this->assertInstanceOf(\Papaya\UI\Reference::class, $node->reference);
  }
}
