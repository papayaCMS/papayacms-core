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

namespace Papaya\Template\Simple\Visitor;
require_once __DIR__.'/../../../../../bootstrap.php';

class OutputTest extends \PapayaTestCase {

  /**
   * @covers \Papaya\Template\Simple\Visitor\Output::clear
   */
  public function testClear() {
    $visitor = new Output();
    $nodes = new \Papaya\Template\Simple\AST\Nodes(
      array(
        new \Papaya\Template\Simple\AST\Node\Output('Hello')
      )
    );
    $nodes->accept($visitor);
    $visitor->clear();
    $this->assertEquals('', (string)$visitor);

  }

  /**
   * @covers \Papaya\Template\Simple\Visitor\Output::visitNodeOutput
   * @covers \Papaya\Template\Simple\Visitor\Output::__toString
   */
  public function testVisitWithOutput() {
    $visitor = new Output();
    $nodes = new \Papaya\Template\Simple\AST\Nodes(
      array(
        new \Papaya\Template\Simple\AST\Node\Output('Hello'),
        new \Papaya\Template\Simple\AST\Node\Output(' '),
        new \Papaya\Template\Simple\AST\Node\Output('World!')
      )
    );
    $nodes->accept($visitor);
    $this->assertEquals('Hello World!', (string)$visitor);
  }

  /**
   * @covers \Papaya\Template\Simple\Visitor\Output::visitNodeValue
   * @covers \Papaya\Template\Simple\Visitor\Output::__toString
   */
  public function testVisitWithValue() {
    $callbacks = $this
      ->getMockBuilder(Output\Callbacks::class)
      ->disableOriginalConstructor()
      ->setMethods(array('onGetValue'))
      ->getMock();
    $callbacks
      ->expects($this->once())
      ->method('onGetValue')
      ->with('$FOO')
      ->will($this->returnValue('Universe'));
    $visitor = new Output();
    $visitor->callbacks($callbacks);

    $nodes = new \Papaya\Template\Simple\AST\Nodes(
      array(
        new \Papaya\Template\Simple\AST\Node\Output('Hello'),
        new \Papaya\Template\Simple\AST\Node\Output(' '),
        new \Papaya\Template\Simple\AST\Node\Value('$FOO', 'World'),
        new \Papaya\Template\Simple\AST\Node\Output('!')
      )
    );
    $nodes->accept($visitor);
    $this->assertEquals('Hello Universe!', (string)$visitor);
  }

  /**
   * @covers \Papaya\Template\Simple\Visitor\Output::visitNodeValue
   * @covers \Papaya\Template\Simple\Visitor\Output::__toString
   */
  public function testVisitWithValueMappingReturnsNull() {
    $callbacks = $this
      ->getMockBuilder(Output\Callbacks::class)
      ->disableOriginalConstructor()
      ->setMethods(array('onGetValue'))
      ->getMock();
    $callbacks
      ->expects($this->once())
      ->method('onGetValue')
      ->with('$FOO')
      ->will($this->returnValue(NULL));
    $visitor = new Output();
    $visitor->callbacks($callbacks);

    $nodes = new \Papaya\Template\Simple\AST\Nodes(
      array(
        new \Papaya\Template\Simple\AST\Node\Output('Hello'),
        new \Papaya\Template\Simple\AST\Node\Output(' '),
        new \Papaya\Template\Simple\AST\Node\Value('$FOO', 'World'),
        new \Papaya\Template\Simple\AST\Node\Output('!')
      )
    );
    $nodes->accept($visitor);
    $this->assertEquals('Hello World!', (string)$visitor);
  }

  /**
   * @covers \Papaya\Template\Simple\Visitor\Output::callbacks
   */
  public function testCallbacksGetAfterSet() {
    $callbacks = $this->createMock(Output\Callbacks::class);
    $visitor = new Output();
    $visitor->callbacks($callbacks);
    $this->assertSame($callbacks, $visitor->callbacks());
  }

  /**
   * @covers \Papaya\Template\Simple\Visitor\Output::callbacks
   */
  public function testCallbacksGetImplicitCreate() {
    $visitor = new Output();
    $this->assertInstanceOf(Output\Callbacks::class, $visitor->callbacks());
  }
}
