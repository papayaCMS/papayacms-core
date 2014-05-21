<?php
/**
* Ast node containing an output.
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
* @version $Id: Output.php 37631 2012-11-05 15:14:22Z weinert $
*/

/**
* Ast node containing an output.
*
* @package Papaya-Library
* @subpackage Template
*
* @property-read string $text
*/
class PapayaTemplateSimpleAstNodeOutput extends PapayaTemplateSimpleAstNode {

  protected $_text = '';

  /**
   * Create node and store text content
   *
   * @param string $text
   */
  public function __construct($text) {
    $this->_text = $text;
  }

  /**
   * Append some text to the already stored output text.
   *
   * @param string $text
   */
  public function append($text) {
    $this->_text .= $text;
  }
}