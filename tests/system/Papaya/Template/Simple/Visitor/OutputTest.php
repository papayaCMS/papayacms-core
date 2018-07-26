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

class PapayaTemplateSimpleVisitorOutputTest extends \PapayaTestCase {

  /**
   * @covers \PapayaTemplateSimpleVisitorOutput::clear
   */
  public function testClear() {
    $visitor = new \PapayaTemplateSimpleVisitorOutput();
    $nodes = new \PapayaTemplateSimpleAstNodes(
      array(
        new \PapayaTemplateSimpleAstNodeOutput('Hello')
      )
    );
    $nodes->accept($visitor);
    $visitor->clear();
    $this->assertEquals('', (string)$visitor);

  }

  /**
   * @covers \PapayaTemplateSimpleVisitorOutput::visitNodeOutput
   * @covers \PapayaTemplateSimpleVisitorOutput::__toString
   */
  public function testVisitWithOutput() {
    $visitor = new \PapayaTemplateSimpleVisitorOutput();
    $nodes = new \PapayaTemplateSimpleAstNodes(
      array(
        new \PapayaTemplateSimpleAstNodeOutput('Hello'),
        new \PapayaTemplateSimpleAstNodeOutput(' '),
        new \PapayaTemplateSimpleAstNodeOutput('World!')
      )
    );
    $nodes->accept($visitor);
    $this->assertEquals('Hello World!', (string)$visitor);
  }

  /**
   * @covers \PapayaTemplateSimpleVisitorOutput::visitNodeValue
   * @covers \PapayaTemplateSimpleVisitorOutput::__toString
   */
  public function testVisitWithValue() {
    $callbacks = $this
      ->getMockBuilder(\PapayaTemplateSimpleVisitorOutputCallbacks::class)
      ->disableOriginalConstructor()
      ->setMethods(array('onGetValue'))
      ->getMock();
    $callbacks
      ->expects($this->once())
      ->method('onGetValue')
      ->with('$FOO')
      ->will($this->returnValue('Universe'));
    $visitor = new \PapayaTemplateSimpleVisitorOutput();
    $visitor->callbacks($callbacks);

    $nodes = new \PapayaTemplateSimpleAstNodes(
      array(
        new \PapayaTemplateSimpleAstNodeOutput('Hello'),
        new \PapayaTemplateSimpleAstNodeOutput(' '),
        new \PapayaTemplateSimpleAstNodeValue('$FOO', 'World'),
        new \PapayaTemplateSimpleAstNodeOutput('!')
      )
    );
    $nodes->accept($visitor);
    $this->assertEquals('Hello Universe!', (string)$visitor);
  }

  /**
   * @covers \PapayaTemplateSimpleVisitorOutput::visitNodeValue
   * @covers \PapayaTemplateSimpleVisitorOutput::__toString
   */
  public function testVisitWithValueMappingReturnsNull() {
    $callbacks = $this
      ->getMockBuilder(\PapayaTemplateSimpleVisitorOutputCallbacks::class)
      ->disableOriginalConstructor()
      ->setMethods(array('onGetValue'))
      ->getMock();
    $callbacks
      ->expects($this->once())
      ->method('onGetValue')
      ->with('$FOO')
      ->will($this->returnValue(NULL));
    $visitor = new \PapayaTemplateSimpleVisitorOutput();
    $visitor->callbacks($callbacks);

    $nodes = new \PapayaTemplateSimpleAstNodes(
      array(
        new \PapayaTemplateSimpleAstNodeOutput('Hello'),
        new \PapayaTemplateSimpleAstNodeOutput(' '),
        new \PapayaTemplateSimpleAstNodeValue('$FOO', 'World'),
        new \PapayaTemplateSimpleAstNodeOutput('!')
      )
    );
    $nodes->accept($visitor);
    $this->assertEquals('Hello World!', (string)$visitor);
  }

  /**
   * @covers \PapayaTemplateSimpleVisitorOutput::callbacks
   */
  public function testCallbacksGetAfterSet() {
    $callbacks = $this->createMock(\PapayaTemplateSimpleVisitorOutputCallbacks::class);
    $visitor = new \PapayaTemplateSimpleVisitorOutput();
    $visitor->callbacks($callbacks);
    $this->assertSame($callbacks, $visitor->callbacks());
  }

  /**
   * @covers \PapayaTemplateSimpleVisitorOutput::callbacks
   */
  public function testCallbacksGetImplicitCreate() {
    $visitor = new \PapayaTemplateSimpleVisitorOutput();
    $this->assertInstanceOf(\PapayaTemplateSimpleVisitorOutputCallbacks::class, $visitor->callbacks());
  }
}
