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

class BacktraceTest extends \Papaya\TestCase {

  /**
   * @covers \Papaya\Message\Context\Backtrace::__construct
   * @covers \Papaya\Message\Context\Backtrace::setOffset
   */
  public function testConstructorWithOffset() {
    $backtrace = new Backtrace(41);
    $this->assertAttributeEquals(
      42,
      '_offset',
      $backtrace
    );
  }

  /**
   * @covers \Papaya\Message\Context\Backtrace::__construct
   * @covers \Papaya\Message\Context\Backtrace::setOffset
   */
  public function testContructorWithOffsetAndTraceData() {
    $backtrace = new Backtrace(42, array());
    $this->assertAttributeEquals(
      42,
      '_offset',
      $backtrace
    );
  }

  /**
   * @covers \Papaya\Message\Context\Backtrace::__construct
   * @covers \Papaya\Message\Context\Backtrace::setOffset
   */
  public function testContructorWithoutOffset() {
    $backtrace = new Backtrace();
    $this->assertAttributeEquals(
      1,
      '_offset',
      $backtrace
    );
  }

  /**
   * @covers \Papaya\Message\Context\Backtrace::setOffset
   */
  public function testSetOffsetWithInvalidOffsetExpectingException() {
    $backtrace = new Backtrace();
    $this->expectException(\InvalidArgumentException::class);
    $backtrace->setOffset(-1);
  }

  /**
   * @covers \Papaya\Message\Context\Backtrace::setBacktrace
   */
  public function testSetBacktrace() {
    $backtrace = new Backtrace();
    $backtrace->setBacktrace(array(1), 42);
    $this->assertAttributeEquals(
      array(1),
      '_backtrace',
      $backtrace
    );
    $this->assertAttributeEquals(
      42,
      '_offset',
      $backtrace
    );
  }

  /**
   * @covers \Papaya\Message\Context\Backtrace::getBacktrace
   */
  public function testGetBacktrace() {
    $backtrace = new Backtrace();
    $backtrace->setBacktrace(array(1));
    $this->assertEquals(
      array(1),
      $backtrace->getBacktrace()
    );
  }

  /**
   * @covers \Papaya\Message\Context\Backtrace::getBacktrace
   */
  public function testGetBacktraceImplicitCreate() {
    $backtrace = new Backtrace();
    $this->assertIsArray(
      $backtrace->getBacktrace()
    );
  }

  /**
   * @covers \Papaya\Message\Context\Backtrace::asArray
   */
  public function testAsArray() {
    $backtrace = new Backtrace();
    $backtrace->setBacktrace(
      $this->getBacktraceFixture()
    );
    $this->assertEquals(
      array(
        'function() test.php:23',
        'testClass::staticFunction() testClass.php:21',
        'testClass->method() testClass.php:42'
      ),
      $backtrace->asArray()
    );
  }

  /**
   * @covers \Papaya\Message\Context\Backtrace::asArray
   */
  public function testAsArrayWithOffset() {
    $backtrace = new Backtrace();
    $backtrace->setBacktrace(
      $this->getBacktraceFixture(),
      2
    );
    $this->assertEquals(
      array(
        'testClass->method() testClass.php:42'
      ),
      $backtrace->asArray()
    );
  }

  /**
   * @covers \Papaya\Message\Context\Backtrace::asString
   */
  public function testAsString() {
    $backtrace = new Backtrace();
    $backtrace->setBacktrace(
      $this->getBacktraceFixture()
    );
    $this->assertEquals(
      'function() test.php:23'."\n".
      'testClass::staticFunction() testClass.php:21'."\n".
      'testClass->method() testClass.php:42',
      $backtrace->asString()
    );
  }

  /**
   * @covers \Papaya\Message\Context\Backtrace::asXhtml
   */
  public function testAsXhtml() {
    $backtrace = new Backtrace();
    $backtrace->setBacktrace(
      $this->getBacktraceFixture()
    );
    $this->assertEquals(
      'function() test.php:23'."<br />\n".
      'testClass::staticFunction() testClass.php:21'."<br />\n".
      'testClass-&gt;method() testClass.php:42',
      $backtrace->asXhtml()
    );
  }

  /**
   * @covers \Papaya\Message\Context\Backtrace::getLabel
   */
  public function testGetLabel() {
    $backtrace = new Backtrace();
    $this->assertEquals(
      'Backtrace',
      $backtrace->getLabel()
    );
  }

  public function getBacktraceFixture() {
    return array(
      array(
        'function' => 'function',
        'file' => 'test.php',
        'line' => 23
      ),
      array(
        'function' => 'staticFunction',
        'file' => 'testClass.php',
        'line' => 21,
        'class' => 'testClass',
        'type' => '::'
      ),
      array(
        'function' => 'method',
        'file' => 'testClass.php',
        'line' => 42,
        'class' => 'testClass',
        'type' => '->'
      )
    );
  }

}
