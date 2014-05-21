<?php
/**
* Linktypes basic object
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
* @version $Id: base_linktypes.php 39358 2014-02-26 15:00:57Z weinert $
*/

/**
* Linktypes basic object
* @package Papaya
* @subpackage Core
*/
class base_linktypes extends base_db {
  /**
  * @var string $tableLinkTypes link types table name
  */
  var $tableLinkTypes = PAPAYA_DB_TBL_LINKTYPES;

  /**
   * @var array
   */
  public $linkTypes = array();

  /**
  * loads link types
  *
  * @param boolean $minimal loads only id and name if set to TRUE, default FALSE
  * @param mixed $linkTypeIds single ID or array of linktype Ids
  * @return array $linkTypes fills this->linkTypes and returns it
  */
  function loadLinkTypes($minimal = FALSE, $linkTypeIds = NULL) {
    $this->linkTypes = array();
    if ($linkTypeIds != NULL) {
      $linkTypeCondition = ' WHERE '.$this->databaseGetSQLCondition(
        'linktype_id', $linkTypeIds
      );
    } else {
      $linkTypeCondition = '';
    }
    if ($minimal) {
      $sql = "SELECT linktype_id, linktype_name
                FROM %s
                $linkTypeCondition
               ORDER BY linktype_name ASC
            ";
    } else {
      $sql = "SELECT linktype_id, linktype_name, linktype_class, linktype_target,
                     linktype_is_popup, linktype_popup_config, linktype_is_visible
                FROM %s
                $linkTypeCondition
               ORDER BY linktype_name ASC
            ";
    }
    $params = array($this->tableLinkTypes);
    if ($res = $this->databaseQueryFmt($sql, $params)) {
      while ($row = $res->fetchRow(DB_FETCHMODE_ASSOC)) {
        if ($minimal) {
          $this->linkTypes[$row['linktype_id']] = $row['linktype_name'];
        } else {
          $this->linkTypes[$row['linktype_id']] = $row;
        }
      }
    }
    return $this->linkTypes;
  }

  /**
   * loads linktypes with additional data
   *
   * @param int|array $linkTypeIds single ID or array of linktype Ids or NULL
   * @param bool $forceLoading
   * @return array $result linktypes with additional data (popup config).
   */
  function getCompleteLinkTypes($linkTypeIds = NULL, $forceLoading = FALSE) {
    static $linkTypes, $invalidIds;
    if ($forceLoading) {
      unset($linkTypes);
      unset($invalidIds);
    }
    $ids = array();
    $returnArray = TRUE;
    if (isset($linkTypeIds)) {
      if (!is_array($linkTypeIds)) {
        $linkTypeIds = array($linkTypeIds);
        $returnArray = FALSE;
      }
      foreach ($linkTypeIds as $linkTypeId) {
        if (isset($linkTypes[$linkTypeId]) &&
            isset($this->linkTypes[$linkTypeId])) {
          //exists in both cache arrays
          continue;
        } elseif (isset($linkTypes[$linkTypeId])) {
          //exists only in method data cache
          $this->linkTypes[$linkTypeId] = $linkTypes[$linkTypeId];
        } elseif (isset($this->linkTypes[$linkTypeId])) {
          //exists only in object data cache
          $linkTypes[$linkTypeId] = $this->linkTypes[$linkTypeId];
        } elseif (!isset($invalidIds[$linkTypeId])) {
          //not loaded so far
          $ids[] = $linkTypeId;
        }
      }
    }
    if (count($ids) > 0) {
      $this->loadLinkTypes(FALSE, $ids);
    }
    $result = array();
    foreach ($linkTypeIds as $linkTypeId) {
      if (isset($this->linkTypes[$linkTypeId])) {
        if (isset($this->linkTypes[$linkTypeId]['linktype_popup_config']) &&
            !isset($this->linkTypes[$linkTypeId]['popup_config'])) {
          $popupConfig = PapayaUtilStringXml::unserializeArray(
            $this->linkTypes[$linkTypeId]['linktype_popup_config']
          );
          $this->linkTypes[$linkTypeId]['popup_config'] = $popupConfig;
          if ($returnArray) {
            $result[$linkTypeId] = $this->linkTypes[$linkTypeId];
          } else {
            return $this->linkTypes[$linkTypeId];
          }
        }
      } else {
        //not loaded - invalid id
        $invalidIds[$linkTypeId] = TRUE;
      }
    }
    return $result;
  }

  /**
  * wrapper for base_linktypes::getCompleteLinkTypes()
  */
  function getLinkType($linkTypeId, $forceLoading = FALSE) {
    return $this->getCompleteLinkTypes($linkTypeId, $forceLoading);
  }

  /**
  * fetches linktypes and their visibility
  *
  * @return array $result array(linktype_id => visibility)
  */
  function getLinkTypesVisibility() {
    $result = array();
    $sql = "SELECT linktype_id, linktype_is_visible
              FROM %s
           ";
    $params = array($this->tableLinkTypes);
    if ($res = $this->databaseQueryFmt($sql, $params)) {
      while ($row = $res->fetchRow(DB_FETCHMODE_ASSOC)) {
        $result[$row['linktype_id']] = $row['linktype_is_visible'];
      }
    }
    return $result;
  }

  /**
  * loads link types by visibility
  *
  * @param integer $visible which status of visibility to load, defaults to 1
  * @param boolean $minimal whether to load additional data, defaults to FALSE
  * @return array $linkTypes link types of requested visibility
  */
  function getLinkTypesByVisibility($visible = 1, $minimal = FALSE) {
    static $linkTypeData;
    if (isset($linkTypeData[$visible]) &&
        isset($linkTypeData[$visible][$minimal])) {
      return $linkTypeData[$visible][$minimal];
    }
    if (!isset($this) || !is_object($this)) {
      $linkTypeObj = new base_linkTypes;
    } else {
      $linkTypeObj = $this;
    }
    $linkTypes = array();
    if ($minimal) {
      $sql = "SELECT linktype_id, linktype_name
                FROM %s
               WHERE linktype_is_visible = '%d'
               ORDER BY linktype_name ASC
            ";
    } else {
      $sql = "SELECT linktype_id, linktype_name, linktype_class, linktype_target,
                     linktype_is_popup, linktype_popup_config, linktype_is_visible
                FROM %s
               WHERE linktype_is_visible = '%d'
               ORDER BY linktype_name ASC
            ";
    }
    $params = array($linkTypeObj->tableLinkTypes, (int)$visible);
    if ($res = $linkTypeObj->databaseQueryFmt($sql, $params)) {
      $linkTypeData[$visible][$minimal] = array();
      while ($row = $res->fetchRow(DB_FETCHMODE_ASSOC)) {
        if ($minimal) {
          $linkTypes[$row['linktype_id']] = $row['linktype_name'];
        } else {
          $linkTypes[$row['linktype_id']] = $row;
        }
      }
      $linkTypeData[$visible][$minimal] = $linkTypes;
    }
    return $linkTypes;
  }

}

