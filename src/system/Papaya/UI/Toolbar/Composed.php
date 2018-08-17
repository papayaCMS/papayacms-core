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
 * A toolbar that consists of multiple sets of elements. The sets are not visisble in
 * the xml output.
 *
 * The main reson for this object is the possibility to provided an order of the elements sets
 * independent from the order the elements are added to the toolbar. Like adding
 * action buttons before navigation button but have the navigation first in the toolbar.
 *
 * @package Papaya-Library
 * @subpackage UI
 */
class Composed extends \Papaya\UI\Control {

  /**
   * The internal set list
   *
   * @var \Papaya\UI\Toolbar\Collection[]
   */
  private $_sets = array();

  /**
   * Internal member variable vor the toolbar subobject.
   *
   * @var \Papaya\UI\Toolbar
   */
  private $_toolbar = NULL;

  /**
   * Create the control and sefine the available sets
   *
   * @param array $sets
   */
  public function __construct(array $sets) {
    $this->setNames($sets);
  }

  /**
   * Define the set by a list of names.
   *
   * @param array $sets
   * @throws \InvalidArgumentException
   */
  public function setNames(array $sets) {
    $this->_sets = array();
    if (empty($sets)) {
      throw new \InvalidArgumentException('No sets defined');
    }
    foreach ($sets as $index => $name) {
      $name = \Papaya\Utility\Text\Identifier::toUnderscoreLower($name);
      if (empty($name)) {
        throw new \InvalidArgumentException(
          sprintf('Invalid set name "%s" in index "%s".', $name, $index)
        );
      }
      $this->_sets[$name] = NULL;
    }
  }

  /**
   * Append the existing toolbar to the parent xml element and set the position attribute.
   * Sets without elements will not be added.
   *
   * @param \Papaya\XML\Element $parent
   */
  public function appendTo(\Papaya\XML\Element $parent) {
    $toolbar = $this->toolbar();
    $toolbar->elements->clear();
    foreach ($this->_sets as $set) {
      if (isset($set)) {
        $toolbar->elements[] = $set;
      }
    }
    $parent->append($toolbar);
  }

  /**
   * The toolbar to sets get appended to.
   *
   * @param \Papaya\UI\Toolbar $toolbar
   * @return \Papaya\UI\Toolbar
   */
  public function toolbar(\Papaya\UI\Toolbar $toolbar = NULL) {
    if (isset($toolbar)) {
      $this->_toolbar = $toolbar;
    } elseif (is_null($this->_toolbar)) {
      $this->_toolbar = new \Papaya\UI\Toolbar();
      $this->_toolbar->papaya($this->papaya());
    }
    return $this->_toolbar;
  }

  /**
   * Return the toolbar set name is defined. The toolbar set does not need to exists at this point.
   *
   * @param string $name
   * @return boolean
   */
  public function __isset($name) {
    $name = \Papaya\Utility\Text\Identifier::toUnderscoreLower($name);
    return array_key_exists($name, $this->_sets);
  }

  /**
   * Return the toolbar set with the given name. If the position name is invalid an excpetion is
   * thrown.
   *
   * @throws \UnexpectedValueException
   * @param string $name
   * @return \Papaya\UI\Toolbar\Collection
   */
  public function __get($name) {
    $name = \Papaya\Utility\Text\Identifier::toUnderscoreLower($name);
    if (array_key_exists($name, $this->_sets)) {
      if (!isset($this->_sets[$name])) {
        $this->_sets[$name] = $set = new \Papaya\UI\Toolbar\Collection();
        $set->papaya($this->papaya());
      }
      return $this->_sets[$name];
    }
    throw new \UnexpectedValueException(
      'Invalid toolbar set requested.'
    );
  }

  /**
   * Set the toolbar set defined by the name.
   * If the position name is invalid an excpetion is thrown.
   *
   * @throws \UnexpectedValueException
   * @param string $name
   * @param \Papaya\UI\Toolbar\Collection $value
   */
  public function __set($name, $value) {
    \Papaya\Utility\Constraints::assertInstanceOf(\Papaya\UI\Toolbar\Collection::class, $value);
    $name = \Papaya\Utility\Text\Identifier::toUnderscoreLower($name);
    if (array_key_exists($name, $this->_sets)) {
      $this->_sets[$name] = $value;
    } else {
      throw new \UnexpectedValueException(
        'Invalid toolbar set requested.'
      );
    }
  }
}
