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

class PapayaTemplateSimpleParserTest extends \PapayaTestCase {

  /**
  * @covers \Papaya\Template\Simple\Parser::__construct
  */
  public function testConstructor() {
    $tokens = $this->createTokens(
      array(
        \Papaya\Template\Simple\Scanner\Token::TEXT, 0, 'foo'
      )
    );
    $parser = $this->getParserFixture($tokens);
    $this->assertAttributeSame(
      $tokens, '_tokens', $parser
    );
  }

  /**
   * @covers \Papaya\Template\Simple\Parser::read
   * @covers \Papaya\Template\Simple\Parser::matchToken
   * @dataProvider provideDirectMatchingTokens
   * @param int $expectedResult
   * @param array $tokens
   * @param array|int $allowedTokens
   * @throws \Papaya\Template\Simple\Exception
   */
  public function testReadMatch($expectedResult, array $tokens, $allowedTokens) {
    $parser = $this->getParserFixture($tokens);
    $originalTokens = $parser->_tokens;
    $readToken = array_shift($originalTokens);

    $parser = $this->getParserFixture($tokens);

    $result = $parser->read($allowedTokens);

    $this->assertEquals($readToken, $result);
    $this->assertEquals($expectedResult, $result->type);
    $this->assertEquals($parser->_tokens, $originalTokens);
  }

  /**
   * @covers \Papaya\Template\Simple\Parser::read
   * @covers \Papaya\Template\Simple\Parser::matchToken
   * @covers \Papaya\Template\Simple\Parser::createMismatchException
   * @dataProvider provideDirectMismatchingTokens
   * @param array $tokens
   * @param array|int $allowedTokens
   * @throws \Papaya\Template\Simple\Exception
   */
  public function testReadMismatch(array $tokens, $allowedTokens) {
    $parser = $this->getParserFixture($tokens);
    $this->expectException(\Papaya\Template\Simple\Exception\Parser::class);
    $parser->read($allowedTokens);
  }

  /**
   * @covers \Papaya\Template\Simple\Parser::lookahead
   * @dataProvider provideDirectMatchingTokens
   * @param int $expectedResult
   * @param array $tokens
   * @param array|int $allowedTokens
   * @throws \Papaya\Template\Simple\Exception
   */
  public function testDirectLookaheadMatch($expectedResult, array $tokens, $allowedTokens) {
    $parser = $this->getParserFixture($tokens);
    $originalTokens = $parser->_tokens;

    $result = $parser->lookahead($allowedTokens);

    $this->assertSame($originalTokens[0], $result);
    $this->assertEquals($expectedResult, $result->type);
    $this->assertEquals($parser->_tokens, $originalTokens);
  }

  /**
   * @covers \Papaya\Template\Simple\Parser::lookahead
   * @dataProvider provideDirectMismatchingTokens
   * @param array $tokens
   * @param array|int $allowedTokens
   * @throws \Papaya\Template\Simple\Exception
   */
  public function testDirectLookaheadMismatch(array $tokens, $allowedTokens) {
    $parser = $this->getParserFixture($tokens);
    $this->expectException(\Papaya\Template\Simple\Exception\Parser::class);
    $parser->lookahead($allowedTokens);
  }

  /**
   * @covers \Papaya\Template\Simple\Parser::lookahead
   * @dataProvider provideLookaheadMatchingTokens
   * @param int $expectedResult
   * @param array $tokens
   * @param array|int $allowedTokens
   * @throws \Papaya\Template\Simple\Exception
  */
  public function testLookaheadMatch($expectedResult, array $tokens, $allowedTokens) {
    $parser = $this->getParserFixture($tokens);
    $originalTokens = $parser->_tokens;

    $result = $parser->lookahead($allowedTokens, 1);

    $this->assertSame($originalTokens[1], $result);
    $this->assertEquals($expectedResult, $result->type);
    $this->assertEquals($parser->_tokens, $originalTokens);
  }

  /**
   * @covers \Papaya\Template\Simple\Parser::lookahead
   * @dataProvider provideLookaheadMismatchingTokens
   * @param array $tokens
   * @param array|int $allowedTokens
   * @throws \Papaya\Template\Simple\Exception
   */
  public function testLookaheadMismatch(array $tokens, $allowedTokens) {
    $parser = $this->getParserFixture($tokens);
    $this->expectException(\Papaya\Template\Simple\Exception\Parser::class);
    $parser->lookahead($allowedTokens, 1);
  }

  /**
  * @covers \Papaya\Template\Simple\Parser::endOfTokens
  */
  public function testEndOfTokensExpectingTrue() {
    $tokens = array();
    $parser = $this->getParserFixture($tokens);
    $this->assertTrue($parser->endOfTokens());
  }

  /**
  * @covers \Papaya\Template\Simple\Parser::endOfTokens
  */
  public function testEndOfTokensExpectingFalse() {
    $tokens = $this->createTokens(
      array(
        array(\Papaya\Template\Simple\Scanner\Token::TEXT, 0, 'foo')
      )
    );
    $parser = $this->getParserFixture($tokens);
    $this->assertFalse($parser->endOfTokens());
  }

  /**
  * @covers \Papaya\Template\Simple\Parser::endOfTokens
  */
  public function testEndOfTokensWithPositionExpectingTrue() {
    $tokens = $this->createTokens(
      array(
        array(\Papaya\Template\Simple\Scanner\Token::TEXT, 0, 'foo')
      )
    );
    $parser = $this->getParserFixture($tokens);
    $this->assertTrue($parser->endOfTokens(2));
  }

  /**
  * @covers \Papaya\Template\Simple\Parser::endOfTokens
  */
  public function testEndOfTokensWithPositionExpectingFalse() {
    $tokens = $this->createTokens(
      array(
        array(\Papaya\Template\Simple\Scanner\Token::TEXT, 0, 'foo'),
        array(\Papaya\Template\Simple\Scanner\Token::TEXT, 0, 'bar')
      )
    );
    $parser = $this->getParserFixture($tokens);
    $this->assertFalse($parser->endOfTokens(1));
  }

  /**
  * @covers \Papaya\Template\Simple\Parser::lookahead
  */
  public function testLookAheadAllowingEndOfTokens() {
    $parser = $this->getParserFixture(array());
    $this->assertEquals(
      new \Papaya\Template\Simple\Scanner\Token(\Papaya\Template\Simple\Scanner\Token::ANY, 0, ''),
      $parser->lookahead(\Papaya\Template\Simple\Scanner\Token::TEXT, 0, TRUE)
    );
  }

  /**
  * @covers \Papaya\Template\Simple\Parser::lookahead
  */
  public function testLookAheadWithPositionAllowingEndOfTokens() {
    $tokens = $this->createTokens(
      array(
        array(\Papaya\Template\Simple\Scanner\Token::TEXT, 0, 'foo')
      )
    );
    $parser = $this->getParserFixture($tokens);
    $this->assertEquals(
      new \Papaya\Template\Simple\Scanner\Token(\Papaya\Template\Simple\Scanner\Token::ANY, 0, ''),
      $parser->lookahead(\Papaya\Template\Simple\Scanner\Token::TEXT, 1, TRUE)
    );
  }

  /**
  * @covers \Papaya\Template\Simple\Parser::ignore
  */
  public function testIgnoreExpectingTrue() {
    $tokens = $this->createTokens(
      array(
        array(\Papaya\Template\Simple\Scanner\Token::WHITESPACE, 0, ' '),
        array(\Papaya\Template\Simple\Scanner\Token::TEXT, 1, 'foo')
      )
    );
    $parser = $this->getParserFixture($tokens);
    $this->assertTrue(
      $parser->ignore(\Papaya\Template\Simple\Scanner\Token::WHITESPACE)
    );
    $this->assertTrue($parser->endOfTokens(1));
  }

  /**
  * @covers \Papaya\Template\Simple\Parser::ignore
  */
  public function testIgnoreMultipleTokensExpectingTrue() {
    $tokens = $this->createTokens(
      array(
        array(\Papaya\Template\Simple\Scanner\Token::WHITESPACE, 0, ' '),
        array(\Papaya\Template\Simple\Scanner\Token::WHITESPACE, 1, ' '),
        array(\Papaya\Template\Simple\Scanner\Token::TEXT, 2, 'foo')
      )
    );
    $parser = $this->getParserFixture($tokens);
    $this->assertTrue(
      $parser->ignore(
        \Papaya\Template\Simple\Scanner\Token::WHITESPACE
      )
    );
    $this->assertTrue($parser->endOfTokens(1));
  }

  /**
  * @covers \Papaya\Template\Simple\Parser::ignore
  */
  public function testIgnoreExpectingFalse() {
    $tokens = array(
      array(\Papaya\Template\Simple\Scanner\Token::TEXT, 0, 'foo')
    );
    $parser = $this->getParserFixture($tokens);
    $this->assertFalse(
      $parser->ignore(\Papaya\Template\Simple\Scanner\Token::WHITESPACE)
    );
    $this->assertTrue($parser->endOfTokens(1));
  }

  /**
  * @covers \Papaya\Template\Simple\Parser::delegate
  */
  public function testDelegate() {
    $parser = $this->getParserFixture();
    $this->assertEquals(
      'Delegated!',
      $parser->delegate(\PapayaTemplateSimpleParser_TestProxyDelegate::class)
    );
  }

  /**
  * @covers \Papaya\Template\Simple\Parser::delegate
  */
  public function testDelegateWithInvalidClassExpectingException() {
    $parser = $this->getParserFixture();
    $this->expectException(LogicException::class);
    $parser->delegate(stdClass::class);
  }

  /*****************************
  * Fixtures
  *****************************/

  /**
   * @param array $tokens
   * @return \PapayaTemplateSimpleParser_TestProxy
   */
  public function getParserFixture(array $tokens = array()) {
    $tokens = $this->createTokens($tokens);
    return new \PapayaTemplateSimpleParser_TestProxy($tokens);
  }

  /**
   * @param array $tokens
   * @return \PapayaTemplateSimpleParser_TestProxy
   */
  public function getParserFixtureWithReference(array &$tokens) {
    return new \PapayaTemplateSimpleParser_TestProxy($tokens);
  }

  public function createTokens($data) {
    $tokens = array();
    if (count($data) > 0 && is_int($data[0])) {
      $data = array($data);
    }
    foreach ($data as $token) {
      if ($token instanceof \Papaya\Template\Simple\Scanner\Token) {
        $tokens[] = $token;
      } else {
        $tokens[] = new \Papaya\Template\Simple\Scanner\Token(
          $token[0], $token[1], $token[2]
        );
      }
    }
    return $tokens;
  }

  /*****************************
  * Data Provider
  *****************************/

  public static function provideDirectMatchingTokens() {
    return array(
      'one token, one token type' => array(
        \Papaya\Template\Simple\Scanner\Token::TEXT, // expected token type
        array(
          array(\Papaya\Template\Simple\Scanner\Token::TEXT, 0, 'foo')
        ), // token list data
        array(\Papaya\Template\Simple\Scanner\Token::TEXT), // allowed token types
      ),
      'one token, one token type as string' => array(
        \Papaya\Template\Simple\Scanner\Token::TEXT,
        array(
          array(\Papaya\Template\Simple\Scanner\Token::TEXT, 0, 'foo')
        ),
        \Papaya\Template\Simple\Scanner\Token::TEXT,
      ),
      'one token, two token types' =>  array(
        \Papaya\Template\Simple\Scanner\Token::TEXT,
        array(
          array(\Papaya\Template\Simple\Scanner\Token::TEXT, 0, 'foo')
        ),
        array(\Papaya\Template\Simple\Scanner\Token::VALUE_NAME, \Papaya\Template\Simple\Scanner\Token::TEXT),
      ),
      'two tokens, one token type' => array(
        \Papaya\Template\Simple\Scanner\Token::TEXT,
        array(
          array(\Papaya\Template\Simple\Scanner\Token::TEXT, 0, 'foo'),
          array(\Papaya\Template\Simple\Scanner\Token::VALUE_NAME, 0, '/*$bar*/')
        ),
        array(\Papaya\Template\Simple\Scanner\Token::TEXT),
      ),
      'two tokens, two token types' => array(
        \Papaya\Template\Simple\Scanner\Token::TEXT,
        array(
          array(\Papaya\Template\Simple\Scanner\Token::TEXT, 0, 'foo'),
          array(\Papaya\Template\Simple\Scanner\Token::VALUE_NAME, 0, '/*$bar*/')
        ),
        array(\Papaya\Template\Simple\Scanner\Token::TEXT, \Papaya\Template\Simple\Scanner\Token::VALUE_NAME),
      ),
      'two tokens, any token type' => array(
        \Papaya\Template\Simple\Scanner\Token::TEXT,
        array(
          array(\Papaya\Template\Simple\Scanner\Token::TEXT, 0, 'foo'),
          array(\Papaya\Template\Simple\Scanner\Token::VALUE_NAME, 0, '/*$bar*/')
        ),
        array(\Papaya\Template\Simple\Scanner\Token::ANY),
      ),
      'two tokens, any token type as skalar' => array(
        \Papaya\Template\Simple\Scanner\Token::TEXT,
        array(
          array(\Papaya\Template\Simple\Scanner\Token::TEXT, 0, 'foo'),
          array(\Papaya\Template\Simple\Scanner\Token::VALUE_NAME, 0, '/*$bar*/')
        ),
        \Papaya\Template\Simple\Scanner\Token::ANY,
      )
    );
  }

  public static function provideDirectMismatchingTokens() {
    return array(
      'one token, one token type' => array(
        array(
          array(\Papaya\Template\Simple\Scanner\Token::TEXT, 0, 'foo')
        ), // token list
        array(\Papaya\Template\Simple\Scanner\Token::VALUE_NAME), // allowed token types
      ),
      'one token, two token types' => array(
        array(
          array(\Papaya\Template\Simple\Scanner\Token::TEXT, 0, 'foo')
        ),
        array(
          \Papaya\Template\Simple\Scanner\Token::VALUE_NAME,
          \Papaya\Template\Simple\Scanner\Token::VALUE_DEFAULT
        ),
      ),
      'two tokens, one token type' => array(
        array(
          array(\Papaya\Template\Simple\Scanner\Token::TEXT, 0, 'foo'),
          array(\Papaya\Template\Simple\Scanner\Token::VALUE_NAME, 0, '/*$bar*/')
        ),
        array(\Papaya\Template\Simple\Scanner\Token::VALUE_NAME),
      ),
      'two tokens, two token types' => array(
        array(
          array(\Papaya\Template\Simple\Scanner\Token::TEXT, 0, 'foo'),
          array(\Papaya\Template\Simple\Scanner\Token::VALUE_NAME, 0, '/*$bar*/')
        ),
        array(
          \Papaya\Template\Simple\Scanner\Token::VALUE_NAME,
          \Papaya\Template\Simple\Scanner\Token::VALUE_DEFAULT
        ),
      ),
      'empty tokens, one token type' => array(
        array(),
        array(\Papaya\Template\Simple\Scanner\Token::TEXT),
      ),
      'empty tokens, special any token type' => array(
        array(),
        array(\Papaya\Template\Simple\Scanner\Token::ANY),
      )
    );
  }

  public static function provideLookaheadMatchingTokens() {
    return array(
      array(
        \Papaya\Template\Simple\Scanner\Token::VALUE_NAME,
        array(
          array(\Papaya\Template\Simple\Scanner\Token::TEXT, 0, 'foo'),
          array(\Papaya\Template\Simple\Scanner\Token::VALUE_NAME, 0, '/*$bar*/')
        ),
        array(\Papaya\Template\Simple\Scanner\Token::VALUE_NAME)
      ),
      array(
        \Papaya\Template\Simple\Scanner\Token::VALUE_NAME,
        array(
          array(\Papaya\Template\Simple\Scanner\Token::TEXT, 0, 'foo'),
          array(\Papaya\Template\Simple\Scanner\Token::VALUE_NAME, 0, '/*$bar*/')
        ),
        array(\Papaya\Template\Simple\Scanner\Token::VALUE_NAME, \Papaya\Template\Simple\Scanner\Token::TEXT)
      ),
      array(
        \Papaya\Template\Simple\Scanner\Token::VALUE_NAME,
        array(
          array(\Papaya\Template\Simple\Scanner\Token::TEXT, 0, 'foo'),
          array(\Papaya\Template\Simple\Scanner\Token::VALUE_NAME, 0, '/*$bar*/')
        ),
        array(\Papaya\Template\Simple\Scanner\Token::ANY)
      ),
      array(
        \Papaya\Template\Simple\Scanner\Token::VALUE_NAME,
        array(
          array(\Papaya\Template\Simple\Scanner\Token::TEXT, 0, 'foo'),
          array(\Papaya\Template\Simple\Scanner\Token::VALUE_NAME, 0, '/*$bar*/')
        ),
        \Papaya\Template\Simple\Scanner\Token::ANY
      )
    );
  }

  public static function provideLookaheadMismatchingTokens() {
    return array(
      array(
        array(
          array(\Papaya\Template\Simple\Scanner\Token::TEXT, 0, 'foo')
        ),
        array(
          \Papaya\Template\Simple\Scanner\Token::TEXT
        )
      ),
      array(
        array(
          array(\Papaya\Template\Simple\Scanner\Token::TEXT, 0, 'foo')
        ),
        array(
          \Papaya\Template\Simple\Scanner\Token::TEXT,
          \Papaya\Template\Simple\Scanner\Token::VALUE_NAME
        )
      ),
      array(
        array(
          array(\Papaya\Template\Simple\Scanner\Token::TEXT, 0, 'foo'),
          array(\Papaya\Template\Simple\Scanner\Token::VALUE_NAME, 0, 'foo')
        ),
        array(\Papaya\Template\Simple\Scanner\Token::TEXT)
      ),
      array(
        array(
          array(\Papaya\Template\Simple\Scanner\Token::TEXT, 0, 'foo'),
          array(\Papaya\Template\Simple\Scanner\Token::VALUE_NAME, 0, 'foo')
        ),
        array(
          \Papaya\Template\Simple\Scanner\Token::TEXT,
          \Papaya\Template\Simple\Scanner\Token::VALUE_DEFAULT
        )
      )
    );
  }
}

class PapayaTemplateSimpleParser_TestProxy extends \Papaya\Template\Simple\Parser {

  public $_tokens;

  public function parse() {
    // Nothing to do here
  }

  public function read($expectedTokens) {
    return parent::read($expectedTokens);
  }

  public function lookahead($expectedTokens, $position = 0, $allowEndOfTokens = false) {
    return parent::lookahead($expectedTokens, $position, $allowEndOfTokens);
  }

  public function endOfTokens($position = 0) {
    return parent::endOfTokens($position);
  }

  public function ignore($expectedTokens) {
    return parent::ignore($expectedTokens);
  }

  public function delegate($subparser) {
    return parent::delegate($subparser);
  }
}
class PapayaTemplateSimpleParser_TestProxyDelegate extends \PapayaTemplateSimpleParser_TestProxy {

  public function parse() {
    return 'Delegated!';
  }

}
