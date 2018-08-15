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

class PapayaTemplateSimpleAstNodesTest extends \PapayaTestCase {

  /**
   * @covers \Papaya\Template\Simple\AST\Nodes::__construct
   */
  public function testLimitIsInitializedAndAllowsAdd() {
    $nodes = new \Papaya\Template\Simple\AST\Nodes();
    $nodes[] = $node = $this->createMock(\Papaya\Template\Simple\AST\Node::class);
    $this->assertSame($node, $nodes[0]);
  }

  /**
   * @covers \Papaya\Template\Simple\AST\Nodes::__construct
   */
  public function testLimitIsInitializedAndRestrictsAddExpectingException() {
    $nodes = new \Papaya\Template\Simple\AST\Nodes();
    $this->expectException(InvalidArgumentException::class);
    $nodes[] = new \stdClass;
  }

  /**
   * @covers \Papaya\Template\Simple\AST\Nodes::__construct
   */
  public function testConstructorWithNodes() {
    $nodes = new \Papaya\Template\Simple\AST\Nodes(
      array(
        $this->createMock(\Papaya\Template\Simple\AST\Node::class),
        $this->createMock(\Papaya\Template\Simple\AST\Node::class)
      )
    );
    $this->assertCount(2, $nodes);
  }

  /**
   * @covers \Papaya\Template\Simple\AST\Nodes::accept
   */
  public function testVisitorIsSentToEachChild() {
    /** @var \PHPUnit_Framework_MockObject_MockObject|\Papaya\Template\Simple\Visitor $visitor */
    $visitor = $this->createMock(\Papaya\Template\Simple\Visitor::class);
    $nodeOne = $this->createMock(\Papaya\Template\Simple\AST\Node::class);
    $nodeOne
      ->expects($this->once())
      ->method('accept')
      ->with($visitor);
    $nodeTwo = $this->createMock(\Papaya\Template\Simple\AST\Node::class);
    $nodeTwo
      ->expects($this->once())
      ->method('accept')
      ->with($visitor);
    $nodes = new \Papaya\Template\Simple\AST\Nodes(array($nodeOne, $nodeTwo));
    $nodes->accept($visitor);
  }

}
