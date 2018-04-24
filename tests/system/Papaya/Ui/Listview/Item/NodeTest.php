<?php
require_once __DIR__.'/../../../../../bootstrap.php';

class PapayaUiListviewItemNodeTest extends PapayaTestCase {

  /**
   * @covers PapayaUiListviewItemNode
   */
  public function testConstructor() {
    $item = $this
      ->getMockBuilder('PapayaUiListviewItem')
      ->disableOriginalConstructor()
      ->getMock();
    $node = new PapayaUiListviewItemNode($item);
    $this->assertSame($item, $node->item);
  }

  /**
   * @covers PapayaUiListviewItemNode
   */
  public function testConstructorWithAllArguments() {
    $item = $this
      ->getMockBuilder('PapayaUiListviewItem')
      ->disableOriginalConstructor()
      ->getMock();
    $node = new PapayaUiListviewItemNode($item, PapayaUiListviewItemNode::NODE_EMPTY);
    $this->assertEquals(PapayaUiListviewItemNode::NODE_EMPTY, $node->status);
  }

  /**
   * @covers PapayaUiListviewItemNode::appendTo
   */
  public function testAppendToWithStatusHidden() {
    $item = $this
      ->getMockBuilder('PapayaUiListviewItem')
      ->disableOriginalConstructor()
      ->getMock();
    $node = new PapayaUiListviewItemNode($item);
    $this->assertEquals('', $node->getXml());
  }

  /**
   * @covers PapayaUiListviewItemNode::appendTo
   */
  public function testAppendToWithStatusEmpty() {
    $item = $this
      ->getMockBuilder('PapayaUiListviewItem')
      ->disableOriginalConstructor()
      ->getMock();
    $node = new PapayaUiListviewItemNode($item, PapayaUiListviewItemNode::NODE_EMPTY);
    $this->assertEquals(
      '<node status="empty"/>',
      $node->getXml()
    );
  }

  /**
   * @covers PapayaUiListviewItemNode::appendTo
   */
  public function testAppendToWithStatusClosed() {
    $item = $this
      ->getMockBuilder('PapayaUiListviewItem')
      ->disableOriginalConstructor()
      ->getMock();
    $reference = $this->getMock('PapayaUiReference');
    $reference
      ->expects($this->once())
      ->method('__toString')
      ->will($this->returnValue('sample.html'));
    $node = new PapayaUiListviewItemNode($item, PapayaUiListviewItemNode::NODE_CLOSED);
    $node->reference($reference);
    $this->assertEquals(
      '<node status="closed" href="sample.html"/>',
      $node->getXml()
    );
  }

  /**
   * @covers PapayaUiListviewItemNode::appendTo
   */
  public function testAppendToWithStatusOpen() {
    $item = $this
      ->getMockBuilder('PapayaUiListviewItem')
      ->disableOriginalConstructor()
      ->getMock();
    $reference = $this->getMock('PapayaUiReference');
    $reference
      ->expects($this->once())
      ->method('__toString')
      ->will($this->returnValue('sample.html'));
    $node = new PapayaUiListviewItemNode($item, PapayaUiListviewItemNode::NODE_OPEN);
    $node->reference($reference);
    $this->assertEquals(
      '<node status="open" href="sample.html"/>',
      $node->getXml()
    );
  }

  /**
   * @covers PapayaUiListviewItemNode::reference
   */
  public function testReferenceGetAfterSet() {
    $item = $this
      ->getMockBuilder('PapayaUiListviewItem')
      ->disableOriginalConstructor()
      ->getMock();
    $node = new PapayaUiListviewItemNode($item, PapayaUiListviewItemNode::NODE_OPEN);
    $node->reference($reference = $this->getMock('PapayaUiReference'));
    $this->assertSame($reference, $node->reference);
  }

  /**
   * @covers PapayaUiListviewItemNode::reference
   */
  public function testReferenceGetClonedFromItem() {
    $item = $this
      ->getMockBuilder('PapayaUiListviewItem')
      ->disableOriginalConstructor()
      ->getMock();
    $item
      ->expects($this->once())
      ->method('reference')
      ->will($this->returnValue($this->getMock('PapayaUiReference')));
    $node = new PapayaUiListviewItemNode($item);
    $this->assertInstanceOf('PapayaUiReference', $node->reference);
  }
}
