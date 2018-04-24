<?php
require_once __DIR__.'/../../../../../bootstrap.php';

class PapayaTemplateSimpleAstNodesTest extends PapayaTestCase {

  /**
   * @covers PapayaTemplateSimpleAstNodes::__construct
   */
  public function testLimitIsInitializedAndAllowsAdd() {
    $nodes = new PapayaTemplateSimpleAstNodes();
    $nodes[] = $node = $this->createMock(PapayaTemplateSimpleAstNode::class);
    $this->assertSame($node, $nodes[0]);
  }

  /**
   * @covers PapayaTemplateSimpleAstNodes::__construct
   */
  public function testLimitIsInitializedAndRestrictsAddExpectingException() {
    $nodes = new PapayaTemplateSimpleAstNodes();
    $this->setExpectedException('InvalidArgumentException');
    $nodes[] = new stdClass;
  }

  /**
   * @covers PapayaTemplateSimpleAstNodes::__construct
   */
  public function testConstructorWithNodes() {
    $nodes = new PapayaTemplateSimpleAstNodes(
      array(
        $this->createMock(PapayaTemplateSimpleAstNode::class),
        $this->createMock(PapayaTemplateSimpleAstNode::class)
      )
    );
    $this->assertCount(2, $nodes);
  }

  /**
   * @covers PapayaTemplateSimpleAstNodes::accept
   */
  public function testVisitorIsSentToEachChild() {
    $visitor = $this->createMock(PapayaTemplateSimpleVisitor::class);
    $nodeOne = $this->createMock(PapayaTemplateSimpleAstNode::class);
    $nodeOne
      ->expects($this->once())
      ->method('accept')
      ->with($visitor);
    $nodeTwo = $this->createMock(PapayaTemplateSimpleAstNode::class);
    $nodeTwo
      ->expects($this->once())
      ->method('accept')
      ->with($visitor);
    $nodes = new PapayaTemplateSimpleAstNodes(array($nodeOne, $nodeTwo));
    $nodes->accept($visitor);
  }

}
