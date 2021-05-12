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

namespace Papaya\Template\Simple {

  require_once __DIR__.'/../../../../bootstrap.php';

  /**
   * @covers \Papaya\Template\Simple\Parser
   */
  class ParserTest extends \Papaya\TestCase {

    /**
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
     * @dataProvider provideDirectMismatchingTokens
     * @param array $tokens
     * @param array|int $allowedTokens
     * @throws \Papaya\Template\Simple\Exception
     */
    public function testReadMismatch(array $tokens, $allowedTokens) {
      $parser = $this->getParserFixture($tokens);
      $this->expectException(Exception\Parser::class);
      $parser->read($allowedTokens);
    }

    /**
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
     * @dataProvider provideDirectMismatchingTokens
     * @param array $tokens
     * @param array|int $allowedTokens
     * @throws \Papaya\Template\Simple\Exception
     */
    public function testDirectLookaheadMismatch(array $tokens, $allowedTokens) {
      $parser = $this->getParserFixture($tokens);
      $this->expectException(Exception\Parser::class);
      $parser->lookahead($allowedTokens);
    }

    /**
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
     * @dataProvider provideLookaheadMismatchingTokens
     * @param array $tokens
     * @param array|int $allowedTokens
     * @throws \Papaya\Template\Simple\Exception
     */
    public function testLookaheadMismatch(array $tokens, $allowedTokens) {
      $parser = $this->getParserFixture($tokens);
      $this->expectException(Exception\Parser::class);
      $parser->lookahead($allowedTokens, 1);
    }

    public function testEndOfTokensExpectingTrue() {
      $tokens = array();
      $parser = $this->getParserFixture($tokens);
      $this->assertTrue($parser->endOfTokens());
    }

    public function testEndOfTokensExpectingFalse() {
      $tokens = $this->createTokens(
        array(
          array(Scanner\Token::TEXT, 0, 'foo')
        )
      );
      $parser = $this->getParserFixture($tokens);
      $this->assertFalse($parser->endOfTokens());
    }

    public function testEndOfTokensWithPositionExpectingTrue() {
      $tokens = $this->createTokens(
        array(
          array(Scanner\Token::TEXT, 0, 'foo')
        )
      );
      $parser = $this->getParserFixture($tokens);
      $this->assertTrue($parser->endOfTokens(2));
    }

    public function testEndOfTokensWithPositionExpectingFalse() {
      $tokens = $this->createTokens(
        array(
          array(Scanner\Token::TEXT, 0, 'foo'),
          array(Scanner\Token::TEXT, 0, 'bar')
        )
      );
      $parser = $this->getParserFixture($tokens);
      $this->assertFalse($parser->endOfTokens(1));
    }

    public function testLookAheadAllowingEndOfTokens() {
      $parser = $this->getParserFixture(array());
      $this->assertEquals(
        new Scanner\Token(Scanner\Token::ANY, 0, ''),
        $parser->lookahead(Scanner\Token::TEXT, 0, TRUE)
      );
    }

    public function testLookAheadWithPositionAllowingEndOfTokens() {
      $tokens = $this->createTokens(
        array(
          array(Scanner\Token::TEXT, 0, 'foo')
        )
      );
      $parser = $this->getParserFixture($tokens);
      $this->assertEquals(
        new Scanner\Token(Scanner\Token::ANY, 0, ''),
        $parser->lookahead(Scanner\Token::TEXT, 1, TRUE)
      );
    }

    public function testIgnoreExpectingTrue() {
      $tokens = $this->createTokens(
        array(
          array(Scanner\Token::WHITESPACE, 0, ' '),
          array(Scanner\Token::TEXT, 1, 'foo')
        )
      );
      $parser = $this->getParserFixture($tokens);
      $this->assertTrue(
        $parser->ignore(Scanner\Token::WHITESPACE)
      );
      $this->assertTrue($parser->endOfTokens(1));
    }

    public function testIgnoreMultipleTokensExpectingTrue() {
      $tokens = $this->createTokens(
        array(
          array(Scanner\Token::WHITESPACE, 0, ' '),
          array(Scanner\Token::WHITESPACE, 1, ' '),
          array(Scanner\Token::TEXT, 2, 'foo')
        )
      );
      $parser = $this->getParserFixture($tokens);
      $this->assertTrue(
        $parser->ignore(
          Scanner\Token::WHITESPACE
        )
      );
      $this->assertTrue($parser->endOfTokens(1));
    }

    public function testIgnoreExpectingFalse() {
      $tokens = array(
        array(Scanner\Token::TEXT, 0, 'foo')
      );
      $parser = $this->getParserFixture($tokens);
      $this->assertFalse(
        $parser->ignore(Scanner\Token::WHITESPACE)
      );
      $this->assertTrue($parser->endOfTokens(1));
    }

    public function testDelegate() {
      $parser = $this->getParserFixture();
      $this->assertEquals(
        'Delegated!',
        $parser->delegate(Parser_TestProxyDelegate::class)
      );
    }

    public function testDelegateWithInvalidClassExpectingException() {
      $parser = $this->getParserFixture();
      $this->expectException(\LogicException::class);
      $parser->delegate(\stdClass::class);
    }

    /*****************************
     * Fixtures
     *****************************/

    /**
     * @param array $tokens
     * @return Parser_TestProxy
     */
    public function getParserFixture(array $tokens = array()) {
      $tokens = $this->createTokens($tokens);
      return new Parser_TestProxy($tokens);
    }

    /**
     * @param array $tokens
     * @return Parser_TestProxy
     */
    public function getParserFixtureWithReference(array &$tokens) {
      return new Parser_TestProxy($tokens);
    }

    public function createTokens($data) {
      $tokens = array();
      if (count($data) > 0 && is_int($data[0])) {
        $data = array($data);
      }
      foreach ($data as $token) {
        if ($token instanceof Scanner\Token) {
          $tokens[] = $token;
        } else {
          $tokens[] = new Scanner\Token(
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
          Scanner\Token::TEXT, // expected token type
          array(
            array(Scanner\Token::TEXT, 0, 'foo')
          ), // token list data
          array(Scanner\Token::TEXT), // allowed token types
        ),
        'one token, one token type as string' => array(
          Scanner\Token::TEXT,
          array(
            array(Scanner\Token::TEXT, 0, 'foo')
          ),
          Scanner\Token::TEXT,
        ),
        'one token, two token types' => array(
          Scanner\Token::TEXT,
          array(
            array(Scanner\Token::TEXT, 0, 'foo')
          ),
          array(Scanner\Token::VALUE_NAME, Scanner\Token::TEXT),
        ),
        'two tokens, one token type' => array(
          Scanner\Token::TEXT,
          array(
            array(Scanner\Token::TEXT, 0, 'foo'),
            array(Scanner\Token::VALUE_NAME, 0, '/*$bar*/')
          ),
          array(Scanner\Token::TEXT),
        ),
        'two tokens, two token types' => array(
          Scanner\Token::TEXT,
          array(
            array(Scanner\Token::TEXT, 0, 'foo'),
            array(Scanner\Token::VALUE_NAME, 0, '/*$bar*/')
          ),
          array(Scanner\Token::TEXT, Scanner\Token::VALUE_NAME),
        ),
        'two tokens, any token type' => array(
          Scanner\Token::TEXT,
          array(
            array(Scanner\Token::TEXT, 0, 'foo'),
            array(Scanner\Token::VALUE_NAME, 0, '/*$bar*/')
          ),
          array(Scanner\Token::ANY),
        ),
        'two tokens, any token type as skalar' => array(
          Scanner\Token::TEXT,
          array(
            array(Scanner\Token::TEXT, 0, 'foo'),
            array(Scanner\Token::VALUE_NAME, 0, '/*$bar*/')
          ),
          Scanner\Token::ANY,
        )
      );
    }

    public static function provideDirectMismatchingTokens() {
      return array(
        'one token, one token type' => array(
          array(
            array(Scanner\Token::TEXT, 0, 'foo')
          ), // token list
          array(Scanner\Token::VALUE_NAME), // allowed token types
        ),
        'one token, two token types' => array(
          array(
            array(Scanner\Token::TEXT, 0, 'foo')
          ),
          array(
            Scanner\Token::VALUE_NAME,
            Scanner\Token::VALUE_DEFAULT
          ),
        ),
        'two tokens, one token type' => array(
          array(
            array(Scanner\Token::TEXT, 0, 'foo'),
            array(Scanner\Token::VALUE_NAME, 0, '/*$bar*/')
          ),
          array(Scanner\Token::VALUE_NAME),
        ),
        'two tokens, two token types' => array(
          array(
            array(Scanner\Token::TEXT, 0, 'foo'),
            array(Scanner\Token::VALUE_NAME, 0, '/*$bar*/')
          ),
          array(
            Scanner\Token::VALUE_NAME,
            Scanner\Token::VALUE_DEFAULT
          ),
        ),
        'empty tokens, one token type' => array(
          array(),
          array(Scanner\Token::TEXT),
        ),
        'empty tokens, special any token type' => array(
          array(),
          array(Scanner\Token::ANY),
        )
      );
    }

    public static function provideLookaheadMatchingTokens() {
      return array(
        array(
          Scanner\Token::VALUE_NAME,
          array(
            array(Scanner\Token::TEXT, 0, 'foo'),
            array(Scanner\Token::VALUE_NAME, 0, '/*$bar*/')
          ),
          array(Scanner\Token::VALUE_NAME)
        ),
        array(
          Scanner\Token::VALUE_NAME,
          array(
            array(Scanner\Token::TEXT, 0, 'foo'),
            array(Scanner\Token::VALUE_NAME, 0, '/*$bar*/')
          ),
          array(Scanner\Token::VALUE_NAME, Scanner\Token::TEXT)
        ),
        array(
          Scanner\Token::VALUE_NAME,
          array(
            array(Scanner\Token::TEXT, 0, 'foo'),
            array(Scanner\Token::VALUE_NAME, 0, '/*$bar*/')
          ),
          array(Scanner\Token::ANY)
        ),
        array(
          Scanner\Token::VALUE_NAME,
          array(
            array(Scanner\Token::TEXT, 0, 'foo'),
            array(Scanner\Token::VALUE_NAME, 0, '/*$bar*/')
          ),
          Scanner\Token::ANY
        )
      );
    }

    public static function provideLookaheadMismatchingTokens() {
      return array(
        array(
          array(
            array(Scanner\Token::TEXT, 0, 'foo')
          ),
          array(
            Scanner\Token::TEXT
          )
        ),
        array(
          array(
            array(Scanner\Token::TEXT, 0, 'foo')
          ),
          array(
            Scanner\Token::TEXT,
            Scanner\Token::VALUE_NAME
          )
        ),
        array(
          array(
            array(Scanner\Token::TEXT, 0, 'foo'),
            array(Scanner\Token::VALUE_NAME, 0, 'foo')
          ),
          array(Scanner\Token::TEXT)
        ),
        array(
          array(
            array(Scanner\Token::TEXT, 0, 'foo'),
            array(Scanner\Token::VALUE_NAME, 0, 'foo')
          ),
          array(
            Scanner\Token::TEXT,
            Scanner\Token::VALUE_DEFAULT
          )
        )
      );
    }
  }

  class Parser_TestProxy extends Parser {

    public $_tokens;

    public function parse() {
      // Nothing to do here
    }

    public function read($expectedTokens) {
      return parent::read($expectedTokens);
    }

    public function lookahead($expectedTokens, $position = 0, $allowEndOfTokens = FALSE) {
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

  class Parser_TestProxyDelegate extends Parser_TestProxy {

    public function parse() {
      return 'Delegated!';
    }

  }

}
