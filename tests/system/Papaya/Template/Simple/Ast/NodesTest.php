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

namespace Papaya\Template\Simple\AST;
require_once __DIR__.'/../../../../../bootstrap.php';

class NodesTest extends \Papaya\TestCase {

  /**
   * @covers \Papaya\Template\Simple\AST\Nodes::__construct
   */
  public function testLimitIsInitializedAndAllowsAdd() {
    $nodes = new Nodes();
    $nodes[] = $node = $this->createMock(Node::class);
    $this->assertSame($node, $nodes[0]);
  }

  /**
   * @covers \Papaya\Template\Simple\AST\Nodes::__construct
   */
  public function testLimitIsInitializedAndRestrictsAddExpectingException() {
    $nodes = new Nodes();
    $this->expectException(\InvalidArgumentException::class);
    $nodes[] = new \stdClass;
  }

  /**
   * @covers \Papaya\Template\Simple\AST\Nodes::__construct
   */
  public function testConstructorWithNodes() {
    $nodes = new Nodes(
      array(
        $this->createMock(Node::class),
        $this->createMock(Node::class)
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
    $nodeOne = $this->createMock(Node::class);
    $nodeOne
      ->expects($this->once())
      ->method('accept')
      ->with($visitor);
    $nodeTwo = $this->createMock(Node::class);
    $nodeTwo
      ->expects($this->once())
      ->method('accept')
      ->with($visitor);
    $nodes = new Nodes(array($nodeOne, $nodeTwo));
    $nodes->accept($visitor);
  }

}
