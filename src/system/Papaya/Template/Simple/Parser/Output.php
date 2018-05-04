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
* Parser status "output" expects text (to output) and template element start tokens
*
* @package Papaya-Library
* @subpackage Template
*/
class PapayaTemplateSimpleParserOutput extends PapayaTemplateSimpleParser {

  /**
   * Templates start in ouput mode.
   *
   * TEXT and WHITESPACE tokens are added as output nodes.
   *
   * VALUE_NAME identifies a value node and an VALUE_DEFAULT ist expected next. WHITESPACE
   * between VALUE_NAME and VALUE_DEFAULT are ignored.
   *
   * @return PapayaTemplateSimpleAstNodes
   */
  public function parse() {
    $nodes = new \PapayaTemplateSimpleAstNodes();
    while (!$this->endOfTokens()) {
      $currentToken = $this->read(
        array(
          PapayaTemplateSimpleScannerToken::TEXT,
          PapayaTemplateSimpleScannerToken::WHITESPACE,
          PapayaTemplateSimpleScannerToken::VALUE_NAME,
          PapayaTemplateSimpleScannerToken::COMMENT_START,
          PapayaTemplateSimpleScannerToken::COMMENT_END
        )
      );
      switch ($currentToken->type) {
      case PapayaTemplateSimpleScannerToken::TEXT :
      case PapayaTemplateSimpleScannerToken::WHITESPACE :
      case PapayaTemplateSimpleScannerToken::COMMENT_START :
      case PapayaTemplateSimpleScannerToken::COMMENT_END :
        if (($count = count($nodes)) > 0 &&
            ($node = $nodes[$count - 1])  &&
            $node instanceof \PapayaTemplateSimpleAstNodeOutput) {
          $node->append($currentToken->content);
        } else {
          $nodes[] = new \PapayaTemplateSimpleAstNodeOutput($currentToken->content);
        }
        break;
      case PapayaTemplateSimpleScannerToken::VALUE_NAME :
        $valueName = preg_replace('(^/\\*\\$?|\\*/$)', '', $currentToken->content);
        $this->ignore(PapayaTemplateSimpleScannerToken::WHITESPACE);
        $currentToken = $this->read(PapayaTemplateSimpleScannerToken::VALUE_DEFAULT);
        $defaultValue = $currentToken->content;
        $nodes[] = new \PapayaTemplateSimpleAstNodeValue($valueName, $defaultValue);
        break;
      }
    }
    return $nodes;
  }
}
