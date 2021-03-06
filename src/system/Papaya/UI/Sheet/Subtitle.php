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
namespace Papaya\UI\Sheet;

use Papaya\BaseObject\Interfaces\StringCastable;
use Papaya\UI;
use Papaya\XML;

/**
 * A single subtitle element for a sheet
 *
 * @package Papaya-Library
 * @subpackage UI
 */
class Subtitle extends UI\Control\Collection\Item {
  /**
   * @var string|StringCastable
   */
  private $_text;

  /**
   * @param string|StringCastable $text
   */
  public function __construct($text) {
    $this->_text = $text;
  }

  /**
   * @param XML\Element $parent
   * @return XML\Element
   */
  public function appendTo(XML\Element $parent) {
    return $parent->appendElement('subtitle', [], (string)$this->_text);
  }
}
