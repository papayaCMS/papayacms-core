<?php
/**
* This configuration storage load the module option records using {@see PapayaContentModuleOptions}
* by the module guid and maps them into an associative array.
*
* @copyright 2010 by papaya Software GmbH - All rights reserved.
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
* @subpackage Plugins
* @version $Id: Storage.php 36791 2012-03-05 17:07:07Z weinert $
*/

/**
* This configuration storage load the module option records using {@see PapayaContentModuleOptions}
* by the module guid and maps them into an associative array.
*
* @package Papaya-Library
* @subpackage Plugins
*/
class PapayaPluginOptionStorage extends PapayaObject
  implements PapayaConfigurationStorage {

  private $_guid;
  private $_options = NULL;

  /**
  * Create storage object and store module guid
  *
  * @param string $guid
  */
  public function __construct($guid) {
    $this->_guid = PapayaUtilStringGuid::toLower($guid);
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
    return new ArrayIterator($result);
  }

  /**
  * Getter/Setter: Options database encapsultation subobject
  *
  * @param PapayaContentModuleOptions $options
  * @return PapayaContentModuleOptions
  */
  public function options(PapayaContentModuleOptions $options = NULL) {
    if (isset($options)) {
      $this->_options = $options;
    } elseif (is_null($this->_options)) {
      $this->_options = new PapayaContentModuleOptions();
    }
    return $this->_options;
  }
}