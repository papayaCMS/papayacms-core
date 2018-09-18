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

namespace Papaya\Plugin\Option;

/**
 * This is a list of the plugin options, the option of each plugin in one separate object.
 *
 * @package Papaya-Library
 * @subpackage Plugins
 */
class Groups extends \Papaya\Application\BaseObject implements \ArrayAccess {
  private $_groups = [];

  /**
   * Check if it is already created.
   */
  public function offsetExists($guid) {
    $guid = \Papaya\Utility\Text\Guid::toLower($guid);
    return isset($this->_groups[$guid]);
  }

  /**
   * Get options object for a plugin, create it if needed
   *
   * @param string $guid
   * @return \Papaya\Configuration
   */
  public function offsetGet($guid) {
    $guid = \Papaya\Utility\Text\Guid::toLower($guid);
    $this->createLazy($guid);
    return $this->_groups[$guid];
  }

  /**
   * Set options object for a plugin.
   *
   * @param string $guid
   * @param \Papaya\Configuration $group
   */
  public function offsetSet($guid, $group) {
    $guid = \Papaya\Utility\Text\Guid::toLower($guid);
    \Papaya\Utility\Constraints::assertInstanceOf(\Papaya\Configuration::class, $group);
    $this->_groups[$guid] = $group;
  }

  /**
   * Remove option object for a given guid if it exists.
   *
   * @param string $guid
   */
  public function offsetUnset($guid) {
    $guid = \Papaya\Utility\Text\Guid::toLower($guid);
    if ($this->offsetExists($guid)) {
      unset($this->_groups[$guid]);
    }
  }

  /**
   * If the option object does not exist, create it and store it in the internal array.
   *
   * @param string $guid
   */
  private function createLazy($guid) {
    $guid = \Papaya\Utility\Text\Guid::toLower($guid);
    if (!isset($this->_groups[$guid])) {
      $this->_groups[$guid] = $options = new \Papaya\Plugin\Options($guid);
      $options->papaya($this->papaya());
    }
  }
}
