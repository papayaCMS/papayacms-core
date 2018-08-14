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
 * This configuration storage load the module option records using
 * {@see \Papaya\Content\Module\Options} by the module guid and maps them into an associative array.
 *
 * @package Papaya-Library
 * @subpackage Plugins
 */
class Storage extends \Papaya\Application\BaseObject
  implements \Papaya\Configuration\Storage {

  private $_guid;
  private $_options;

  /**
   * Create storage object and store module guid
   *
   * @param string $guid
   */
  public function __construct($guid) {
    $this->_guid = \Papaya\Utility\Text\Guid::toLower($guid);
  }

  /**
   * Load module options from database
   *
   * @return boolean
   */
  public function load() {
    return $this->options()->load(array('guid' => $this->_guid));
  }

  /**
   * Map and return module options
   *
   * @return array
   */
  public function getIterator() {
    $result = array();
    foreach ($this->options() as $option) {
      $result[$option['name']] = $option['value'];
    }
    return new \ArrayIterator($result);
  }

  /**
   * Getter/Setter: Options database encapsultation subobject
   *
   * @param \Papaya\Content\Module\Options $options
   * @return \Papaya\Content\Module\Options
   */
  public function options(\Papaya\Content\Module\Options $options = NULL) {
    if (isset($options)) {
      $this->_options = $options;
    } elseif (is_null($this->_options)) {
      $this->_options = new \Papaya\Content\Module\Options();
    }
    return $this->_options;
  }
}
