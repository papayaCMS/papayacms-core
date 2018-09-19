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
namespace Papaya\Plugin\Configurable;

/**
 * This a standard implementation for a configurable plugin. It
 * add a configuration() getter/setter method to the plugin.
 *
 * It contains information from the output filter/configuration.
 *
 * @package Papaya-Library
 * @subpackage Plugins
 */
trait Aggregation {
  /**
   * @var \Papaya\BaseObject\Parameters
   */
  private $_configuration;

  /**
   * The configuration is an {@see ArrayObject} containing options that can affect the
   * execution of other methods (like appendTo()).
   *
   * @see \Papaya\Plugin\Configurable::configuration()
   *
   * @param \Papaya\BaseObject\Parameters $configuration
   *
   * @return \Papaya\BaseObject\Parameters
   */
  public function configuration(\Papaya\BaseObject\Parameters $configuration = NULL) {
    if (NULL !== $configuration) {
      $this->_configuration = $configuration;
    } elseif (NULL === $this->_configuration) {
      $this->_configuration = new \Papaya\BaseObject\Parameters();
    }
    return $this->_configuration;
  }
}
