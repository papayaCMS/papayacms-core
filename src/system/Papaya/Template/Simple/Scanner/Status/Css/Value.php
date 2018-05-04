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

/**
* After a simple template value name comment, this status looks for the default value.
*
* @package Papaya-Library
* @subpackage Template
*/
class PapayaTemplateSimpleScannerStatusCssValue extends PapayaTemplateSimpleScannerStatus {

  private $_patterns = array(
    '(\\s+)S' => \PapayaTemplateSimpleScannerToken::WHITESPACE,
    '([^\\s;,!\\r\\n]+)S' => \PapayaTemplateSimpleScannerToken::VALUE_DEFAULT
  );

  /**
   * Match the patterns against the buffer string, return a new token if it is found at
   * offset position.
   *
   * @param string $buffer
   * @param integer $offset
   * @return NULL|\PapayaTemplateSimpleScannerToken
   */
  public function getToken($buffer, $offset) {
    return $this->matchPatterns($buffer, $offset, $this->_patterns);
  }

  /**
   * Return TRUE if the token is a default value - which is the only possible token
   * in this status for now.
   *
   * @param \PapayaTemplateSimpleScannerToken $token
   * @return boolean
   */
  public function isEndToken($token) {
    return ($token->type == \PapayaTemplateSimpleScannerToken::VALUE_DEFAULT);
  }
}
