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

class PapayaUiListviewItemNodeTest extends PapayaTestCase {

  /**
   * @covers \PapayaUiListviewItemNode
   */
  public function testConstructor() {
    /** @var \PHPUnit_Framework_MockObject_MockObject|\PapayaUiListviewItem $item */
    $item = $this
      ->getMockBuilder(PapayaUiListviewItem::class)
      ->disableOriginalConstructor()
      ->getMock();
    $node = new \PapayaUiListviewItemNode($item);
    $this->assertSame($item, $node->item);
  }

  /**
   * @covers \PapayaUiListviewItemNode
   */
  public function testConstructorWithAllArguments() {
    /** @var \PHPUnit_Framework_MockObject_MockObject|\PapayaUiListviewItem $item */
    $item = $this
      ->getMockBuilder(PapayaUiListviewItem::class)
      ->disableOriginalConstructor()
      ->getMock();
    $node = new \PapayaUiListviewItemNode($item, \PapayaUiListviewItemNode::NODE_EMPTY);
    $this->assertEquals(PapayaUiListviewItemNode::NODE_EMPTY, $node->status);
  }

  /**
   * @covers \PapayaUiListviewItemNode::appendTo
   */
  public function testAppendToWithStatusHidden() {
    /** @var \PHPUnit_Framework_MockObject_MockObject|\PapayaUiListviewItem $item */
    $item = $this
      ->getMockBuilder(PapayaUiListviewItem::class)
      ->disableOriginalConstructor()
      ->getMock();
    $node = new \PapayaUiListviewItemNode($item);
    $this->assertEquals('', $node->getXml());
  }

  /**
   * @covers \PapayaUiListviewItemNode::appendTo
   */
  public function testAppendToWithStatusEmpty() {
    /** @var \PHPUnit_Framework_MockObject_MockObject|\PapayaUiListviewItem $item */
    $item = $this
      ->getMockBuilder(PapayaUiListviewItem::class)
      ->disableOriginalConstructor()
      ->getMock();
    $node = new \PapayaUiListviewItemNode($item, \PapayaUiListviewItemNode::NODE_EMPTY);
    $this->assertEquals(
      /** @lang XML */'<node status="empty"/>',
      $node->getXml()
    );
  }

  /**
   * @covers \PapayaUiListviewItemNode::appendTo
   */
  public function testAppendToWithStatusClosed() {
    /** @var \PHPUnit_Framework_MockObject_MockObject|\PapayaUiListviewItem $item */
    $item = $this
      ->getMockBuilder(PapayaUiListviewItem::class)
      ->disableOriginalConstructor()
      ->getMock();
    $reference = $this->createMock(PapayaUiReference::class);
    $reference
      ->expects($this->once())
      ->method('__toString')
      ->will($this->returnValue('sample.html'));
    $node = new \PapayaUiListviewItemNode($item, \PapayaUiListviewItemNode::NODE_CLOSED);
    $node->reference($reference);
    $this->assertEquals(
    /** @lang XML */'<node status="closed" href="sample.html"/>',
      $node->getXml()
    );
  }

  /**
   * @covers \PapayaUiListviewItemNode::appendTo
   */
  public function testAppendToWithStatusOpen() {
    /** @var \PHPUnit_Framework_MockObject_MockObject|\PapayaUiListviewItem $item */
    $item = $this
      ->getMockBuilder(PapayaUiListviewItem::class)
      ->disableOriginalConstructor()
      ->getMock();
    $reference = $this->createMock(PapayaUiReference::class);
    $reference
      ->expects($this->once())
      ->method('__toString')
      ->will($this->returnValue('sample.html'));
    $node = new \PapayaUiListviewItemNode($item, \PapayaUiListviewItemNode::NODE_OPEN);
    $node->reference($reference);
    $this->assertEquals(
      /** @lang XML */'<node status="open" href="sample.html"/>',
      $node->getXml()
    );
  }

  /**
   * @covers \PapayaUiListviewItemNode::reference
   */
  public function testReferenceGetAfterSet() {
    /** @var \PHPUnit_Framework_MockObject_MockObject|\PapayaUiListviewItem $item */
    $item = $this
      ->getMockBuilder(PapayaUiListviewItem::class)
      ->disableOriginalConstructor()
      ->getMock();
    $node = new \PapayaUiListviewItemNode($item, \PapayaUiListviewItemNode::NODE_OPEN);
    $node->reference($reference = $this->createMock(PapayaUiReference::class));
    $this->assertSame($reference, $node->reference);
  }

  /**
   * @covers \PapayaUiListviewItemNode::reference
   */
  public function testReferenceGetClonedFromItem() {
    /** @var \PHPUnit_Framework_MockObject_MockObject|\PapayaUiListviewItem $item */
    $item = $this
      ->getMockBuilder(PapayaUiListviewItem::class)
      ->disableOriginalConstructor()
      ->getMock();
    $item
      ->expects($this->once())
      ->method('reference')
      ->will($this->returnValue($this->createMock(PapayaUiReference::class)));
    $node = new \PapayaUiListviewItemNode($item);
    $this->assertInstanceOf(PapayaUiReference::class, $node->reference);
  }
}
