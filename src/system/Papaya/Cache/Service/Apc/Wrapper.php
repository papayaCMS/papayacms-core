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

namespace Papaya\Cache\Service\Apc;
/**
 * APC function wrapper
 *
 * @package Papaya-Library
 * @subpackage Cache
 */
class Wrapper {

  /**
   * APC is available
   *
   * @return boolean
   */
  public function available() {
    return extension_loaded('apc');
  }

  /**
   * Cache a variable in the data store
   *
   * @codeCoverageIgnore
   * @param string $cacheId
   * @param mixed $data
   * @param integer $expires
   * @return boolean
   */
  public function store($cacheId, $data, $expires) {
    return apc_store($cacheId, $data, $expires);
  }

  /**
   * Fetch a stored variable from the cache
   *
   * @codeCoverageIgnore
   * @param string $cacheId
   * @return mixed
   */
  public function fetch($cacheId) {
    $data = apc_fetch($cacheId, $success);
    return $success ? $data : NULL;
  }

  /**
   * Clears the user/system cache.
   *
   * @codeCoverageIgnore
   * @param string $cacheType
   * @return boolean
   */
  public function clearCache($cacheType) {
    return apc_clear_cache($cacheType);
  }
}
