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
namespace Papaya\Content\Page\Publication;

use Papaya\Cache;
use Papaya\Content;

/**
 * Load status informations of a page publication.
 *
 * @package Papaya-Library
 * @subpackage Content
 */
class Status extends Content\Page\Status {
  /**
   * Get status from page publication
   *
   * @var string
   */
  protected $_tableName = Content\Tables::PAGE_PUBLICATIONS;

  /**
   * Query data cache.
   *
   * @var Cache\Service
   */
  private $_cache;

  /**
   * Cache the database result to avoid to many small queries for each page.
   *
   * @param int $id
   *
   * @return bool
   */
  public function load($id) {
    $expires = $this->papaya()->options->get(\Papaya\Configuration\CMS::CACHE_DATA_TIME, 0);
    if (($cache = $this->cache()) &&
      ($content = $cache->read('pages', 'status', $id, $expires))) {
      $this->assign(\unserialize($content));
      return TRUE;
    }
    $result = parent::load($id);
    if ($cache) {
      $cache->write('pages', 'status', $id, \serialize($this->toArray()), $expires);
    }
    return $result;
  }

  /**
   * Getter/Setter for cache object, fetches the system data cache if not set.
   *
   * @param Cache\Service $cache
   *
   * @return false|Cache\Service
   */
  public function cache(Cache\Service $cache = NULL) {
    if (NULL !== $cache) {
      $this->_cache = $cache;
    } elseif (NULL === $this->_cache) {
      /* @noinspection PhpParamsInspection */
      $this->_cache = Cache::get(Cache::DATA, $this->papaya()->options);
    }
    return $this->_cache;
  }
}
