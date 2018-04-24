<?php
require_once __DIR__.'/../../../../../bootstrap.php';

class PapayaTemplateSimpleAstNodeTest extends PapayaTestCase {

  /**
   * @covers PapayaTemplateSimpleAstNode::__get
   */
  public function testReadProperty() {
    $node = new PapayaTemplateSimpleAstNode_TestProxy();
    $this->assertEquals('bar', $node->foo);
  }

  /**
   * @covers PapayaTemplateSimpleAstNode::__get
   */
  public function testPropertyReadUnkownPropertyExpectingException() {
    $node = new PapayaTemplateSimpleAstNode_TestProxy();
    $this->setExpectedException(
      'LogicException', 'Unknown property: PapayaTemplateSimpleAstNode_TestProxy::$UNKNOWN'
    );
    $node->UNKNOWN;
  }

  /**
   * @covers PapayaTemplateSimpleAstNode::__set
   */
  public function testPropertyWriteThrowsException() {
    $node = new PapayaTemplateSimpleAstNode_TestProxy();
    $this->setExpectedException('LogicException');
    $node->foo = 23;
  }

  /**
   * @covers PapayaTemplateSimpleAstNode::accept
   */
  public function testAccept() {
    $node = new PapayaTemplateSimpleAstNode_TestProxy();

    $visitor = $this->getMock('PapayaTemplateSimpleVisitor');
    $visitor
      ->expects($this->once())
      ->method('visit')
      ->with($node);

    $node->accept($visitor);
  }
}

class PapayaTemplateSimpleAstNode_TestProxy extends PapayaTemplateSimpleAstNode {

  protected $_foo = 'bar';
}
