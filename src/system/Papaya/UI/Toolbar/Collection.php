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

namespace Papaya\UI\Toolbar;

/**
 * A menu element set. This is a sublist of menu elements like buttons.
 *
 * This allows to append the toolbar elements to a specific part of a toolbar (defined by the set)
 *
 * @package Papaya-Library
 * @subpackage UI
 *
 * @property \Papaya\UI\Toolbar\Elements $elements
 */
class Collection
  extends Element {
  /**
   * Group elements collection
   *
   * @var null|\Papaya\UI\Toolbar\Elements
   */
  protected $_elements;

  /**
   * Declare properties
   *
   * @var array
   */
  protected $_declaredProperties = [
    'elements' => ['elements', 'elements']
  ];

  /**
   * Getter/setter for elements collection
   *
   * @param \Papaya\UI\Toolbar\Elements $elements
   * @return \Papaya\UI\Toolbar\Elements
   */
  public function elements(\Papaya\UI\Toolbar\Elements $elements = NULL) {
    if (isset($elements)) {
      $this->_elements = $elements;
      $this->_elements->owner($this);
    }
    if (\is_null($this->_elements)) {
      $this->_elements = new \Papaya\UI\Toolbar\Elements($this);
      $this->_elements->allowGroups = FALSE;
    }
    return $this->_elements;
  }

  /**
   * Append group and elements to the output xml.
   *
   * @param \Papaya\XML\Element $parent
   */
  public function appendTo(\Papaya\XML\Element $parent) {
    $this->elements()->appendTo($parent);
    return;
  }
}
