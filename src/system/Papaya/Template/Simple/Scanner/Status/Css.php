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
* Look for template tokens inside a CSS string
*
* @package Papaya-Library
* @subpackage Template
*/
class PapayaTemplateSimpleScannerStatusCss extends PapayaTemplateSimpleScannerStatus {

  private $_patterns = array(
    '(/\\*\\$[^*\\r\\n]+\*/)S' => PapayaTemplateSimpleScannerToken::VALUE_NAME,
    '(\\s+)S' => PapayaTemplateSimpleScannerToken::WHITESPACE,
    '(/\\*)S' => PapayaTemplateSimpleScannerToken::COMMENT_START,
    '(([^/\\s]+|/[^*\\s]+|(/$))+)S' => PapayaTemplateSimpleScannerToken::TEXT,
  );

  /**
   * Match the patterns against the buffer string, return a new token if it is found at
   * offset position
   *
   * @param string $buffer
   * @param integer $offset
   * @return NULL|PapayaTemplateSimpleScannerToken
   */
  public function getToken($buffer, $offset) {
    return $this->matchPatterns($buffer, $offset, $this->_patterns);
  }

  /**
   * If a token name is found, switch to value status, expecting a css value that can
   * be replaced (or not) by the defined value.
   *
   * @param PapayaTemplateSimpleScannerToken
   * @return PapayaTemplateSimpleScannerStatus|NULL
   */
  public function getNewStatus($token) {
    switch ($token->type) {
    case PapayaTemplateSimpleScannerToken::VALUE_NAME :
      return new \PapayaTemplateSimpleScannerStatusCssValue();
    case PapayaTemplateSimpleScannerToken::COMMENT_START :
      return new \PapayaTemplateSimpleScannerStatusCssComment();
    }
    return NULL;
  }
}
