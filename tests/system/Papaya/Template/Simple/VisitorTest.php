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

namespace Papaya\Template\Simple {

  require_once __DIR__.'/../../../../bootstrap.php';

  class VisitorTest extends \Papaya\TestFramework\TestCase {

    /**
     * @covers \Papaya\Template\Simple\Visitor::visit
     * @covers \Papaya\Template\Simple\Visitor::getMethodName
     */
    public function testVisitCallsMappedFunction() {
      $visitor = new Visitor_TestProxy();
      $node = new AST\Node\Output('foo');
      $visitor->visit($node);
      $this->assertSame($node, $visitor->visited);
    }

    /**
     * @covers \Papaya\Template\Simple\Visitor::visit
     * @covers \Papaya\Template\Simple\Visitor::getMethodName
     */
    public function testVisitIgnoresUnknownFunction() {
      $visitor = new Visitor_TestProxy();
      /** @var \PHPUnit_Framework_MockObject_MockObject|AST\Node $node */
      $node = $this->createMock(AST\Node::class);
      $visitor->visit($node);
      $this->assertNull($visitor->visited);
    }

    /**
     * @covers \Papaya\Template\Simple\Visitor::getMethodName
     */
    public function testVisitWithFullClassNameMappedToFunction() {
      $visitor = new Visitor_TestProxy();
      /** @var \PHPUnit_Framework_MockObject_MockObject|AST\Node $node */
      $node = $this
        ->getMockBuilder(AST\Node::class)
        ->setMockClassName('TestClass_PapayaTemplateSimpleAstNode')
        ->getMock();
      $visitor->visit($node);
      $this->assertSame($node, $visitor->visited);
    }

    /**
     * @covers \Papaya\Template\Simple\Visitor::enter
     * @covers \Papaya\Template\Simple\Visitor::getMethodName
     */
    public function testEnterCallsMappedFunction() {
      $visitor = new Visitor_TestProxy();
      $node = new AST\Node\Output('foo');
      $visitor->enter($node);
      $this->assertSame($node, $visitor->entered);
    }

    /**
     * @covers \Papaya\Template\Simple\Visitor::leave
     * @covers \Papaya\Template\Simple\Visitor::getMethodName
     */
    public function testLeaveCallsMappedFunction() {
      $visitor = new Visitor_TestProxy();
      $node = new AST\Node\Output('foo');
      $visitor->leave($node);
      $this->assertSame($node, $visitor->leaved);
    }
  }

  class Visitor_TestProxy extends Visitor {

    public $visited;
    public $entered;
    public $leaved;

    public function clear() {
    }

    public function __toString() {
      return '';
    }

    public function visitNodeOutput(AST\Node\Output $node) {
      $this->visited = $node;
    }

    public function visitTestClass_PapayaTemplateSimpleAstNode(AST $node) {
      $this->visited = $node;
    }

    public function enterNodeOutput(AST\Node\Output $node) {
      $this->entered = $node;
    }

    public function leaveNodeOutput(AST\Node\Output $node) {
      $this->leaved = $node;
    }
  }
}
