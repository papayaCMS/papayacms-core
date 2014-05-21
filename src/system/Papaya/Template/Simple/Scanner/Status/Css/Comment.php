<?php
/**
* After a simple template value name comment, this status looks for the default value.
*
* @copyright 2012 by papaya Software GmbH - All rights reserved.
* @link http://www.papaya-cms.com/
* @license http://www.gnu.org/licenses/old-licenses/gpl-2.0.html GNU General Public License, version 2
*
* You can redistribute and/or modify this script under the terms of the GNU General Public
* License (GPL) version 2, provided that the copyright and license notes, including these
* lines, remain unmodified. papaya is distributed in the hope that it will be useful, but
* WITHOUT ANY WARRANTY; without even the implied warranty of MERCHANTABILITY or FITNESS
* FOR A PARTICULAR PURPOSE.
*
* @package Papaya-Library
* @subpackage Template
* @version $Id: Comment.php 37659 2012-11-09 15:03:17Z weinert $
*/

/**
* After a simple template value name comment, this status looks for the default value.
*
* @package Papaya-Library
* @subpackage Template
*/
class PapayaTemplateSimpleScannerStatusCssComment extends PapayaTemplateSimpleScannerStatus {

  private $_patterns = array(
    '(\\*/)S' => PapayaTemplateSimpleScannerToken::COMMENT_END,
    '(([^*]+|[*][^/]|[*]$)+)S' => PapayaTemplateSimpleScannerToken::TEXT
  );

  /**
   * Match the patterns against the buffer string, return a new token if it is found at
   * offset position.
   *
   * @param string $buffer
   * @param integer $offset
   * @return NULL|PapayaTemplateSimpleScannerToken
   */
  public function getToken($buffer, $offset) {
    return $this->matchPatterns($buffer, $offset, $this->_patterns);
  }

  /**
   * Return TRUE if the token is a comment end
   *
   * @param PapayaTemplateSimpleScannerToken $token
   * @return boolean
   */
  public function isEndToken($token) {
    return ($token->type == PapayaTemplateSimpleScannerToken::COMMENT_END);
  }
}
