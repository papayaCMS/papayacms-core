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
* Load status informations of a page publication.
*
* @package Papaya-Library
* @subpackage Content
*/
class PapayaContentPagePublicationStatus extends PapayaContentPageStatus {

  /**
  * Get status from page publication
  *
  * @var string
  */
  protected $_tableName = \PapayaContentTables::PAGE_PUBLICATIONS;

  /**
  * Query data cache.
  *
  * @var PapayaCacheService
  */
  private $_cache = NULL;

  /**
   * Cache the database result to avoid to many small queries for each page.
   *
   * @param integer $id
   * @return bool
   */
  public function load($id) {
    $expires = $this->papaya()->options->get('PAPAYA_CACHE_DATA_TIME', 0);
    if (($cache = $this->cache()) &&
        ($content = $cache->read('pages', 'status', $id, $expires))) {
      $this->assign(unserialize($content));
      return TRUE;
    } else {
      $result = parent::load($id);
      if ($cache) {
        $cache->write('pages', 'status', $id, serialize($this->toArray()), $expires);
      }
    }
    return $result;
  }

  /**
  * Getter/Setter for cache object, fetches the system data cache if not set.
  *
  * @param \PapayaCacheService $cache
  * @return FALSE|\PapayaCacheService
  */
  public function cache(\PapayaCacheService $cache = NULL) {
    if (isset($cache)) {
      $this->_cache = $cache;
    } elseif (is_null($this->_cache)) {
      /** @noinspection PhpParamsInspection */
      $this->_cache = \PapayaCache::get(\PapayaCache::DATA, $this->papaya()->options);
    }
    return $this->_cache;
  }
}
