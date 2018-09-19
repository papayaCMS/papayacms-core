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
namespace Papaya\Template\Simple\AST\Node;

/**
 * AST node representing a node value placeholder with a default value
 *
 * @package Papaya-Library
 * @subpackage Template
 *
 * @property-read string $name
 * @property-read string $default
 */
class Value extends \Papaya\Template\Simple\AST\Node {
  protected $_name = '';

  protected $_default = '';

  /**
   * Create node
   *
   * @param string $name
   * @param string $default
   */
  public function __construct($name, $default) {
    $this->_name = $name;
    $this->_default = $default;
  }
}
