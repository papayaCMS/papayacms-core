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

/**
* Array Access implementation for phrase group objects.
*
* @package Papaya-Library
* @subpackage Phrases
*/
class PapayaPhrasesGroups implements \ArrayAccess {

  /**
   * @var array
   */
  private $_groups = array();

  /**
   * @var \Papaya\Phrases
   */
  private $_phrases = NULL;

  public function __construct(\Papaya\Phrases $phrases) {
    $this->_phrases = $phrases;
  }

  /**
   * @param string $name
   * @return \PapayaPhrasesGroup
   */
  public function get($name) {
    return $this->offsetGet($name);
  }

  /**
   * @param string $name
   * @return boolean
   */
  public function offsetExists($name) {
    return array_key_exists($name, $this->_groups);
  }

  /**
   * @param string $name
   * @return \PapayaPhrasesGroup
   */
  public function offsetGet($name) {
    if (!isset($this->_groups[$name])) {
      $this->_groups[$name] = new \PapayaPhrasesGroup($this->_phrases, $name);
    }
    return $this->_groups[$name];
  }

  /**
   * @param string $name
   * @param \PapayaPhrasesGroup $group
   */
  public function offsetSet($name, $group) {
    \PapayaUtilConstraints::assertInstanceOf(\PapayaPhrasesGroup::class, $group);
    $this->_groups[$name] = $group;
  }

  /**
   * @param string $name
   */
  public function offsetUnset($name) {
    if (isset($this->_groups[$name])) {
      unset($this->_groups[$name]);
    }
  }
}
