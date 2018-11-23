<?php
/**
* Basic mimetypes database access class
*
* Use this class to fetch mimegroup or mimetype information. Editing mimetypes
* takes place in papaya_mediadb.
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
* @subpackage Media-Database
* @version $Id: base_mediadb_mimetypes.php 39612 2014-03-18 21:33:14Z weinert $
*/

/**
* Basic mimetypes database access class
*
* @package Papaya
* @subpackage Media-Database
*/
class base_mediadb_mimetypes extends base_db {

  /**
  * @var string $tableMimeGroups table for mime groups
  */
  var $tableMimeGroups = PAPAYA_DB_TBL_MEDIADB_MIMEGROUPS;
  /**
  * @var string $tableMimeGroups table for mime groups translations
  */
  var $tableMimeGroupsTrans = PAPAYA_DB_TBL_MEDIADB_MIMEGROUPS_TRANS;
  /**
  * @var string $tableMimeGroups table for mimetypes
  */
  var $tableMimeTypes = PAPAYA_DB_TBL_MEDIADB_MIMETYPES;
  /**
  * @var string $tableMimeGroups table for mimetypes extensions
  */
  var $tableMimeTypesExtensions = PAPAYA_DB_TBL_MEDIADB_MIMETYPES_EXTENSIONS;

  /**
  * create an instance of base_mediadb_mimtypes if none exists, returns it
  */
  public static function getInstance() {
    static $mimeObj = NULL;
    if (!isset($mimeObj)) {
      $mimeObj = new base_mediadb_mimetypes;
    }
    return $mimeObj;
  }

  /**
  * fetch mimegroups from the database
  *
  * use this method to get a list of mimegroups in a given language
  *
  * @param integer $lngId language id
  * @param boolean $countTypes optional, whether number of corresponding types shall be counted
  * @return array $result list of mimegroups (id, icon, title, [COUNT])
  */
  function getMimeGroups($lngId, $countTypes = FALSE) {
    $result = array();
    $sql = "SELECT m.mimegroup_id, m.mimegroup_icon, mt.mimegroup_title
              FROM %s m
              LEFT OUTER JOIN %s mt
                   ON (m.mimegroup_id = mt.mimegroup_id AND mt.lng_id = %d)
            ORDER BY mimegroup_title ASC
           ";
    $params = array($this->tableMimeGroups, $this->tableMimeGroupsTrans, $lngId);

    if ($res = $this->databaseQueryFmt($sql, $params)) {
      while ($row = $res->fetchRow(DB_FETCHMODE_ASSOC)) {
        $result[$row['mimegroup_id']] = $row;
      }
      if ($countTypes) {
        $groupCondition = $this->databaseGetSQLCondition('mimegroup_id', array_keys($result));
        $sql = "SELECT mimegroup_id, COUNT(*) AS count
                  FROM %s
                 WHERE $groupCondition
                 GROUP BY mimegroup_id
               ";
        $params = array($this->tableMimeTypes);
        if ($res = $this->databaseQueryFmt($sql, $params)) {
          while ($row = $res->fetchRow(DB_FETCHMODE_ASSOC)) {
            $result[$row['mimegroup_id']]['COUNT'] = $row['count'];
          }
        }
      }
    }
    return $result;
  }

  /**
  * fetch all translations of a given mimegroup
  *
  * use this method to fetch properties translations for a given mimegroup
  *
  * @param integer $groupId mimegroup id
  * @return array $result mimegroup translations (lng_id -> (id, icon, title))
  */
  function getMimeGroup($groupId) {
    $result = array();
    $sql = "SELECT m.mimegroup_id, m.mimegroup_icon, mt.lng_id, mt.mimegroup_title
              FROM %s m, %s mt
             WHERE m.mimegroup_id = %d AND m.mimegroup_id = mt.mimegroup_id
           ";
    $params = array($this->tableMimeGroups, $this->tableMimeGroupsTrans, $groupId);

    if ($res = $this->databaseQueryFmt($sql, $params)) {
      while ($row = $res->fetchRow(DB_FETCHMODE_ASSOC)) {
        $result[$row['lng_id']] = $row;
      }
    }
    return $result;
  }

  /**
  * fetch list of mimetypes limited to a group, if group id is given
  *
  * use this method if you need all mimetypes by group or only mimetypes of
  * a/some group(s)
  *
  * @param mixed $groupId optional, group id to limit result to
  * @return array $result list of mimetypes
  *   (group_id -> (type_id -> (id, type, icon, group, range-support)))
  */
  function getMimeTypes($groupId = NULL) {
    $result = array();
    $groupCondition = $this->databaseGetSQLCondition('mimegroup_id', $groupId);
    $sql = "SELECT mimetype_id, mimetype, mimetype_icon, mimetype_ext, mimegroup_id,
                   range_support, shaping, shaping_limit, shaping_offset
              FROM %s
             WHERE $groupCondition
             ORDER BY mimetype ASC
           ";
    $params = array($this->tableMimeTypes);

    if ($res = $this->databaseQueryFmt($sql, $params)) {
      while ($row = $res->fetchRow(DB_FETCHMODE_ASSOC)) {
        $result[$row['mimegroup_id']][$row['mimetype_id']] = $row;
      }
    }
    return $result;
  }

  /**
  * fetch mimetype data by id or mimetype string
  *
  * use this method if you need mimetype data for a mimetypeid OR string
  *
  * @param mixed $mimeType if integer, used as mimetype_id, else as mimetype, may be array of those
  * @return array $result id, type, icon, group_id, range_support
  *
  */
  function getMimeType($mimeType) {
    $result = array();
    if ((is_array($mimeType) && count($mimeType) > 0) || $mimeType != '') {
      $condition = '';
      if (is_array($mimeType)) {
        foreach ($mimeType as $type) {
          if (is_int($type)) {
            $ids[] = $type;
          } elseif ($type != '') {
            $types[] = $type;
          }
        }
      } elseif (is_int($mimeType)) {
        $condition = $this->databaseGetSQLCondition('mimetype_id', $mimeType);
      } else {
        $condition = $this->databaseGetSQLCondition('mimetype', $mimeType);
      }
      if (isset($ids) && is_array($ids) && count($ids) > 0) {
        $idsCondition = ' OR '.$this->databaseGetSQLCondition('mimetype_id', $ids);
      } else {
        $idsCondition = '';
      }
      if (isset($types) && is_array($types) && count($types) > 0) {
        $typesCondition = ' OR '.$this->databaseGetSQLCondition('mimetype', $types);
      } else {
        $typesCondition = '';
      }

      $sql = "SELECT mimetype_id, mimetype, mimetype_icon, mimetype_ext, mimegroup_id,
                     range_support, shaping, shaping_limit, shaping_offset
                FROM %s
              WHERE $condition
                 $idsCondition
                 $typesCondition
            ";
      $params = array($this->tableMimeTypes);
      if ($res = $this->databaseQueryFmt($sql, $params)) {
        while ($row = $res->fetchRow(DB_FETCHMODE_ASSOC)) {
          if (is_int($mimeType) || (!is_array($mimeType) && $mimeType != '')) {
            return $row;
          } else {
            $result[$row['mimetype_id']] = $row;
          }
        }
      }
    }
    return $result;
  }

  /**
   * Returns the mime group to a mime type
   *
   * @param mixed $mimeType if integer, used as mimetype_id, else as string mimetype
   * @return integer mimegroup_id
   */
  function getMimeGroupIdByMimeType($mimeType) {
    if (is_int($mimeType)) {
      $condition = $this->databaseGetSQLCondition('mimetype_id', $mimeType);
    } else {
      $condition = $this->databaseGetSQLCondition('mimetype', $mimeType);
    }
    $sql = "SELECT mimegroup_id
              FROM %s
             WHERE $condition";
    $params = array($this->tableMimeTypes);
    if ($res = $this->databaseQueryFmt($sql, $params)) {
      return $res->fetchField();
    }
    return FALSE;
  }

  /**
  * fetch mimetypes extensions for a given mimetype id
  *
  * use this method if you need a list of extensions for a given mimetype id
  *
  * @param integer $mimeTypeId mimetype id
  * @return array $result list of mimetype extensions (id, extension)
  */
  function getMimeTypesExtensions($mimeTypeId) {
    $result = array();
    $mimeTypeCondition = $this->databaseGetSQLCondition('mimetype_id', $mimeTypeId);

    $sql = "SELECT mimetype_id, mimetype_extension
              FROM %s
             WHERE $mimeTypeCondition
           ";
    $params = array($this->tableMimeTypesExtensions);

    if ($res = $this->databaseQueryFmt($sql, $params)) {
      while ($row = $res->fetchRow(DB_FETCHMODE_ASSOC)) {
        $result[$row['mimetype_extension']] = $row['mimetype_extension'];
      }
    }
    return $result;
  }

  /**
  * fetch possible mimetypes for a given extension
  *
  * use this method if you have a file extension and need the mimetype/id for it
  *
  * @param string $extension file extension
  * @return array $result list of mimetypes (id -> mimetype)
  */
  function getMimeTypeByExtension($extension) {
    $result = array();
    $sql = "SELECT e.mimetype_id, m.mimetype, m.mimetype_icon, m.mimetype_ext, m.mimegroup_id,
                   m.range_support
              FROM %s e
              LEFT OUTER JOIN %s m ON (e.mimetype_id = m.mimetype_id)
             WHERE mimetype_extension = '%s'
           ";
    $params = array($this->tableMimeTypesExtensions, $this->tableMimeTypes,
      strtolower($extension));
    if ($res = $this->databaseQueryFmt($sql, $params)) {
      if ($row = $res->fetchRow(DB_FETCHMODE_ASSOC)) {
        return $row;
      }
    }
    return $result;
  }

  /**
  * This method generates the src URI for a mimetype icon.
  *
  * @param string $mimeTypeIcon icon name with extension
  * @param integer $size size of the icon, 16, 22 or 48
  * @return string images source URI
  */
  function getMimeTypeIcon($mimeTypeIcon, $size = 16) {
    switch ($size) {
    case 16:
    default:
      $size = 16;
      break;
    case 22:
      $size = 22;
      break;
    case 48:
      $size = 48;
      break;
    }
    return papaya_strings::escapeHTMLChars(
      'icon.mimetypes.'.preg_replace('(\.(gif|png|svg)$)', '', $mimeTypeIcon).'?size='.$size
    );
  }

}

