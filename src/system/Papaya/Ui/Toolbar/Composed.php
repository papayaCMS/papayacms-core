<?php
/**
* A toolbar that consists of multiple sets of elements. The sets are not visisble in
* the xml output.
*
* @copyright 2011 by papaya Software GmbH - All rights reserved.
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
* @subpackage Ui
* @version $Id: Composed.php 39429 2014-02-27 20:14:26Z weinert $
*/

/**
* A toolbar that consists of multiple sets of elements. The sets are not visisble in
* the xml output.
*
* The main reson for this object is the possibility to provided an order of the elements sets
* independent from the order the elements are added to the toolbar. Like adding
* action buttons before navigation button but have the navigation first in the toolbar.
*
* @package Papaya-Library
* @subpackage Ui
*/
class PapayaUiToolbarComposed extends PapayaUiControl {

  /**
  * The internal set list
  *
  * @var array(string=>NULL|PapayaUiToolbarSet,...)
  */
  private $_sets = array();

  /**
  * Internal member variable vor the toolbar subobject.
  *
  * @var PapayaUiToolbar
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
   * @throws InvalidArgumentException
   */
  public function setNames(array $sets) {
    $this->_sets = array();
    if (empty($sets)) {
      throw new InvalidArgumentException('No sets defined');
    }
    foreach ($sets as $index => $name) {
      $name = PapayaUtilStringIdentifier::toUnderscoreLower($name);
      if (empty($name)) {
        throw new InvalidArgumentException(
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
  * @param PapayaXmlElement $parent
  */
  public function appendTo(PapayaXmlElement $parent) {
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
  * @param PapayaUiToolbar $toolbar
  * @return PapayaUiToolbar
  */
  public function toolbar(PapayaUiToolbar $toolbar = NULL) {
    if (isset($toolbar)) {
      $this->_toolbar = $toolbar;
    } elseif (is_null($this->_toolbar)) {
      $this->_toolbar = new PapayaUiToolbar();
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
    $name = PapayaUtilStringIdentifier::toUnderscoreLower($name);
    return array_key_exists($name, $this->_sets);
  }

  /**
  * Return the toolbar set with the given name. If the position name is invalid an excpetion is
  * thrown.
  *
  * @throws UnexpectedValueException
  * @param string $name
  * @return PapayaUiToolbarSet
  */
  public function __get($name) {
    $name = PapayaUtilStringIdentifier::toUnderscoreLower($name);
    if (array_key_exists($name, $this->_sets)) {
      if (!isset($this->_sets[$name])) {
        $this->_sets[$name] = $set = new PapayaUiToolbarSet();
        $set->papaya($this->papaya());
      }
      return $this->_sets[$name];
    }
    throw new UnexpectedValueException(
      'Invalid toolbar set requested.'
    );
  }

  /**
  * Set the toolbar set defined by the name.
  * If the position name is invalid an excpetion is thrown.
  *
  * @throws UnexpectedValueException
  * @param string $name
  * @param PapayaUiToolbarSet $value
  */
  public function __set($name, $value) {
    PapayaUtilConstraints::assertInstanceOf('PapayaUiToolbarSet', $value);
    $name = PapayaUtilStringIdentifier::toUnderscoreLower($name);
    if (array_key_exists($name, $this->_sets)) {
      $this->_sets[$name] = $value;
    } else {
      throw new UnexpectedValueException(
        'Invalid toolbar set requested.'
      );
    }
  }
}