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

namespace Papaya\Template\Simple\AST {

  require_once __DIR__.'/../../../../../bootstrap.php';

  class NodeTest extends \PapayaTestCase {

    /**
     * @covers \Papaya\Template\Simple\AST\Node::__get
     */
    public function testReadProperty() {
      $node = new Node_TestProxy();
      $this->assertEquals('bar', $node->foo);
    }

    /**
     * @covers \Papaya\Template\Simple\AST\Node::__get
     */
    public function testPropertyReadUnknownPropertyExpectingException() {
      $node = new Node_TestProxy();
      $this->expectException(\LogicException::class);
      $this->expectExceptionMessage('Unknown property: Papaya\Template\Simple\AST\Node_TestProxy::$UNKNOWN');
      /** @noinspection PhpUndefinedFieldInspection */
      $node->UNKNOWN;
    }

    /**
     * @covers \Papaya\Template\Simple\AST\Node::__set
     */
    public function testPropertyWriteThrowsException() {
      $node = new Node_TestProxy();
      $this->expectException(\LogicException::class);
      $node->foo = 23;
    }

    /**
     * @covers \Papaya\Template\Simple\AST\Node::accept
     */
    public function testAccept() {
      $node = new Node_TestProxy();

      /** @var \PHPUnit_Framework_MockObject_MockObject|\Papaya\Template\Simple\Visitor $visitor */
      $visitor = $this->createMock(\Papaya\Template\Simple\Visitor::class);
      $visitor
        ->expects($this->once())
        ->method('visit')
        ->with($node);

      $node->accept($visitor);
    }
  }

  /**
   * @property mixed foo
   */
  class Node_TestProxy extends Node {

    protected $_foo = 'bar';
  }
}
