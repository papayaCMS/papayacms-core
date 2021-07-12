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

namespace Papaya\Template\Simple\Parser;
require_once __DIR__.'/../../../../../bootstrap.php';

class OutputTest extends \Papaya\TestFramework\TestCase {

  /**
   * @covers \Papaya\Template\Simple\Parser\Output::parse
   */
  public function testWithText() {
    $tokens = array(
      new \Papaya\Template\Simple\Scanner\Token(\Papaya\Template\Simple\Scanner\Token::TEXT, 0, 'foo')
    );
    $parser = new Output($tokens);
    $this->assertEquals(
      new \Papaya\Template\Simple\AST\Nodes(
        array(
          new \Papaya\Template\Simple\AST\Node\Output('foo')
        )
      ),
      $parser->parse()
    );
  }

  /**
   * @covers \Papaya\Template\Simple\Parser\Output::parse
   */
  public function testWithWhitespace() {
    $tokens = array(
      new \Papaya\Template\Simple\Scanner\Token(\Papaya\Template\Simple\Scanner\Token::WHITESPACE, 0, "\n")
    );
    $parser = new Output($tokens);
    $this->assertEquals(
      new \Papaya\Template\Simple\AST\Nodes(
        array(
          new \Papaya\Template\Simple\AST\Node\Output("\n")
        )
      ),
      $parser->parse()
    );
  }

  /**
   * @covers \Papaya\Template\Simple\Parser\Output::parse
   */
  public function testWithSeveralOutputTokensOptimizesAst() {
    $tokens = array(
      new \Papaya\Template\Simple\Scanner\Token(\Papaya\Template\Simple\Scanner\Token::TEXT, 0, 'foo'),
      new \Papaya\Template\Simple\Scanner\Token(\Papaya\Template\Simple\Scanner\Token::WHITESPACE, 3, "\n"),
      new \Papaya\Template\Simple\Scanner\Token(\Papaya\Template\Simple\Scanner\Token::TEXT, 4, 'bar'),
    );
    $parser = new Output($tokens);
    $this->assertEquals(
      new \Papaya\Template\Simple\AST\Nodes(
        array(
          new \Papaya\Template\Simple\AST\Node\Output("foo\nbar")
        )
      ),
      $parser->parse()
    );
  }

  /**
   * @covers \Papaya\Template\Simple\Parser\Output::parse
   */
  public function testWithValue() {
    $tokens = array(
      new \Papaya\Template\Simple\Scanner\Token(
        \Papaya\Template\Simple\Scanner\Token::VALUE_NAME, 0, '/*$foo*/'
      ),
      new \Papaya\Template\Simple\Scanner\Token(
        \Papaya\Template\Simple\Scanner\Token::WHITESPACE, 6, ' '
      ),
      new \Papaya\Template\Simple\Scanner\Token(
        \Papaya\Template\Simple\Scanner\Token::VALUE_DEFAULT, 7, 'bar'
      )
    );
    $parser = new Output($tokens);
    $this->assertEquals(
      new \Papaya\Template\Simple\AST\Nodes(
        array(
          new \Papaya\Template\Simple\AST\Node\Value('foo', 'bar')
        )
      ),
      $parser->parse()
    );
  }
}
