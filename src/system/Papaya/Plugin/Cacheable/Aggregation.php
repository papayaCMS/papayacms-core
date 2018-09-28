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
namespace Papaya\Plugin\Cacheable;

use Papaya\Cache;

/**
 * Define the plugin output as cacheable. A cache definition allows to get
 * the cache values, but also the sources the are from.
 *
 * @package Papaya-Library
 * @subpackage Plugins
 */
trait Aggregation {
  /**
   * @var Cache\Identifier\Definition
   */
  private $_cacheDefinition;

  /**
   * Provide the cache definition for the output.
   *
   * @param Cache\Identifier\Definition $definition
   *
   * @return Cache\Identifier\Definition
   */
  public function cacheable(Cache\Identifier\Definition $definition = NULL) {
    if (NULL !== $definition) {
      $this->_cacheDefinition = $definition;
    } elseif (NULL === $this->_cacheDefinition) {
      $this->_cacheDefinition = $this->createCacheDefinition();
    }
    return $this->_cacheDefinition;
  }

  /**
   * @return Cache\Identifier\Definition
   */
  abstract public function createCacheDefinition();
}
