<?php
/**
* papaya_public_boxeslinks variable
*
* @copyright 2002-2007 by papaya Software GmbH - All rights reserved.
* @link http://www.papaya-cms.com/
* @license http://www.gnu.org/licenses/old-licenses/gpl-2.0.html GNU General Public License, version 2
*
* You can redistribute and/or modify this script under the terms of the GNU General Public
* License (GPL) version 2, provided that the copyright and license notes, including these
* lines, remain unmodified. papaya is distributed in the hope that it will be useful, but
* WITHOUT ANY WARRANTY; without even the implied warranty of MERCHANTABILITY or FITNESS
* FOR A PARTICULAR PURPOSE.
*
* @package Papaya
* @subpackage Core
* @version $Id: papaya_publicboxeslinks.php 39794 2014-05-06 15:34:47Z weinert $
*/

/**
* papaya_public_boxeslinks variable
*
* @package Papaya
* @subpackage Core
*/
class papaya_public_boxeslinks extends base_boxeslinks {
  /**
  * Papaya database table box public
  * @var string $tableBox
  */
  var $tableBox = PAPAYA_DB_TBL_BOX_PUBLIC;
  /**
  * Papaya database table box public translation
  * @var string $tableBoxTrans
  */
  var $tableBoxTrans = PAPAYA_DB_TBL_BOX_PUBLIC_TRANS;
  /**
  * Papaya database table box group
  * @var string $tableBoxgroup
  */
  var $tableBoxgroup = PAPAYA_DB_TBL_BOXGROUP;
  /**
  * Papaya database table box links
  * @var string $tableLink
  */
  var $tableLink = PAPAYA_DB_TBL_BOXLINKS;
  /**
  * Papaya database table  topics public
  * @var string $tableTopics
  */
  var $tableTopics = PAPAYA_DB_TBL_TOPICS_PUBLIC;

  /**
   * Merged cache definition for all loaded boxes
   */
  private $_cacheDefinition = NULL;

  /**
  * Load data list
  *
  * @param integer $lngId language id
  * @param integer $viewModeId view mode id
  * @access public
  * @return boolean
  */
  function loadDataList($lngId, $viewModeId, $now = NULL) {
    $this->_cacheDefinition = NULL;
    return parent::loadDataList($lngId, $viewModeId, time());
  }

  /**
   * Load data list
   *
   * @param integer $lngId language id
   * @param integer $viewModeId view mode id
   * @param array|integer $boxIds
   * @access public
   * @return boolean
   */
  function loadDataElements($lngId, $viewModeId, $boxIds) {
    $this->data = array();
    $this->_cacheDefinition = NULL;
    $now = time();
    if (is_array($boxIds) && count($boxIds) > 0) {
      $filter = $this->databaseGetSQLCondition('b.box_id', $boxIds);
    } else {
      $filter = $this->databaseGetSQLCondition('b.box_id', (int)$boxIds);
    }
    if ($viewModeId > 0) {
      $sql = "SELECT b.box_id, b.box_name, b.boxgroup_id,
                     b.box_deliverymode,
                     b.box_cachemode, b.box_cachetime,
                     b.box_expiresmode, b.box_expirestime,
                     bt.box_title, bt.box_data, bt.view_id, bt.box_trans_modified,
                     m.module_guid, m.module_useoutputfilter,
                     m.module_path, m.module_file, m.module_class
                FROM %s b,
                     %s bt,
                     %s v,
                     %s vl,
                     %s m
               WHERE $filter
                 AND bt.box_id = b.box_id
                 AND bt.lng_id = '%d'
                 AND v.view_id = bt.view_id
                 AND vl.view_id = bt.view_id
                 AND vl.viewmode_id = %d
                 AND m.module_guid = v.module_guid
                 AND m.module_active = 1
                 AND m.module_type = 'box'
                 AND (b.box_public_from = 0 OR b.box_public_from <= '%d')
                 AND (b.box_public_to = 0 OR b.box_public_to >= '%d')";
      $params = array(
        $this->tableBox,
        $this->tableBoxTrans,
        $this->tableViews,
        $this->tableViewLinks,
        $this->tableModules,
        $lngId,
        $viewModeId,
        $now,
        $now
      );
    } else {
      $sql = "SELECT b.box_id, b.box_name, b.boxgroup_id,
                     b.box_deliverymode,
                     b.box_cachemode, b.box_cachetime,
                     b.box_expiresmode, b.box_expirestime,
                     bt.box_title, bt.box_data, bt.view_id, bt.box_trans_modified,
                     m.module_guid, m.module_useoutputfilter,
                     m.module_path, m.module_file, m.module_class
                FROM %s b,
                     %s bt,
                     %s v,
                     %s m
               WHERE $filter
                 AND bt.box_id = b.box_id
                 AND bt.lng_id = '%d'
                 AND v.view_id = bt.view_id
                 AND m.module_guid = v.module_guid
                 AND m.module_active = 1
                 AND m.module_type = 'box'
                 AND (b.box_public_from = 0 OR b.box_public_from <= '%d')
                 AND (b.box_public_to = 0 OR b.box_public_to >= '%d')";
      $params = array(
        $this->tableBox,
        $this->tableBoxTrans,
        $this->tableViews,
        $this->tableModules,
        $lngId,
        $now,
        $now
      );
    }
    if ($res = $this->databaseQueryFmt($sql, $params)) {
      while ($row = $res->fetchRow(DB_FETCHMODE_ASSOC)) {
        $row['module_file'] = $row['module_path'].$row['module_file'];
        $this->data[$row['box_id']] = $row;
      }
      return TRUE;
    }
    return FALSE;
  }

  /**
   * Merge the cache definiiton of the loaded boxes and return them
   *
   * @param PapayaCacheIdentifierDefinition $definition
   * @return PapayaCacheIdentifierDefinition
   */
  public function cacheable(PapayaCacheIdentifierDefinition $definition = NULL) {
    if (isset($definition)) {
      $this->_cacheDefinition = $definition;
    } elseif (NULL === $this->_cacheDefinition) {
      $this->_cacheDefinition = $definition = new PapayaCacheIdentifierDefinitionGroup();
      $modules = array();
      foreach ($this->data as $boxData) {
        $modules[] = $boxData['module_guid'];
      }
      foreach ($this->data as $boxData) {
        $plugin = $this->papaya()->plugins->get(
          $boxData['module_guid'],
          $this->parentObj,
          $boxData['box_data']
        );
        if ($plugin instanceof PapayaPluginCacheable) {
          $definition->add($plugin->cacheable());
        } elseif (method_exists($plugin, 'getCacheId')) {
          $definition->add(
            new PapayaCacheIdentifierDefinitionCallback(array($plugin, 'getCacheId'))
          );
        } else {
          $this->_cacheDefinition = new PapayaCacheIdentifierDefinitionBoolean(FALSE);
          return $this->_cacheDefinition;
        }
      }
    }
    return $this->_cacheDefinition;
  }
}

