<?php
/**
* Export database structur as XML
*
* @copyright 2002-2009 by papaya Software GmbH - All rights reserved.
* @link http://www.papaya-cms.com/
* @license http://www.gnu.org/licenses/old-licenses/gpl-2.0.html GNU General Public License, version 2
*
* You can redistribute and/or modify this script under the terms of the GNU General Public
* License (GPL) version 2, provided that the copyright and license notes, including these
* lines, remain unmodified. papaya is distributed in the hope that it will be useful, but
* WITHOUT ANY WARRANTY; without even the implied warranty of MERCHANTABILITY or FITNESS
* FOR A PARTICULAR PURPOSE.
*
* @package Papaya-Library
* @subpackage Database
* @version $Id: base_db2xml.php 39731 2014-04-08 10:08:07Z weinert $
*/

/**
* Export database structur as XML
*
* @package Papaya-Library
* @subpackage Database
*/
class base_database2xml extends base_db {

  /**
  * Data tables
  * @var array $dataTables
  */
  var $dataTables = array();

  /**
   * Transform table to XML
   *
   * @param string $tableName database table name
   * @param null $fileName
   * @access public
   * @return boolean
   */
  function table2xml($tableName, $fileName = NULL) {
    $data = $this->databaseQueryTableStructure($tableName);
    if (isset($data['type'])) {
      $typeStr = ' type="'.papaya_strings::escapeHTMLChars($data['type']).'"';
    } else {
      $typeStr = '';
    }
    if (strpos($tableName, PAPAYA_DB_TABLEPREFIX) === 0) {
      $xml = '<table name="'.
        papaya_strings::escapeHTMLChars(substr($tableName, strlen(PAPAYA_DB_TABLEPREFIX) + 1)).
        '" prefix="yes"'.$typeStr.'>'.LF;
    } else {
      $xml = '<table name="'.papaya_strings::escapeHTMLChars($tableName).'"'.$typeStr.'>'.LF;
    }
    $xml .= '  <fields>'.LF;
    foreach ($data['fields'] as $field) {
      $xml .= sprintf(
        '    <field name="%s" type="%s" size="%s"%s%s%s/>'.LF,
        papaya_strings::escapeHTMLChars($field['name']),
        papaya_strings::escapeHTMLChars($field['type']),
        papaya_strings::escapeHTMLChars($field['size']),
        ($field['null'] == 'yes') ? ' null="yes"' : ' null="no"',
        ($field['autoinc'] == 'yes') ? ' autoinc="yes"' : '',
        ($field['default'])
          ? ' default="'.papaya_strings::escapeHTMLChars($field['default']).'"' : ''
      );
    }
    $xml .= '  </fields>'.LF;
    $xml .= '  <keys>'.LF;
    if (isset($data['keys']['PRIMARY'])) {
      $xml .= '    <primary-key>'.LF;
      ksort($data['keys']['PRIMARY']['fields']);
      foreach ($data['keys']['PRIMARY']['fields'] as $field) {
        if (isset($data['keys']['PRIMARY']['keysize'][$field]) &&
            $data['keys']['PRIMARY']['keysize'][$field] > 0) {
          $xml .= '      <field size="'.(int)$data['keys']['PRIMARY']['keysize'][$field].'">'.
            papaya_strings::escapeHTMLChars($field).'</field>'.LF;
        } else {
          $xml .= '      <field>'.papaya_strings::escapeHTMLChars($field).'</field>'.LF;
        }
      }
      $xml .= '    </primary-key>'.LF;
      unset($data['keys']['PRIMARY']);
    }
    foreach ($data['keys'] as $key) {
      $fulltext = ($key['fulltext'] == 'yes') ? ' fulltext="yes"' : '';
      $unique = ($key['unique'] == 'yes') ? ' unique="yes"' : '';
      $xml .= sprintf(
        '    <key name="%s"%s%s>'.LF,
        papaya_strings::escapeHTMLChars($key['name']),
        $unique,
        $fulltext
      );
      ksort($key['fields']);
      foreach ($key['fields'] as $field) {
        if (isset($key['keysize'][$field]) && $key['keysize'][$field] > 0) {
          $xml .= '      <field size="'.(int)$key['keysize'][$field].'">'.
            papaya_strings::escapeHTMLChars($field).'</field>'.LF;
        } else {
          $xml .= '      <field>'.papaya_strings::escapeHTMLChars($field).'</field>'.LF;
        }
      }
      $xml .= '    </key>'.LF;
    }
    $xml .= '  </keys>'.LF;
    $xml .= '</table>'.LF;

    if (isset($fileName)) {
      if ($fh = fopen($fileName, 'w')) {
        fwrite($fh, '<?xml version="1.0" encoding="UTF-8" ?>'.LF.$xml);
        fclose($fh);
        return TRUE;
      }
    } else {
      return '<?xml version="1.0" encoding="UTF-8" ?>'.LF.$xml;
    }
    return FALSE;
  }

  /**
   * Export a table into XML-file
   *
   * @param string $path target path
   * @param string $prefix file prefix
   * @param null $callbackFunc
   * @param null $dataTables
   * @access public
   * @return boolean
   */
  function exportTables2XML(
    $path = './', $prefix = 'table_', $callbackFunc = NULL, $dataTables = NULL
  ) {
    $tables = $this->databaseQueryTableNames();
    $this->dataTables = $dataTables;
    $rPath = realpath($path).'/';
    if (file_exists($rPath)) {
      if ($dh = opendir($rPath)) {
        while ($file = readdir($dh)) {
          if (strpos($file, $prefix) === 0) {
            unlink($rPath.$file);
          }
        }
        closedir($dh);
        foreach ($tables as $tableName) {
          if (strpos($tableName, PAPAYA_DB_TABLEPREFIX) === 0) {
            $fileName = $rPath.$prefix.substr($tableName, strlen(PAPAYA_DB_TABLEPREFIX) + 1);
            $tableNameStripped = substr($tableName, strlen(PAPAYA_DB_TABLEPREFIX) + 1);
          } else {
            $fileName = $rPath.$prefix.$tableName;
            $tableNameStripped = $tableName;
          }
          $result = $this->table2XML($tableName, $fileName.'.xml');
          if ($result &&
              isset($this->dataTables) && is_array($this->dataTables) &&
              in_array($tableNameStripped, $this->dataTables)) {
            $result = $this->tableData2CSV($tableName, $fileName.'.csv');
          }
          if (isset($callbackFunc) && is_string($callbackFunc) &&
              function_exists($callbackFunc)) {
            $callbackFunc($tableName, $result);
          }
        }
        return TRUE;
      }
    }
    return FALSE;
  }

  /**
  * Transform aray to CSV line
  *
  * @param array $data
  * @access public
  * @return string
  */
  function array2CSVLine($data) {
    if (isset($data) && is_array($data)) {
      $result = '';
      foreach ($data as $value) {
        $result .= ',"'.str_replace('"', '""', $value).'"';
        $result = str_replace('\n', '\\n', $result);
        $result = strtr($result, array("\r\n" => '\n', "\n" => '\n', "\r" => '\n'));
      }
      return substr($result, 1);
    }
    return '';
  }

  /**
  * Transform table data to CSV
  *
  * @param string $tableName database table name
  * @param string $fileName csv file name
  * @access public
  * @return boolean
  */
  function tableData2CSV($tableName, $fileName) {
    if ($res = $this->databaseQueryFmt('SELECT COUNT(*) FROM %s', $tableName)) {
      list($recordCount) = $res->fetchRow();
      $res->free();
      if ($recordCount > 0) {
        if ($res = $this->databaseQueryFmt('SELECT * FROM %s', $tableName)) {
          if ($row = $res->fetchRow(DB_FETCHMODE_ASSOC)) {
            if ($fh = fopen($fileName, 'w')) {
              fwrite($fh, $this->array2CSVLine(array_keys($row)).LF);
              fwrite($fh, $this->array2CSVLine($row).LF);
              while ($row = $res->fetchRow()) {
                fwrite($fh, $this->array2CSVLine($row).LF);
              }
              fclose($fh);
            }
          }
          $res->free();
          return TRUE;
        }
        return FALSE;
      } else {
        return TRUE;
      }
    }
    return FALSE;
  }
}


