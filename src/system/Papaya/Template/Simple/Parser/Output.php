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
/**
 * Parser status "output" expects text (to output) and template element start tokens
 *
 * @package Papaya-Library
 * @subpackage Template
 */
class Output extends \Papaya\Template\Simple\Parser {

  /**
   * Templates start in ouput mode.
   *
   * TEXT and WHITESPACE tokens are added as output nodes.
   *
   * VALUE_NAME identifies a value node and an VALUE_DEFAULT ist expected next. WHITESPACE
   * between VALUE_NAME and VALUE_DEFAULT are ignored.
   *
   * @return \Papaya\Template\Simple\AST\Nodes
   * @throws \Papaya\Template\Simple\Exception
   */
  public function parse() {
    $nodes = new \Papaya\Template\Simple\AST\Nodes();
    while (!$this->endOfTokens()) {
      $currentToken = $this->read(
        array(
          \Papaya\Template\Simple\Scanner\Token::TEXT,
          \Papaya\Template\Simple\Scanner\Token::WHITESPACE,
          \Papaya\Template\Simple\Scanner\Token::VALUE_NAME,
          \Papaya\Template\Simple\Scanner\Token::COMMENT_START,
          \Papaya\Template\Simple\Scanner\Token::COMMENT_END
        )
      );
      switch ($currentToken->type) {
        case \Papaya\Template\Simple\Scanner\Token::TEXT :
        case \Papaya\Template\Simple\Scanner\Token::WHITESPACE :
        case \Papaya\Template\Simple\Scanner\Token::COMMENT_START :
        case \Papaya\Template\Simple\Scanner\Token::COMMENT_END :
          if (($count = count($nodes)) > 0 &&
            ($node = $nodes[$count - 1]) &&
            $node instanceof \Papaya\Template\Simple\AST\Node\Output) {
            $node->append($currentToken->content);
          } else {
            $nodes[] = new \Papaya\Template\Simple\AST\Node\Output($currentToken->content);
          }
        break;
        case \Papaya\Template\Simple\Scanner\Token::VALUE_NAME :
          $valueName = preg_replace('(^/\\*\\$?|\\*/$)', '', $currentToken->content);
          $this->ignore(\Papaya\Template\Simple\Scanner\Token::WHITESPACE);
          $currentToken = $this->read(\Papaya\Template\Simple\Scanner\Token::VALUE_DEFAULT);
          $defaultValue = $currentToken->content;
          $nodes[] = new \Papaya\Template\Simple\AST\Node\Value($valueName, $defaultValue);
        break;
      }
    }
    return $nodes;
  }
}
