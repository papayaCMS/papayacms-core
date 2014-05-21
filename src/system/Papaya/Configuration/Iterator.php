<?php
/**
* Iterator for the PapayaConfiguration class.
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
* @subpackage Configuration
* @version $Id: Iterator.php 36051 2011-08-05 16:32:54Z weinert $
*/

/**
* Iterator for the PapayaConfiguration class.
*
* @package Papaya-Library
* @subpackage Configuration
*/
class PapayaConfigurationIterator implements Iterator {

  /**
  * option names
  * @var array(string)
  */
  private $_names = array();

  /**
  * configuration object
  * @var PapayaConfiguration
  */
  private $_configuration = NULL;

  /**
  * Current iterator position
  * @var integer
  */
  private $_position = 0;

  /**
  * iterator maximum
  * @var integer
  */
  private $_maximum = 0;

  /**
  * Create object, store names and configuration object
  *
  * @param array $names
  * @param PapayaConfiguration $configuration
  */
  public function __construct(array $names, PapayaConfiguration $configuration) {
    $this->_names = array_values($names);
    $this->_maximum = count($names) - 1;
    $this->_configuration = $configuration;
  }

  /**
  * Reset iterator position
  */
  public function rewind() {
    $this->_position = 0;
  }

  /**
  * Return option value form current iterator position
  */
  public function current() {
    if ($this->_position <= $this->_maximum) {
      return $this->_configuration->get($this->_names[$this->_position]);
    }
    return NULL;
  }

  /**
  * Return option name form current iterator position
  */
  public function key() {
    return $this->_names[$this->_position];
  }

  /**
  * Move iterator to next position and return option value
  */
  public function next() {
    ++$this->_position;
    return $this->current();
  }

  /**
  * Return if current iterator position is valid
  */
  public function valid() {
    return $this->_position <= $this->_maximum;
  }
}