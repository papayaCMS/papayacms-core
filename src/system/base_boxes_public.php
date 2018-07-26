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
* Manage action boxes, database handling and parsing
*
* @package Papaya
* @subpackage Core
*/
class base_boxes_public extends base_boxes {
  /**
  * Papaya database table box
  * @var string $tableBox
  */
  var $tableBox = PAPAYA_DB_TBL_BOX_PUBLIC;
  /**
  * Papaya database table box translation
  * @var string $tableBoxTrans
  */
  var $tableBoxTrans = PAPAYA_DB_TBL_BOX_PUBLIC_TRANS;

  /**
  * Load Box by id
  *
  * @param integer $boxId
  * @param integer $lngId
  * @access public
  * @return boolean
  */
  function load($boxId, $lngId = 0) {
    $this->box = NULL;
    if ($boxId > 0) {
      $sql = "SELECT b.box_id, b.box_name, b.boxgroup_id,
                     b.box_created, b.box_modified,
                     b.box_deliverymode,
                     b.box_cachemode, b.box_cachetime,
                     b.box_expiresmode, b.box_expirestime,
                     bp.box_modified AS box_published,
                     bp.box_public_from,
                     bp.box_public_to
                FROM %s b
                LEFT OUTER JOIN %s bp ON bp.box_id = b.box_id
               WHERE b.box_id = '%d'";
      $params = array($this->tableBox, $this->tableBoxPublic, $boxId);
      if ($res = $this->databaseQueryFmt($sql, $params)) {
        if ($row = $res->fetchRow(DB_FETCHMODE_ASSOC)) {
          $this->box = $row;
          if (isset($lngId) && $lngId > 0) {
            $this->loadTranslation($this->box['box_id'], $lngId);
          }
          return TRUE;
        }
      }
    }
    return NULL;
  }

  /**
  * Returns the cache time of the box in seconds.
  *
  * @return integer
  */
  function getBoxBrowserCacheTime() {
    switch ($this->box['box_expiresmode']) {
    case \Papaya\Content\Options::CACHE_SYSTEM :
      //system cache time
      if (defined('PAPAYA_CACHE_TIME_BROWSER') && PAPAYA_CACHE_TIME_BROWSER > 0) {
        return (int)PAPAYA_CACHE_TIME_BROWSER;
      }
      break;
    case \Papaya\Content\Options::CACHE_INDIVIDUAL :
      if ($this->box['box_expirestime'] > 0) {
        return (int)$this->box['box_expirestime'];
      }
    }
    return 0;
  }
}

