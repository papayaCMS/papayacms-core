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

namespace Papaya\Ui;
/**
 * A toolbar gui control. This is a list of elements like buttons, separators and selects.
 *
 * @package Papaya-Library
 * @subpackage Ui
 *
 * @property Toolbar\Elements $elements
 */
class Toolbar extends Control {

  /**
   * menu elements collection
   *
   * @var NULL|Toolbar\Elements
   */
  protected $_elements;

  /**
   * Declare public properties
   *
   * @var array
   */
  protected $_declaredProperties = array(
    'elements' => array('elements', 'elements')
  );

  /**
   * Getter/setter for elements collection
   *
   * @param Toolbar\Elements $elements
   * @return Toolbar\Elements
   */
  public function elements(Toolbar\Elements $elements = NULL) {
    if (NULL !== $elements) {
      $this->_elements = $elements;
      $this->_elements->owner($this);
    } elseif (NULL === $this->_elements) {
      $this->_elements = new Toolbar\Elements($this);
    }
    return $this->_elements;
  }

  /**
   * Append toolbar and elements and set identifier if available
   *
   * @param \Papaya\Xml\Element $parent
   * @return \Papaya\Xml\Element|NULL
   */
  public function appendTo(\Papaya\Xml\Element $parent) {
    if (count($this->elements()) > 0) {
      $toolbar = $parent->appendElement('toolbar');
      $this->elements()->appendTo($toolbar);
      return $toolbar;
    }
    return NULL;
  }

}
