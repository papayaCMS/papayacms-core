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
namespace Papaya\Template\Simple\Scanner\Status\CSS;

/**
 * After a simple template value name comment, this status looks for the default value.
 *
 * @package Papaya-Library
 * @subpackage Template
 */
class Value extends \Papaya\Template\Simple\Scanner\Status {
  private $_patterns = [
    '(\\s+)S' => \Papaya\Template\Simple\Scanner\Token::WHITESPACE,
    '([^\\s;,!\\r\\n]+)S' => \Papaya\Template\Simple\Scanner\Token::VALUE_DEFAULT
  ];

  /**
   * Match the patterns against the buffer string, return a new token if it is found at
   * offset position.
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
   * Return TRUE if the token is a default value - which is the only possible token
   * in this status for now.
   *
   * @param \Papaya\Template\Simple\Scanner\Token $token
   *
   * @return bool
   */
  public function isEndToken($token) {
    return (\Papaya\Template\Simple\Scanner\Token::VALUE_DEFAULT == $token->type);
  }
}
