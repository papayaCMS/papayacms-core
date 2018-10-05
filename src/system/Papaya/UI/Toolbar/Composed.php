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

use Papaya\UI;
use Papaya\Utility;
use Papaya\XML;

/**
 * A toolbar that consists of multiple sets of elements. The sets are not visible in
 * the xml output.
 *
 * The main reason for this object is the possibility to provided an order of the elements sets
 * independent from the order the elements are added to the toolbar. Like adding
 * action buttons before navigation button but have the navigation first in the toolbar.
 *
 * @package Papaya-Library
 * @subpackage UI
 */
class Composed extends UI\Control {
  /**
   * The internal set list
   *
   * @var Collection[]
   */
  private $_groups = [];

  /**
   * Internal member variable vor the toolbar subobject.
   *
   * @var UI\Toolbar
   */
  private $_toolbar;

  /**
   * Create the control and define the available sets
   *
   * @param array $sets
   */
  public function __construct(array $sets) {
    $this->setNames($sets);
  }

  /**
   * Define the set by a list of names.
   *
   * @param array $groups
   *
   * @throws \InvalidArgumentException
   */
  public function setNames(array $groups) {
    $this->_groups = [];
    if (empty($groups)) {
      throw new \InvalidArgumentException('No sets defined');
    }
    foreach ($groups as $index => $name) {
      $name = Utility\Text\Identifier::toUnderscoreLower($name);
      if (empty($name)) {
        throw new \InvalidArgumentException(
          \sprintf('Invalid set name "%s" in index "%s".', $name, $index)
        );
      }
      $this->_groups[$name] = NULL;
    }
  }

  /**
   * Append the existing toolbar to the parent xml element and set the position attribute.
   * Sets without elements will not be added.
   *
   * @param XML\Element $parent
   */
  public function appendTo(XML\Element $parent) {
    $toolbar = $this->toolbar();
    $toolbar->elements->clear();
    foreach ($this->_groups as $group) {
      if (NULL !== $group) {
        $toolbar->elements[] = $group;
      }
    }
    $parent->append($toolbar);
  }

  /**
   * The toolbar to sets get appended to.
   *
   * @param UI\Toolbar $toolbar
   *
   * @return UI\Toolbar
   */
  public function toolbar(UI\Toolbar $toolbar = NULL) {
    if (NULL !== $toolbar) {
      $this->_toolbar = $toolbar;
    } elseif (NULL === $this->_toolbar) {
      $this->_toolbar = new UI\Toolbar();
      $this->_toolbar->papaya($this->papaya());
    }
    return $this->_toolbar;
  }

  /**
   * Return the toolbar set name is defined. The toolbar set does not need to exists at this point.
   *
   * @param string $name
   *
   * @return bool
   */
  public function __isset($name) {
    $name = Utility\Text\Identifier::toUnderscoreLower($name);
    return \array_key_exists($name, $this->_groups);
  }

  /**
   * Return the toolbar set with the given name. If the position name is invalid an excpetion is
   * thrown.
   *
   * @throws \UnexpectedValueException
   *
   * @param string $name
   *
   * @return Collection
   */
  public function __get($name) {
    $name = Utility\Text\Identifier::toUnderscoreLower($name);
    if (\array_key_exists($name, $this->_groups)) {
      if (!isset($this->_groups[$name])) {
        $this->_groups[$name] = $set = new Collection();
        $set->papaya($this->papaya());
      }
      return $this->_groups[$name];
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
   *
   * @param string $name
   * @param Collection $value
   */
  public function __set($name, $value) {
    Utility\Constraints::assertInstanceOf(Collection::class, $value);
    $name = Utility\Text\Identifier::toUnderscoreLower($name);
    if (\array_key_exists($name, $this->_groups)) {
      $this->_groups[$name] = $value;
    } else {
      throw new \UnexpectedValueException(
        'Invalid toolbar set requested.'
      );
    }
  }
}
