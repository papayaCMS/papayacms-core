<?php
require_once(dirname(__FILE__).'/../../../../../bootstrap.php');

class PapayaTemplateSimpleAstNodesTest extends PapayaTestCase {

  /**
   * @covers PapayaTemplateSimpleAstNodes::__construct
   */
  public function testLimitIsInitializedAndAllowsAdd() {
    $nodes = new PapayaTemplateSimpleAstNodes();
    $nodes[] = $node = $this->getMock('PapayaTemplateSimpleAstNode');
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
        $this->getMock('PapayaTemplateSimpleAstNode'),
        $this->getMock('PapayaTemplateSimpleAstNode')
      )
    );
    $this->assertCount(2, $nodes);
  }

  /**
   * @covers PapayaTemplateSimpleAstNodes::accept
   */
  public function testVisitorIsSentToEachChild() {
    $visitor = $this->getMock('PapayaTemplateSimpleVisitor');
    $nodeOne = $this->getMock('PapayaTemplateSimpleAstNode');
    $nodeOne
      ->expects($this->once())
      ->method('accept')
      ->with($visitor);
    $nodeTwo = $this->getMock('PapayaTemplateSimpleAstNode');
    $nodeTwo
      ->expects($this->once())
      ->method('accept')
      ->with($visitor);
    $nodes = new PapayaTemplateSimpleAstNodes(array($nodeOne, $nodeTwo));
    $nodes->accept($visitor);
  }

}