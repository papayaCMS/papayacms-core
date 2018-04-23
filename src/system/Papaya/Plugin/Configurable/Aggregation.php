<?php
/**
 * This a standard implementation for a configurable plugin.
 *
 * @copyright 2018 by papaya Software GmbH - All rights reserved.
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
 */

/**
 * This a standard implementation for a configurable plugin. It
 * add a configuration() getter/setter method to the plugin.
 *
 * It contains information about the data and output filters.
 *
 * @package Papaya-Library
 * @subpackage Plugins
 */
trait PapayaPluginConfigurableAggregation {

  /**
   * @var PapayaObjectParameters
   */
  private $_configuration;


  /**
   * The configuration is an {@see ArrayObject} containing options that can affect the
   * execution of other methods (like appendTo()).
   *
   * @see PapayaPluginConfigurable::configuration()
   * @param PapayaObjectParameters $configuration
   * @return PapayaObjectParameters
   */
  public function configuration(PapayaObjectParameters $configuration = NULL) {
    if ($configuration !== NULL) {
      $this->_configuration = $configuration;
    } elseif (NULL === $this->_configuration) {
      $this->_configuration = new PapayaObjectParameters();
    }
    return $this->_configuration;
  }
}
