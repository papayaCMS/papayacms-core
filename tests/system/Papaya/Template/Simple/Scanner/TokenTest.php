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

namespace Papaya\Template\Simple\Scanner;
require_once __DIR__.'/../../../../../bootstrap.php';

class TokenTest extends \PapayaTestCase {

  /**
   * @covers \Papaya\Template\Simple\Scanner\Token::__construct
   * @covers \Papaya\Template\Simple\Scanner\Token::__toString
   */
  public function testConstructorAndStringCasting() {
    $token = new Token(
      Token::TEXT,
      21,
      'foo'
    );
    $this->assertEquals(
      'TEXT@21: "foo"',
      (string)$token
    );
  }

  /**
   * @covers \Papaya\Template\Simple\Scanner\Token::__construct
   */
  public function testConstructorWithInvalidTypeExpectingException() {
    $this->expectException(\InvalidArgumentException::class);
    new Token(-23, 0, '');
  }

  /**
   * @covers \Papaya\Template\Simple\Scanner\Token::getTypeString
   * @covers \Papaya\Template\Simple\Scanner\Token::getTokenTypes
   */
  public function testGetTypeStringExpectingText() {
    $this->assertEquals(
      'TEXT',
      Token::getTypeString(Token::TEXT)
    );
  }

  /**
   * @covers \Papaya\Template\Simple\Scanner\Token::getTypeString
   */
  public function testGetTypeStringwithInvalidTokenTypeExpectingNull() {
    $this->assertNull(
      Token::getTypeString(-23)
    );
  }

  /**
   * @covers \Papaya\Template\Simple\Scanner\Token::__get
   */
  public function testPropertyReadType() {
    $token = new Token(
      Token::VALUE_NAME, 0, ''
    );
    $this->assertEquals(Token::VALUE_NAME, $token->type);
  }

  /**
   * @covers \Papaya\Template\Simple\Scanner\Token::__get
   */
  public function testPropertyReadOffset() {
    $token = new Token(
      Token::VALUE_NAME, 42, ''
    );
    $this->assertEquals(42, $token->offset);
  }

  /**
   * @covers \Papaya\Template\Simple\Scanner\Token::__get
   */
  public function testPropertyReadContent() {
    $token = new Token(
      Token::VALUE_NAME, 0, 'foo'
    );
    $this->assertEquals('foo', $token->content);
  }

  /**
   * @covers \Papaya\Template\Simple\Scanner\Token::__get
   */
  public function testPropertyReadLength() {
    $token = new Token(
      Token::VALUE_NAME, 0, 'foo'
    );
    $this->assertEquals(3, $token->length);
  }

  /**
   * @covers \Papaya\Template\Simple\Scanner\Token::__get
   */
  public function testPropertyReadUnkownPropertyExpectingException() {
    $token = new Token(
      Token::VALUE_NAME, 0, 'foo'
    );
    $this->expectException(\LogicException::class);
    $this->expectExceptionMessage('Unknown property: Papaya\Template\Simple\Scanner\Token::$UNKNOWN');
    /** @noinspection PhpUndefinedFieldInspection */
    $token->UNKNOWN;
  }

  /**
   * @covers \Papaya\Template\Simple\Scanner\Token::__set
   */
  public function testPropertyWriteThrowsException() {
    $token = new Token(
      Token::VALUE_NAME, 0, ''
    );
    $this->expectException(\LogicException::class);
    /** @noinspection Annotator */
    $token->offset = 23;
  }
}
