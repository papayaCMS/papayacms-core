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

class PapayaMessageContextVariableTest extends PapayaTestCase {

  /**
  * @covers \PapayaMessageContextVariable::__construct
  */
  public function testConstructor() {
    $context = new \PapayaMessageContextVariable(42);
    $this->assertAttributeSame(
      42,
      '_variable',
      $context
    );
  }

  /**
  * @covers \PapayaMessageContextVariable::__construct
  * @covers \PapayaMessageContextVariable::setDepth
  */
  public function testConstructorWithDepth() {
    $context = new \PapayaMessageContextVariable(42, 21);
    $this->assertAttributeSame(
      21,
      '_depth',
      $context
    );
  }

  /**
  * @covers \PapayaMessageContextVariable::getDepth
  */
  public function testGetDepth() {
    $context = new \PapayaMessageContextVariable(42, 21);
    $this->assertSame(
      21,
      $context->getDepth()
    );
  }

  /**
  * @covers \PapayaMessageContextVariable::setDepth
  */
  public function testSetDepthWithInvalidDepthExpectingException() {
    $context = new \PapayaMessageContextVariable(NULL);
    $this->expectException(InvalidArgumentException::class);
    $context->setDepth(0);
  }

  /**
  * @covers \PapayaMessageContextVariable::__construct
  * @covers \PapayaMessageContextVariable::setStringLength
  */
  public function testConstructorWithStringLength() {
    $context = new \PapayaMessageContextVariable(42, 21, 23);
    $this->assertAttributeSame(
      23,
      '_stringLength',
      $context
    );
  }

  /**
  * @covers \PapayaMessageContextVariable::getStringLength
  */
  public function testGetStringLength() {
    $context = new \PapayaMessageContextVariable(42, 21, 23);
    $this->assertSame(
      23,
      $context->getStringLength()
    );
  }

  /**
  * @covers \PapayaMessageContextVariable::setStringLength
  */
  public function testSetStringLengthWithInvalidLengthExpectingException() {
    $context = new \PapayaMessageContextVariable(NULL);
    $this->expectException(InvalidArgumentException::class);
    $context->setStringLength(-1);
  }

  /**
  * @covers \PapayaMessageContextVariable::acceptVisitor
  */
  public function testAcceptVisitor() {
    /** @var \PHPUnit_Framework_MockObject_MockObject|\PapayaMessageContextVariableVisitorString $visitor */
    $visitor = $this
      ->getMockBuilder(PapayaMessageContextVariableVisitorString::class)
      ->setConstructorArgs(array(21, 42))
      ->getMock();
    $visitor
      ->expects($this->once())
      ->method('visitVariable')
      ->with($this->equalTo('variable'));
    $context = new \PapayaMessageContextVariable('variable');
    $context->acceptVisitor($visitor);
  }

  /**
  * @covers \PapayaMessageContextVariable::asString
  */
  public function testAsString() {
    $context = new \PapayaMessageContextVariable(FALSE);
    $this->assertEquals(
      'bool(false)',
      $context->asString()
    );
  }

  /**
  * @covers \PapayaMessageContextVariable::asXhtml
  */
  public function testAsXhtml() {
    $context = new \PapayaMessageContextVariable(FALSE);
    $this->assertEquals(
      '<ul class="variableDump">'.
      '<li><strong>bool</strong>(<em class="boolean">false</em>)</li>'.
      '</ul>',
      $context->asXhtml()
    );
  }
}
