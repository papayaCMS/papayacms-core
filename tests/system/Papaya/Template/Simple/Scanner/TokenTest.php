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

namespace Papaya\Template\Simple\Scanner {

  use Papaya\TestFramework\TestCase;

  require_once __DIR__.'/../../../../../bootstrap.php';

  /**
   * @covers \Papaya\Template\Simple\Scanner\Token
   */
  class TokenTest extends TestCase {

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

    public function testConstructorWithInvalidTypeExpectingException() {
      $this->expectException(\InvalidArgumentException::class);
      new Token(-23, 0, '');
    }

    public function testGetTypeStringExpectingText() {
      $this->assertEquals(
        'TEXT',
        Token::getTypeString(Token::TEXT)
      );
    }

    public function testGetTypeStringWithInvalidTokenTypeExpectingNull() {
      $this->assertNull(
        Token::getTypeString(-23)
      );
    }

    public function testPropertyReadType() {
      $token = new Token(
        Token::VALUE_NAME, 0, ''
      );
      $this->assertTrue(isset($token->type));
      $this->assertEquals(Token::VALUE_NAME, $token->type);
    }

    public function testPropertyReadOffset() {
      $token = new Token(
        Token::VALUE_NAME, 42, ''
      );
      $this->assertTrue(isset($token->offset));
      $this->assertEquals(42, $token->offset);
    }

    public function testPropertyReadContent() {
      $token = new Token(
        Token::VALUE_NAME, 0, 'foo'
      );
      $this->assertTrue(isset($token->content));
      $this->assertEquals('foo', $token->content);
    }

    public function testPropertyReadLength() {
      $token = new Token(
        Token::VALUE_NAME, 0, 'foo'
      );
      $this->assertTrue(isset($token->length));
      $this->assertEquals(3, $token->length);
    }

    public function testPropertyReadUnknownPropertyExpectingException() {
      $token = new Token(
        Token::VALUE_NAME, 0, 'foo'
      );
      $this->assertFalse(isset($token->UNKNOWN));
      $this->expectException(\LogicException::class);
      $this->expectExceptionMessage('Unknown property: Papaya\Template\Simple\Scanner\Token::$UNKNOWN');
      /** @noinspection PhpUndefinedFieldInspection */
      $token->UNKNOWN;
    }

    public function testPropertyWriteThrowsException() {
      $token = new Token(
        Token::VALUE_NAME, 0, ''
      );
      $this->expectException(\LogicException::class);
      /** @noinspection Annotator */
      $token->offset = 23;
    }

    public function testPropertyUnsetThrowsException() {
      $token = new Token(
        Token::VALUE_NAME, 0, ''
      );
      $this->expectException(\LogicException::class);
      /** @noinspection Annotator */
     unset($token->offset);
    }
  }
}
