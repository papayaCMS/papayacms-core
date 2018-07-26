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
   * @covers \PapayaTemplateSimpleAstNodes::__construct
   */
  public function testLimitIsInitializedAndAllowsAdd() {
    $nodes = new \PapayaTemplateSimpleAstNodes();
    $nodes[] = $node = $this->createMock(\PapayaTemplateSimpleAstNode::class);
    $this->assertSame($node, $nodes[0]);
  }

  /**
   * @covers \PapayaTemplateSimpleAstNodes::__construct
   */
  public function testLimitIsInitializedAndRestrictsAddExpectingException() {
    $nodes = new \PapayaTemplateSimpleAstNodes();
    $this->expectException(InvalidArgumentException::class);
    $nodes[] = new stdClass;
  }

  /**
   * @covers \PapayaTemplateSimpleAstNodes::__construct
   */
  public function testConstructorWithNodes() {
    $nodes = new \PapayaTemplateSimpleAstNodes(
      array(
        $this->createMock(\PapayaTemplateSimpleAstNode::class),
        $this->createMock(\PapayaTemplateSimpleAstNode::class)
      )
    );
    $this->assertCount(2, $nodes);
  }

  /**
   * @covers \PapayaTemplateSimpleAstNodes::accept
   */
  public function testVisitorIsSentToEachChild() {
    /** @var \PHPUnit_Framework_MockObject_MockObject|\PapayaTemplateSimpleVisitor $visitor */
    $visitor = $this->createMock(\PapayaTemplateSimpleVisitor::class);
    $nodeOne = $this->createMock(\PapayaTemplateSimpleAstNode::class);
    $nodeOne
      ->expects($this->once())
      ->method('accept')
      ->with($visitor);
    $nodeTwo = $this->createMock(\PapayaTemplateSimpleAstNode::class);
    $nodeTwo
      ->expects($this->once())
      ->method('accept')
      ->with($visitor);
    $nodes = new \PapayaTemplateSimpleAstNodes(array($nodeOne, $nodeTwo));
    $nodes->accept($visitor);
  }

}
