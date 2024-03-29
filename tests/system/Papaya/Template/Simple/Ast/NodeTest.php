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

  use Papaya\TestFramework\TestCase;

  require_once __DIR__.'/../../../../../bootstrap.php';

  /**
   * @covers \Papaya\Template\Simple\AST\Node
   */
  class NodeTest extends TestCase {

    public function testReadProperty() {
      $node = new Node_TestProxy();
      $this->assertTrue(isset($node->foo));
      $this->assertEquals('bar', $node->foo);
    }

    public function testPropertyReadUnknownPropertyExpectingException() {
      $node = new Node_TestProxy();
      $this->assertFalse(isset($node->UNKNOWN));
      $this->expectException(\LogicException::class);
      $this->expectExceptionMessage('Unknown property: Papaya\Template\Simple\AST\Node_TestProxy::$UNKNOWN');
      /** @noinspection PhpUndefinedFieldInspection */
      $node->UNKNOWN;
    }

    public function testPropertyWriteThrowsException() {
      $node = new Node_TestProxy();
      $this->expectException(\LogicException::class);
      $node->foo = 23;
    }

    public function testPropertyUnsetThrowsException() {
      $node = new Node_TestProxy();
      $this->expectException(\LogicException::class);
      unset($node->foo);
    }

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
