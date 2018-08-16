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

namespace Papaya\Message\Context;
require_once __DIR__.'/../../../../bootstrap.php';

class VariableTest extends \PapayaTestCase {

  /**
   * @covers \Papaya\Message\Context\Variable::__construct
   */
  public function testConstructor() {
    $context = new Variable(42);
    $this->assertAttributeSame(
      42,
      '_variable',
      $context
    );
  }

  /**
   * @covers \Papaya\Message\Context\Variable::__construct
   * @covers \Papaya\Message\Context\Variable::setDepth
   */
  public function testConstructorWithDepth() {
    $context = new Variable(42, 21);
    $this->assertAttributeSame(
      21,
      '_depth',
      $context
    );
  }

  /**
   * @covers \Papaya\Message\Context\Variable::getDepth
   */
  public function testGetDepth() {
    $context = new Variable(42, 21);
    $this->assertSame(
      21,
      $context->getDepth()
    );
  }

  /**
   * @covers \Papaya\Message\Context\Variable::setDepth
   */
  public function testSetDepthWithInvalidDepthExpectingException() {
    $context = new Variable(NULL);
    $this->expectException(\InvalidArgumentException::class);
    $context->setDepth(0);
  }

  /**
   * @covers \Papaya\Message\Context\Variable::__construct
   * @covers \Papaya\Message\Context\Variable::setStringLength
   */
  public function testConstructorWithStringLength() {
    $context = new Variable(42, 21, 23);
    $this->assertAttributeSame(
      23,
      '_stringLength',
      $context
    );
  }

  /**
   * @covers \Papaya\Message\Context\Variable::getStringLength
   */
  public function testGetStringLength() {
    $context = new Variable(42, 21, 23);
    $this->assertSame(
      23,
      $context->getStringLength()
    );
  }

  /**
   * @covers \Papaya\Message\Context\Variable::setStringLength
   */
  public function testSetStringLengthWithInvalidLengthExpectingException() {
    $context = new Variable(NULL);
    $this->expectException(\InvalidArgumentException::class);
    $context->setStringLength(-1);
  }

  /**
   * @covers \Papaya\Message\Context\Variable::acceptVisitor
   */
  public function testAcceptVisitor() {
    /** @var \PHPUnit_Framework_MockObject_MockObject|\Papaya\Message\Context\Variable\Visitor\Text $visitor */
    $visitor = $this
      ->getMockBuilder(\Papaya\Message\Context\Variable\Visitor\Text::class)
      ->setConstructorArgs(array(21, 42))
      ->getMock();
    $visitor
      ->expects($this->once())
      ->method('visitVariable')
      ->with($this->equalTo('variable'));
    $context = new Variable('variable');
    $context->acceptVisitor($visitor);
  }

  /**
   * @covers \Papaya\Message\Context\Variable::asString
   */
  public function testAsString() {
    $context = new Variable(FALSE);
    $this->assertEquals(
      'bool(false)',
      $context->asString()
    );
  }

  /**
   * @covers \Papaya\Message\Context\Variable::asXhtml
   */
  public function testAsXhtml() {
    $context = new Variable(FALSE);
    $this->assertEquals(
      '<ul class="variableDump">'.
      '<li><strong>bool</strong>(<em class="boolean">false</em>)</li>'.
      '</ul>',
      $context->asXhtml()
    );
  }
}
