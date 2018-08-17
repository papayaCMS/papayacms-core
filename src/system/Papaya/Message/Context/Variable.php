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

namespace Papaya\Message\Context;
/**
 * Message context containing a variable
 *
 * This class is used for debugging variables.
 *
 * @package Papaya-Library
 * @subpackage Messages
 */
class Variable
  implements
  Interfaces\Text,
  Interfaces\XHTML {

  /**
   * The depth defines the recursion depth for the variable output
   *
   * @var integer
   */
  private $_depth = 3;

  /**
   * The context variable
   *
   * @var array
   */
  private $_variable = NULL;

  /**
   * Shorten string values to n bytes
   *
   * @var integer
   */
  private $_stringLength = 30;

  /**
   * Create variable context
   *
   * @param mixed $variable
   * @param integer $depth variable output depth
   * @param int $length
   */
  public function __construct($variable, $depth = 3, $length = 30) {
    $this->setDepth($depth);
    $this->setStringLength($length);
    $this->_variable = $variable;
  }

  /**
   * Check and set the depth. It must be greater then zero.
   *
   * @param integer $depth
   * @throws \InvalidArgumentException
   */
  public function setDepth($depth) {
    if (!is_int($depth) || $depth < 1) {
      throw new \InvalidArgumentException('$depth must be an integer greater zero.');
    }
    $this->_depth = $depth;
  }

  /**
   * Return the maximum recursion depth stored in the private property, used for additional visitors
   *
   * @return integer
   */
  public function getDepth() {
    return $this->_depth;
  }

  /**
   * Check and set the depth. It must be greater then zero.
   *
   * @param integer $length
   * @throws \InvalidArgumentException
   */
  public function setStringLength($length) {
    if (!is_int($length) || $length < 0) {
      throw new \InvalidArgumentException('$length must be an integer greater or equal zero.');
    }
    $this->_stringLength = $length;
  }

  /**
   * Return the string length stored in the private property, used for additional visitors
   *
   * @return integer
   */
  public function getStringLength() {
    return $this->_stringLength;
  }

  /**
   * Get a string representation of the variable
   *
   * @return string
   */
  public function asString() {
    $visitor = new Variable\Visitor\Text($this->_depth, $this->_stringLength);
    $this->acceptVisitor($visitor);
    return (string)$visitor;
  }

  /**
   * Get a string representation of the variable
   *
   * @return string
   */
  public function asXhtml() {
    $visitor = new Variable\Visitor\XHTML($this->_depth, $this->_stringLength);
    $this->acceptVisitor($visitor);
    return (string)$visitor;
  }

  /**
   * Visitor method
   *
   * @see \Papaya\Message\Context\Variable::asString()
   * @see \Papaya\Message\Context\Variable::asXhtml()
   *
   * @param Variable\Visitor $visitor
   */
  public function acceptVisitor(Variable\Visitor $visitor) {
    $visitor->visitVariable($this->_variable);
  }
}
