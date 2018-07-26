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
class base_boxes extends base_db {
  /**
  * Papaya database table box
  * @var string $tableBox
  */
  var $tableBox = PAPAYA_DB_TBL_BOX;
  /**
  * Papaya database table box translation
  * @var string $tableBoxTrans
  */
  var $tableBoxTrans = PAPAYA_DB_TBL_BOX_TRANS;
  /**
  * Papaya database table box versions
  * @var string $tableBoxVersions
  */
  var $tableBoxVersions = PAPAYA_DB_TBL_BOX_VERSIONS;
  /**
  * Papaya database table box versions of translation
  * @var string $tableBoxVersionsTrans
  */
  var $tableBoxVersionsTrans = PAPAYA_DB_TBL_BOX_VERSIONS_TRANS;
  /**
  * Papaya database table box public
  * @var string $tableBoxPublic
  */
  var $tableBoxPublic = PAPAYA_DB_TBL_BOX_PUBLIC;
  /**
  * Papaya database table box public translation
  * @var string $tableBoxPublicTrans
  */
  var $tableBoxPublicTrans = PAPAYA_DB_TBL_BOX_PUBLIC_TRANS;
  /**
  * Papaya database table boxgroup
  * @var string $tableBoxgroup
  */
  var $tableBoxgroup = PAPAYA_DB_TBL_BOXGROUP;
  /**
  * Papaya database table modules
  * @var string $tableModules
  */
  var $tableModules = PAPAYA_DB_TBL_MODULES;
  /**
  * Papaya database table views
  * @var string $tableViews
  */
  var $tableViews = PAPAYA_DB_TBL_VIEWS;
  /**
  * Papaya database table boxlinks
  * @var string $tableLink
  */
  var $tableLink = PAPAYA_DB_TBL_BOXLINKS;
  /**
  * Papaya database table authentification user
  * @var string $tableAuthUser
  */
  var $tableAuthUser = PAPAYA_DB_TBL_AUTHUSER;
  /**
  * Papaya database table topics trans
  * @var string $tableTopicTrans
  */
  var $tableTopics = PAPAYA_DB_TBL_TOPICS;
  /**
  * Papaya database table topics trans
  * @var string $tableTopicTrans
  */
  var $tableTopicsTrans = PAPAYA_DB_TBL_TOPICS_TRANS;

  /**
  * box $box
  * @var array
  */
  var $box = NULL;

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
                     b.box_unpublished_languages,
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
  * Load translated data for a box
  *
  * @param integer $boxId
  * @param integer $lngId
  * @access public
  * @return boolean
  */
  function loadTranslation($boxId, $lngId) {
    unset($this->box['TRANSLATION']);
    $sql = "SELECT b.box_id, b.lng_id, b.view_id, b.box_title, b.box_data,
                   b.box_trans_created, b.box_trans_modified, b.view_id,
                   m.module_guid, m.module_path, m.module_file,
                   m.module_class, m.module_useoutputfilter
              FROM %s b
              LEFT OUTER JOIN %s v ON (v.view_id = b.view_id)
              LEFT OUTER JOIN %s m ON (m.module_guid = v.module_guid
                                      AND m.module_active = 1 AND m.module_type = 'box')
             WHERE b.box_id = '%d' AND lng_id = '%d'";
    $params = array($this->tableBoxTrans,
                    $this->tableViews,
                    $this->tableModules,
                    $boxId,
                    $lngId);
    if ($res = $this->databaseQueryFmt($sql, $params)) {
      if ($row = $res->fetchRow(DB_FETCHMODE_ASSOC)) {
        $this->box['TRANSLATION'] = $row;
        return TRUE;
      }
    }
    return FALSE;
  }

  /**
  * Get publish date
  *
  * @access public
  * @return mixed boolean od modified
  */
  function getPublicDate() {
    $sql = "SELECT box_modified
              FROM %s
             WHERE box_id = '%d'";
    $params = array(
      $this->tableBoxPublic,
      $this->box['box_id']
    );
    if ($res = $this->databaseQueryFmt($sql, $params)) {
      if ($row = $res->fetchRow()) {
        return $row[0];
      }
    }
    return FALSE;
  }

  /**
   * Parsed Box
   *
   * @access public
   * @param $topicObj
   * @param array $parseParams
   * @return mixed preview
   */
  function parsedBox($topicObj, $parseParams = array()) {
    $output = '';
    if (isset($this->box) && is_array($this->box) && isset($this->box['TRANSLATION'])) {
      if (isset($this->box['TRANSLATION']['module_guid']) &&
          $this->box['TRANSLATION']['module_guid'] != '') {
        $plugin = $this->papaya()->plugins->get(
          $this->box['TRANSLATION']['module_guid'],
          $topicObj,
          $this->box['TRANSLATION']['box_data']
        );
        if ($plugin instanceof \PapayaPluginAppendable) {
          $dom = new \PapayaXmlDocument();
          $boxNode = $dom->appendElement('box');
          $boxNode->append($plugin);
          $output = $boxNode->saveFragment();
        } elseif (isset($plugin) && is_object($plugin)) {
          $plugin->boxId = $this->box['box_id'];
          $plugin->languageId = $this->box['TRANSLATION']['lng_id'];
          $output = $plugin->getParsedData();
        }
        if (!empty($output)) {
          $parser = new papaya_parser;
          $parser->tableTopics = $this->tableTopics;
          $parser->tableTopicsTrans = $this->tableTopicsTrans;
          if (isset($parseParams['link_outputmode'])) {
            $parser->setLinkOutputMode($parseParams['link_outputmode']);
          }
          $output = $parser->parse($output, $this->box['TRANSLATION']['lng_id']);
        }
      } else {
        $this->logMsg(
          MSG_ERROR,
          PAPAYA_LOGTYPE_SYSTEM,
          'No module for box defined.'
        );
      }
    }
    return $output;
  }

  /**
  * get current box view id
  *
  * @return integer
  */
  function getViewId() {
    if (isset($this->box) && isset($this->box['TRANSLATION'])) {
      return (int)$this->box['TRANSLATION']['view_id'];
    } else {
      return 0;
    }
  }

  /**
  * Returns the cache time of the box in seconds.
  *
  * @return integer
  */
  function getBoxBrowserCacheTime() {
    return 0;
  }
}

