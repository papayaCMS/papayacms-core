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
  * @covers \PapayaTemplateSimpleParser::__construct
  */
  public function testConstructor() {
    $tokens = $this->createTokens(
      array(
        \PapayaTemplateSimpleScannerToken::TEXT, 0, 'foo'
      )
    );
    $parser = $this->getParserFixture($tokens);
    $this->assertAttributeSame(
      $tokens, '_tokens', $parser
    );
  }

  /**
   * @covers \PapayaTemplateSimpleParser::read
   * @covers \PapayaTemplateSimpleParser::matchToken
   * @dataProvider provideDirectMatchingTokens
   * @param int $expectedResult
   * @param array $tokens
   * @param array|int $allowedTokens
   * @throws \PapayaTemplateSimpleException
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
   * @covers \PapayaTemplateSimpleParser::read
   * @covers \PapayaTemplateSimpleParser::matchToken
   * @covers \PapayaTemplateSimpleParser::createMismatchException
   * @dataProvider provideDirectMismatchingTokens
   * @param array $tokens
   * @param array|int $allowedTokens
   * @throws \PapayaTemplateSimpleException
   */
  public function testReadMismatch(array $tokens, $allowedTokens) {
    $parser = $this->getParserFixture($tokens);
    $this->expectException(\PapayaTemplateSimpleExceptionParser::class);
    $parser->read($allowedTokens);
  }

  /**
   * @covers \PapayaTemplateSimpleParser::lookahead
   * @dataProvider provideDirectMatchingTokens
   * @param int $expectedResult
   * @param array $tokens
   * @param array|int $allowedTokens
   * @throws \PapayaTemplateSimpleException
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
   * @covers \PapayaTemplateSimpleParser::lookahead
   * @dataProvider provideDirectMismatchingTokens
   * @param array $tokens
   * @param array|int $allowedTokens
   * @throws \PapayaTemplateSimpleException
   */
  public function testDirectLookaheadMismatch(array $tokens, $allowedTokens) {
    $parser = $this->getParserFixture($tokens);
    $this->expectException(\PapayaTemplateSimpleExceptionParser::class);
    $parser->lookahead($allowedTokens);
  }

  /**
   * @covers \PapayaTemplateSimpleParser::lookahead
   * @dataProvider provideLookaheadMatchingTokens
   * @param int $expectedResult
   * @param array $tokens
   * @param array|int $allowedTokens
   * @throws \PapayaTemplateSimpleException
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
   * @covers \PapayaTemplateSimpleParser::lookahead
   * @dataProvider provideLookaheadMismatchingTokens
   * @param array $tokens
   * @param array|int $allowedTokens
   * @throws \PapayaTemplateSimpleException
   */
  public function testLookaheadMismatch(array $tokens, $allowedTokens) {
    $parser = $this->getParserFixture($tokens);
    $this->expectException(\PapayaTemplateSimpleExceptionParser::class);
    $parser->lookahead($allowedTokens, 1);
  }

  /**
  * @covers \PapayaTemplateSimpleParser::endOfTokens
  */
  public function testEndOfTokensExpectingTrue() {
    $tokens = array();
    $parser = $this->getParserFixture($tokens);
    $this->assertTrue($parser->endOfTokens());
  }

  /**
  * @covers \PapayaTemplateSimpleParser::endOfTokens
  */
  public function testEndOfTokensExpectingFalse() {
    $tokens = $this->createTokens(
      array(
        array(\PapayaTemplateSimpleScannerToken::TEXT, 0, 'foo')
      )
    );
    $parser = $this->getParserFixture($tokens);
    $this->assertFalse($parser->endOfTokens());
  }

  /**
  * @covers \PapayaTemplateSimpleParser::endOfTokens
  */
  public function testEndOfTokensWithPositionExpectingTrue() {
    $tokens = $this->createTokens(
      array(
        array(\PapayaTemplateSimpleScannerToken::TEXT, 0, 'foo')
      )
    );
    $parser = $this->getParserFixture($tokens);
    $this->assertTrue($parser->endOfTokens(2));
  }

  /**
  * @covers \PapayaTemplateSimpleParser::endOfTokens
  */
  public function testEndOfTokensWithPositionExpectingFalse() {
    $tokens = $this->createTokens(
      array(
        array(\PapayaTemplateSimpleScannerToken::TEXT, 0, 'foo'),
        array(\PapayaTemplateSimpleScannerToken::TEXT, 0, 'bar')
      )
    );
    $parser = $this->getParserFixture($tokens);
    $this->assertFalse($parser->endOfTokens(1));
  }

  /**
  * @covers \PapayaTemplateSimpleParser::lookahead
  */
  public function testLookAheadAllowingEndOfTokens() {
    $parser = $this->getParserFixture(array());
    $this->assertEquals(
      new \PapayaTemplateSimpleScannerToken(\PapayaTemplateSimpleScannerToken::ANY, 0, ''),
      $parser->lookahead(\PapayaTemplateSimpleScannerToken::TEXT, 0, TRUE)
    );
  }

  /**
  * @covers \PapayaTemplateSimpleParser::lookahead
  */
  public function testLookAheadWithPositionAllowingEndOfTokens() {
    $tokens = $this->createTokens(
      array(
        array(\PapayaTemplateSimpleScannerToken::TEXT, 0, 'foo')
      )
    );
    $parser = $this->getParserFixture($tokens);
    $this->assertEquals(
      new \PapayaTemplateSimpleScannerToken(\PapayaTemplateSimpleScannerToken::ANY, 0, ''),
      $parser->lookahead(\PapayaTemplateSimpleScannerToken::TEXT, 1, TRUE)
    );
  }

  /**
  * @covers \PapayaTemplateSimpleParser::ignore
  */
  public function testIgnoreExpectingTrue() {
    $tokens = $this->createTokens(
      array(
        array(\PapayaTemplateSimpleScannerToken::WHITESPACE, 0, ' '),
        array(\PapayaTemplateSimpleScannerToken::TEXT, 1, 'foo')
      )
    );
    $parser = $this->getParserFixture($tokens);
    $this->assertTrue(
      $parser->ignore(\PapayaTemplateSimpleScannerToken::WHITESPACE)
    );
    $this->assertTrue($parser->endOfTokens(1));
  }

  /**
  * @covers \PapayaTemplateSimpleParser::ignore
  */
  public function testIgnoreMultipleTokensExpectingTrue() {
    $tokens = $this->createTokens(
      array(
        array(\PapayaTemplateSimpleScannerToken::WHITESPACE, 0, ' '),
        array(\PapayaTemplateSimpleScannerToken::WHITESPACE, 1, ' '),
        array(\PapayaTemplateSimpleScannerToken::TEXT, 2, 'foo')
      )
    );
    $parser = $this->getParserFixture($tokens);
    $this->assertTrue(
      $parser->ignore(
        \PapayaTemplateSimpleScannerToken::WHITESPACE
      )
    );
    $this->assertTrue($parser->endOfTokens(1));
  }

  /**
  * @covers \PapayaTemplateSimpleParser::ignore
  */
  public function testIgnoreExpectingFalse() {
    $tokens = array(
      array(\PapayaTemplateSimpleScannerToken::TEXT, 0, 'foo')
    );
    $parser = $this->getParserFixture($tokens);
    $this->assertFalse(
      $parser->ignore(\PapayaTemplateSimpleScannerToken::WHITESPACE)
    );
    $this->assertTrue($parser->endOfTokens(1));
  }

  /**
  * @covers \PapayaTemplateSimpleParser::delegate
  */
  public function testDelegate() {
    $parser = $this->getParserFixture();
    $this->assertEquals(
      'Delegated!',
      $parser->delegate(\PapayaTemplateSimpleParser_TestProxyDelegate::class)
    );
  }

  /**
  * @covers \PapayaTemplateSimpleParser::delegate
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
      if ($token instanceof \PapayaTemplateSimpleScannerToken) {
        $tokens[] = $token;
      } else {
        $tokens[] = new \PapayaTemplateSimpleScannerToken(
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
        \PapayaTemplateSimpleScannerToken::TEXT, // expected token type
        array(
          array(\PapayaTemplateSimpleScannerToken::TEXT, 0, 'foo')
        ), // token list data
        array(\PapayaTemplateSimpleScannerToken::TEXT), // allowed token types
      ),
      'one token, one token type as string' => array(
        \PapayaTemplateSimpleScannerToken::TEXT,
        array(
          array(\PapayaTemplateSimpleScannerToken::TEXT, 0, 'foo')
        ),
        \PapayaTemplateSimpleScannerToken::TEXT,
      ),
      'one token, two token types' =>  array(
        \PapayaTemplateSimpleScannerToken::TEXT,
        array(
          array(\PapayaTemplateSimpleScannerToken::TEXT, 0, 'foo')
        ),
        array(\PapayaTemplateSimpleScannerToken::VALUE_NAME, \PapayaTemplateSimpleScannerToken::TEXT),
      ),
      'two tokens, one token type' => array(
        \PapayaTemplateSimpleScannerToken::TEXT,
        array(
          array(\PapayaTemplateSimpleScannerToken::TEXT, 0, 'foo'),
          array(\PapayaTemplateSimpleScannerToken::VALUE_NAME, 0, '/*$bar*/')
        ),
        array(\PapayaTemplateSimpleScannerToken::TEXT),
      ),
      'two tokens, two token types' => array(
        \PapayaTemplateSimpleScannerToken::TEXT,
        array(
          array(\PapayaTemplateSimpleScannerToken::TEXT, 0, 'foo'),
          array(\PapayaTemplateSimpleScannerToken::VALUE_NAME, 0, '/*$bar*/')
        ),
        array(\PapayaTemplateSimpleScannerToken::TEXT, \PapayaTemplateSimpleScannerToken::VALUE_NAME),
      ),
      'two tokens, any token type' => array(
        \PapayaTemplateSimpleScannerToken::TEXT,
        array(
          array(\PapayaTemplateSimpleScannerToken::TEXT, 0, 'foo'),
          array(\PapayaTemplateSimpleScannerToken::VALUE_NAME, 0, '/*$bar*/')
        ),
        array(\PapayaTemplateSimpleScannerToken::ANY),
      ),
      'two tokens, any token type as skalar' => array(
        \PapayaTemplateSimpleScannerToken::TEXT,
        array(
          array(\PapayaTemplateSimpleScannerToken::TEXT, 0, 'foo'),
          array(\PapayaTemplateSimpleScannerToken::VALUE_NAME, 0, '/*$bar*/')
        ),
        \PapayaTemplateSimpleScannerToken::ANY,
      )
    );
  }

  public static function provideDirectMismatchingTokens() {
    return array(
      'one token, one token type' => array(
        array(
          array(\PapayaTemplateSimpleScannerToken::TEXT, 0, 'foo')
        ), // token list
        array(\PapayaTemplateSimpleScannerToken::VALUE_NAME), // allowed token types
      ),
      'one token, two token types' => array(
        array(
          array(\PapayaTemplateSimpleScannerToken::TEXT, 0, 'foo')
        ),
        array(
          \PapayaTemplateSimpleScannerToken::VALUE_NAME,
          \PapayaTemplateSimpleScannerToken::VALUE_DEFAULT
        ),
      ),
      'two tokens, one token type' => array(
        array(
          array(\PapayaTemplateSimpleScannerToken::TEXT, 0, 'foo'),
          array(\PapayaTemplateSimpleScannerToken::VALUE_NAME, 0, '/*$bar*/')
        ),
        array(\PapayaTemplateSimpleScannerToken::VALUE_NAME),
      ),
      'two tokens, two token types' => array(
        array(
          array(\PapayaTemplateSimpleScannerToken::TEXT, 0, 'foo'),
          array(\PapayaTemplateSimpleScannerToken::VALUE_NAME, 0, '/*$bar*/')
        ),
        array(
          \PapayaTemplateSimpleScannerToken::VALUE_NAME,
          \PapayaTemplateSimpleScannerToken::VALUE_DEFAULT
        ),
      ),
      'empty tokens, one token type' => array(
        array(),
        array(\PapayaTemplateSimpleScannerToken::TEXT),
      ),
      'empty tokens, special any token type' => array(
        array(),
        array(\PapayaTemplateSimpleScannerToken::ANY),
      )
    );
  }

  public static function provideLookaheadMatchingTokens() {
    return array(
      array(
        \PapayaTemplateSimpleScannerToken::VALUE_NAME,
        array(
          array(\PapayaTemplateSimpleScannerToken::TEXT, 0, 'foo'),
          array(\PapayaTemplateSimpleScannerToken::VALUE_NAME, 0, '/*$bar*/')
        ),
        array(\PapayaTemplateSimpleScannerToken::VALUE_NAME)
      ),
      array(
        \PapayaTemplateSimpleScannerToken::VALUE_NAME,
        array(
          array(\PapayaTemplateSimpleScannerToken::TEXT, 0, 'foo'),
          array(\PapayaTemplateSimpleScannerToken::VALUE_NAME, 0, '/*$bar*/')
        ),
        array(\PapayaTemplateSimpleScannerToken::VALUE_NAME, \PapayaTemplateSimpleScannerToken::TEXT)
      ),
      array(
        \PapayaTemplateSimpleScannerToken::VALUE_NAME,
        array(
          array(\PapayaTemplateSimpleScannerToken::TEXT, 0, 'foo'),
          array(\PapayaTemplateSimpleScannerToken::VALUE_NAME, 0, '/*$bar*/')
        ),
        array(\PapayaTemplateSimpleScannerToken::ANY)
      ),
      array(
        \PapayaTemplateSimpleScannerToken::VALUE_NAME,
        array(
          array(\PapayaTemplateSimpleScannerToken::TEXT, 0, 'foo'),
          array(\PapayaTemplateSimpleScannerToken::VALUE_NAME, 0, '/*$bar*/')
        ),
        \PapayaTemplateSimpleScannerToken::ANY
      )
    );
  }

  public static function provideLookaheadMismatchingTokens() {
    return array(
      array(
        array(
          array(\PapayaTemplateSimpleScannerToken::TEXT, 0, 'foo')
        ),
        array(
          \PapayaTemplateSimpleScannerToken::TEXT
        )
      ),
      array(
        array(
          array(\PapayaTemplateSimpleScannerToken::TEXT, 0, 'foo')
        ),
        array(
          \PapayaTemplateSimpleScannerToken::TEXT,
          \PapayaTemplateSimpleScannerToken::VALUE_NAME
        )
      ),
      array(
        array(
          array(\PapayaTemplateSimpleScannerToken::TEXT, 0, 'foo'),
          array(\PapayaTemplateSimpleScannerToken::VALUE_NAME, 0, 'foo')
        ),
        array(\PapayaTemplateSimpleScannerToken::TEXT)
      ),
      array(
        array(
          array(\PapayaTemplateSimpleScannerToken::TEXT, 0, 'foo'),
          array(\PapayaTemplateSimpleScannerToken::VALUE_NAME, 0, 'foo')
        ),
        array(
          \PapayaTemplateSimpleScannerToken::TEXT,
          \PapayaTemplateSimpleScannerToken::VALUE_DEFAULT
        )
      )
    );
  }
}

class PapayaTemplateSimpleParser_TestProxy extends \PapayaTemplateSimpleParser {

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
