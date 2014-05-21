<?php
/**
* Mimetype data
*
* @deprecated this class is replaced by base_mediadb_mimetypes, please confirm
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
* @version $Id: base_mimetypes.php 39260 2014-02-18 17:13:06Z weinert $
*/

/**
* Mime type data
*
* @package Papaya
* @subpackage Media-Database
*/
class base_mimetypes extends base_db {
  /**
  * Papaya database table mime types
  * @var string $tableMimeTypes
  */
  var $tableMimeTypes = PAPAYA_DB_TBL_MIMETYPES;
  /**
  * Mime types
  * @var array $mimeTypes
  */
  var $mimeTypes = array();
  /**
  * Mime types index
  * @var array $mimeTypesIndex
  */
  var $mimeTypesIndex = array();

  /**
  * Load mime types by extension
  *
  * @param string $ext extension
  * @param boolean $reload optional, default value FALSE
  * @access public
  * @return mixed boolean FALSE or array database row
  */
  function loadMimeTypeByExt($ext, $reload = FALSE) {
    $iExt = strtolower($ext);
    if (isset($this->mimeTypesIndex) &&
        isset($this->mimeTypesIndex[$iExt])) {
      if ($reload) {
        $id = $this->mimeTypesIndex[$iExt]['mimetype_id'];
        unset($this->mimeTypesIndex[$iExt]);
        if (isset($this->mimeTypes[$id])) {
          unset($this->mimeTypes[$id]);
        }
      } else {
        return $this->mimeTypesIndex[$iExt]['mimetype_id'];
      }
    }
    $sql = "SELECT mimetype_id, mimetype_ext, mimetype_text, mimetype_icon,
                   mimetype_ext
              FROM %s
             WHERE mimetype_ext = '%s'";
    $params = array($this->tableMimeTypes, strtolower($iExt));
    if ($res = $this->databaseQueryFmt($sql, $params)) {
      if ($row = $res->fetchRow(DB_FETCHMODE_ASSOC)) {
        $this->mimeTypes[$row['mimetype_id']] = $row;
        $this->mimeTypesIndex[$row['mimetype_ext']] =
          &$this->mimeTypes[$row['mimetype_id']];
        return $row;
      }
    }
    return FALSE;
  }

  /**
  * Load icons by extensions
  *
  * @param array $extensions
  * @access public
  * @return array $result
  */
  function loadIconsByExtensions($extensions) {
    $result = FALSE;
    if (isset($extensions) && is_array($extensions) && count($extensions) > 0) {
      if (count($extensions) > 1) {
        $sql = "SELECT mimetype_ext, mimetype_icon
                  FROM %s
                 WHERE mimetype_ext IN (";
        foreach ($extensions as $ext) {
          $sql .= "'".addslashes($ext)."', ";
        }
        $sql = substr($sql, 0, -2).')';
        $res = $this->databaseQueryFmt($sql, $this->tableMimeTypes);
      } else {
        $sql = "SELECT mimetype_ext, mimetype_icon
                  FROM %s
                 WHERE mimetype_ext = '%s'";
        $params = array($this->tableMimeTypes, $extensions[0]);
        $res = $this->databaseQueryFmt($sql, $params);
      }
      if ($res) {
        while ($row = $res->fetchRow(DB_FETCHMODE_ASSOC)) {
          $result[$row['mimetype_ext']] = $row['mimetype_icon'];
        }
      }
    }
    return $result;
  }
}

