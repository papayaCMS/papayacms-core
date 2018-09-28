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

use Papaya\Template\Simple;

/**
 * Parser status "output" expects text (to output) and template element start tokens
 *
 * @package Papaya-Library
 * @subpackage Template
 */
class Output extends Simple\Parser {
  /**
   * Templates start in ouput mode.
   *
   * TEXT and WHITESPACE tokens are added as output nodes.
   *
   * VALUE_NAME identifies a value node and an VALUE_DEFAULT ist expected next. WHITESPACE
   * between VALUE_NAME and VALUE_DEFAULT are ignored.
   *
   * @return Simple\AST\Nodes
   *
   * @throws \Papaya\Template\Simple\Exception
   */
  public function parse() {
    $nodes = new Simple\AST\Nodes();
    while (!$this->endOfTokens()) {
      $currentToken = $this->read(
        [
          Simple\Scanner\Token::TEXT,
          Simple\Scanner\Token::WHITESPACE,
          Simple\Scanner\Token::VALUE_NAME,
          Simple\Scanner\Token::COMMENT_START,
          Simple\Scanner\Token::COMMENT_END
        ]
      );
      switch ($currentToken->type) {
        case Simple\Scanner\Token::TEXT :
        case Simple\Scanner\Token::WHITESPACE :
        case Simple\Scanner\Token::COMMENT_START :
        case Simple\Scanner\Token::COMMENT_END :
          if (($count = \count($nodes)) > 0 &&
            ($node = $nodes[$count - 1]) &&
            $node instanceof Simple\AST\Node\Output
          ) {
            $node->append($currentToken->content);
          } else {
            $nodes[] = new Simple\AST\Node\Output($currentToken->content);
          }
        break;
        case Simple\Scanner\Token::VALUE_NAME :
          $valueName = \preg_replace('(^/\\*\\$?|\\*/$)', '', $currentToken->content);
          $this->ignore(Simple\Scanner\Token::WHITESPACE);
          $currentToken = $this->read(Simple\Scanner\Token::VALUE_DEFAULT);
          $defaultValue = $currentToken->content;
          $nodes[] = new Simple\AST\Node\Value($valueName, $defaultValue);
        break;
      }
    }
    return $nodes;
  }
}
