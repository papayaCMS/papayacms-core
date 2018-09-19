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
namespace Papaya\Template\Simple\Scanner\Status;

/**
 * Look for template tokens inside a CSS string
 *
 * @package Papaya-Library
 * @subpackage Template
 */
class CSS extends \Papaya\Template\Simple\Scanner\Status {
  private $_patterns = [
    '(/\\*\\$[^*\\r\\n]+\*/)S' => \Papaya\Template\Simple\Scanner\Token::VALUE_NAME,
    '(\\s+)S' => \Papaya\Template\Simple\Scanner\Token::WHITESPACE,
    '(/\\*)S' => \Papaya\Template\Simple\Scanner\Token::COMMENT_START,
    '(([^/\\s]+|/[^*\\s]+|(/$))+)S' => \Papaya\Template\Simple\Scanner\Token::TEXT,
  ];

  /**
   * Match the patterns against the buffer string, return a new token if it is found at
   * offset position
   *
   * @param string $buffer
   * @param int $offset
   *
   * @return null|\Papaya\Template\Simple\Scanner\Token
   */
  public function getToken($buffer, $offset) {
    return $this->matchPatterns($buffer, $offset, $this->_patterns);
  }

  /**
   * If a token name is found, switch to value status, expecting a css value that can
   * be replaced (or not) by the defined value.
   *
   * @param \Papaya\Template\Simple\Scanner\Token
   *
   * @return \Papaya\Template\Simple\Scanner\Status|null
   */
  public function getNewStatus($token) {
    switch ($token->type) {
      case \Papaya\Template\Simple\Scanner\Token::VALUE_NAME :
        return new \Papaya\Template\Simple\Scanner\Status\CSS\Value();
      case \Papaya\Template\Simple\Scanner\Token::COMMENT_START :
        return new \Papaya\Template\Simple\Scanner\Status\CSS\Comment();
    }
    return;
  }
}
