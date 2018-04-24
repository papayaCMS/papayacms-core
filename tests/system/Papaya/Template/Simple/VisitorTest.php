<?php
require_once __DIR__.'/../../../../bootstrap.php';

class PapayaTemplateSimpleVisitorTest extends PapayaTestCase {

  /**
   * covers PapayaTemplateSimpleVisitor::visit
   * covers PapayaTemplateSimpleVisitor::getMethodName
   */
  public function testVisitCallsMappedFunction() {
    $visitor = new PapayaTemplateSimpleVisitor_TestProxy();
    $node = new PapayaTemplateSimpleAstNodeOutput('foo');
    $visitor->visit($node);
    $this->assertSame($node, $visitor->visited);
  }

  /**
   * covers PapayaTemplateSimpleVisitor::visit
   * covers PapayaTemplateSimpleVisitor::getMethodName
   */
  public function testVisitIgnoresUnknownFunction() {
    $visitor = new PapayaTemplateSimpleVisitor_TestProxy();
    $node = $this->getMock('PapayaTemplateSimpleAstNode');
    $visitor->visit($node);
    $this->assertNull($visitor->visited);
  }

  /**
   * covers PapayaTemplateSimpleVisitor::getMethodName
   */
  public function testVisitWithFullClassnameMappedToFunction() {
    $visitor = new PapayaTemplateSimpleVisitor_TestProxy();
    $node = $this
      ->getMockBuilder('PapayaTemplateSimpleAstNode')
      ->setMockClassName('TestClass_PapayaTemplateSimpleAstNode')
      ->getMock();
    $visitor->visit($node);
    $this->assertSame($node, $visitor->visited);
  }

  /**
   * covers PapayaTemplateSimpleVisitor::enter
   * covers PapayaTemplateSimpleVisitor::getMethodName
   */
  public function testEnterCallsMappedFunction() {
    $visitor = new PapayaTemplateSimpleVisitor_TestProxy();
    $node = new PapayaTemplateSimpleAstNodeOutput('foo');
    $visitor->enter($node);
    $this->assertSame($node, $visitor->entered);
  }

  /**
   * covers PapayaTemplateSimpleVisitor::leave
   * covers PapayaTemplateSimpleVisitor::getMethodName
   */
  public function testLeaveCallsMappedFunction() {
    $visitor = new PapayaTemplateSimpleVisitor_TestProxy();
    $node = new PapayaTemplateSimpleAstNodeOutput('foo');
    $visitor->leave($node);
    $this->assertSame($node, $visitor->leaved);
  }
}

class PapayaTemplateSimpleVisitor_TestProxy extends PapayaTemplateSimpleVisitor {

  public $visited = NULL;
  public $entered = NULL;
  public $leaved = NULL;

  public function clear() {
  }

  public function visitNodeOutput(PapayaTemplateSimpleAstNodeOutput $node) {
    $this->visited = $node;
  }

  public function visitTestClass_PapayaTemplateSimpleAstNode(PapayaTemplateSimpleAst $node) {
    $this->visited = $node;
  }

  public function enterNodeOutput(PapayaTemplateSimpleAstNodeOutput $node) {
    $this->entered = $node;
  }

  public function leaveNodeOutput(PapayaTemplateSimpleAstNodeOutput $node) {
    $this->leaved = $node;
  }
}
