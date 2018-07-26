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

require_once __DIR__.'/../../../../bootstrap.php';

class PapayaTemplateSimpleVisitorTest extends \PapayaTestCase {

  /**
   * covers \PapayaTemplateSimpleVisitor::visit
   * covers \PapayaTemplateSimpleVisitor::getMethodName
   */
  public function testVisitCallsMappedFunction() {
    $visitor = new \PapayaTemplateSimpleVisitor_TestProxy();
    $node = new \PapayaTemplateSimpleAstNodeOutput('foo');
    $visitor->visit($node);
    $this->assertSame($node, $visitor->visited);
  }

  /**
   * covers \PapayaTemplateSimpleVisitor::visit
   * covers \PapayaTemplateSimpleVisitor::getMethodName
   */
  public function testVisitIgnoresUnknownFunction() {
    $visitor = new \PapayaTemplateSimpleVisitor_TestProxy();
    /** @var \PHPUnit_Framework_MockObject_MockObject|\PapayaTemplateSimpleAstNode $node */
    $node = $this->createMock(\PapayaTemplateSimpleAstNode::class);
    $visitor->visit($node);
    $this->assertNull($visitor->visited);
  }

  /**
   * covers \PapayaTemplateSimpleVisitor::getMethodName
   */
  public function testVisitWithFullClassNameMappedToFunction() {
    $visitor = new \PapayaTemplateSimpleVisitor_TestProxy();
    /** @var \PHPUnit_Framework_MockObject_MockObject|\PapayaTemplateSimpleAstNode $node */
    $node = $this
      ->getMockBuilder(\PapayaTemplateSimpleAstNode::class)
      ->setMockClassName('TestClass_PapayaTemplateSimpleAstNode')
      ->getMock();
    $visitor->visit($node);
    $this->assertSame($node, $visitor->visited);
  }

  /**
   * covers \PapayaTemplateSimpleVisitor::enter
   * covers \PapayaTemplateSimpleVisitor::getMethodName
   */
  public function testEnterCallsMappedFunction() {
    $visitor = new \PapayaTemplateSimpleVisitor_TestProxy();
    $node = new \PapayaTemplateSimpleAstNodeOutput('foo');
    $visitor->enter($node);
    $this->assertSame($node, $visitor->entered);
  }

  /**
   * covers \PapayaTemplateSimpleVisitor::leave
   * covers \PapayaTemplateSimpleVisitor::getMethodName
   */
  public function testLeaveCallsMappedFunction() {
    $visitor = new \PapayaTemplateSimpleVisitor_TestProxy();
    $node = new \PapayaTemplateSimpleAstNodeOutput('foo');
    $visitor->leave($node);
    $this->assertSame($node, $visitor->leaved);
  }
}

class PapayaTemplateSimpleVisitor_TestProxy extends \PapayaTemplateSimpleVisitor {

  public $visited;
  public $entered;
  public $leaved;

  public function clear() {
  }

  public function __toString() {
    return '';
  }

  public function visitNodeOutput(\PapayaTemplateSimpleAstNodeOutput $node) {
    $this->visited = $node;
  }

  public function visitTestClass_PapayaTemplateSimpleAstNode(\PapayaTemplateSimpleAst $node) {
    $this->visited = $node;
  }

  public function enterNodeOutput(\PapayaTemplateSimpleAstNodeOutput $node) {
    $this->entered = $node;
  }

  public function leaveNodeOutput(\PapayaTemplateSimpleAstNodeOutput $node) {
    $this->leaved = $node;
  }
}
