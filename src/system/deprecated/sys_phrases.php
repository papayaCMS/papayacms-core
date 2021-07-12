<?php
/**
* Implementation phrase management
*
* Provides functionality for translation
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
* @subpackage Administration
* @version $Id: sys_phrases.php 39731 2014-04-08 10:08:07Z weinert $
*/

/**
* Manage phrase class
*
* Provides functionality for translation
*
* @package Papaya
* @subpackage Administration
*/
class base_phrases extends base_db {
  /**
  * Phrase table
  * @var string $tablePhrase
  */
  var $tablePhrase = PAPAYA_DB_TBL_PHRASE;

  /**
  * Translation table
  * @var string $tableTranslation
  */
  var $tableTranslation = PAPAYA_DB_TBL_PHRASE_TRANS;

  /**
  * Module phrases
  * @var string $tableModule
  */
  var $tableModule = PAPAYA_DB_TBL_PHRASE_MODULE;

  /**
  * Relations between phrases - moduels
  * @var string $tableModuleRelation
  */
  var $tableModuleRelation = PAPAYA_DB_TBL_PHRASE_MODULE_REL;

  /**
  * Log messages - table
  * @var string $tableLog
  */
  var $tableLog = PAPAYA_DB_TBL_PHRASE_LOG;
  /**
  * Language - table
  * @var string $tableLanguage
  */
  var $tableLanguage = PAPAYA_DB_TBL_LNG;

  /**
  * Current language
  * @var string $lng
  */
  var $lng = NULL;

  /**
  * Modules
  * @var array $modules
  */
  var $modules = NULL;

  /**
  * Default module
  * @var string $defaultModule
  */
  var $defaultModule = NULL;

  /**
   * Load translation for module
   *
   * @param string $title title module
   * @access public
   * @return mixed flase for no translation otherwise translation will be returned
   */
  function loadModule($title) {
    if (isset($this->modules[$title]['phrases'])) {
      unset($this->modules[$title]['phrases']);
    }
    $sql = "SELECT p.phrase_text_lower, pt.translation
              FROM %s m
              LEFT JOIN %s m_rel ON m_rel.module_id = m.module_id
              LEFT JOIN %s p ON p.phrase_id = m_rel.phrase_id
              LEFT JOIN %s pt ON (pt.phrase_id = p.phrase_id AND pt.lng_id = '%d')
             WHERE m.module_title_lower = '%s'";
    $params = array($this->tableModule, $this->tableModuleRelation,
      $this->tablePhrase, $this->tableTranslation,
      $this->lng['lng_id'], $title);
    if ($res = $this->databaseQueryFmt($sql, $params)) {
      while ($row = $res->fetchRow()) {
        $this->modules[$title]['phrases'][$row[0]] = $row[1];
      }
      return (
        isset($this->modules[$title]['phrases']) &&
        is_array($this->modules[$title]['phrases'])
      );
    }
    return FALSE;
  }

  /**
  * Load translation
  *
  * @param string $phrase Phrase
  * @access public
  * @return mixed
  */
  function loadTranslation($phrase) {
    $sql = "SELECT p.phrase_id, pt.translation
              FROM %s p
              LEFT OUTER JOIN %s pt
                ON (pt.phrase_id = p.phrase_id AND pt.lng_id = %d)
             WHERE p.phrase_text_lower = '%s'";
    $params = array($this->tablePhrase, $this->tableTranslation,
      $this->lng['lng_id'], strToLower($phrase));
    if ($res = $this->databaseQueryFmt($sql, $params)) {
      if ($row = $res->fetchRow()) {
        return array($row[0], $row[1]);
      }
    }
    return FALSE;
  }

  /**
  * Return PhraseID
  *
  * @param string $phrase Phrase
  * @access public
  * @return mixed PhraseID or FALSE
  */
  function getPhraseId($phrase) {
    $sql = "SELECT p.phrase_id
              FROM %s p
             WHERE p.phrase_text_lower = '%s'";
    $params = array($this->tablePhrase, $phrase);
    if ($res = $this->databaseQueryFmt($sql, $params)) {
      if ($row = $res->fetchRow()) {
        return $row[0];
      }
    }
    return FALSE;
  }

  /**
  * Return ModulID
  *
  * @param mixed $module Modul
  * @access public
  * @return mixed ModuleID oder FALSE
  */
  function getModuleId($module) {
    $lModule = strtolower($module);
    if (!isset($this->modules[$lModule]['id'])) {
      $sql = "SELECT m.module_id
                FROM %s m
               WHERE m.module_title_lower = '%s'";
      $params = array($this->tableModule, $module);
      if ($res = $this->databaseQueryFmt($sql, $params)) {
        if ($row = $res->fetchRow()) {
          $this->modules[$lModule]['id'] = $row[0];
          return $row[0];
        }
      }
    } else {
      return $this->modules[$lModule]['id'];
    }
    return FALSE;
  }

  /**
  * Get language id from database
  *
  * @param integer $lng language (de-DE, en-US, ...)
  * @access public
  * @return mixed ID or FALSE
  */
  function getLngId($lng) {
    $sql = "SELECT lng_id, lng_short, lng_title
              FROM %s
             WHERE lng_short = '%s'";
    $params = array($this->tableLanguage, $lng);
    if ($res = $this->databaseQueryFmt($sql, $params)) {
      if ($row = $res->fetchRow(DB_FETCHMODE_ASSOC)) {
        $this->lng = $row;
        return $row['lng_id'];
      }
    }
    return FALSE;
  }

  /**
  * Write a new Module in the language database
  *
  * @param mixed $module Modul
  * @access public
  * @return mixed record number or FALSE
  */
  function addModule($module) {
    $data = array(
      'module_title' => $module,
      'module_title_lower' => strToLower($module)
    );
    return $this->databaseInsertRecord($this->tableModule, 'module_id', $data);
  }

  /**
  * Create new connection between tables
  *
  * @param mixed $module Modul
  * @param integer $phraseId Phrase ID
  * @access public
  * @return mixed record number  or FALSE
  */
  function addRelation($module, $phraseId) {
    if ($moduleId = $this->getModuleId($module)) {
      $sql = "SELECT COUNT(*)
                FROM %s
               WHERE phrase_id = %d
                 AND module_id = %d";
      $params = array($this->tableModuleRelation, $phraseId, $moduleId);
      if ($res = $this->databaseQueryFmt($sql, $params)) {
        if ($row = $res->fetchRow()) {
          if ($row[0] === '0') {
            $data = array(
              'phrase_id' => (int)$phraseId,
              'module_id' => (int)$moduleId
            );
            return $this->databaseInsertRecord(
              $this->tableModuleRelation, NULL, $data
            );
          }
        }
      }
    }
    return FALSE;
  }

  /**
   * Writes a message in the logger
   *
   * If debugging is enabled translation will be logged
   *
   * @param string $msg
   * @param string $phrase Phrase
   * @param integer $phraseId Phrase
   * @param mixed $module Modul
   * @access public
   * @return mixed TRUE without log function, otherwise return $this->insertRecord
   */
  function addPhraseLogMsg($msg, $phrase = NULL, $phraseId = NULL, $module = NULL) {
    if (defined('PAPAYA_DBG_PHRASES') && PAPAYA_DBG_PHRASES === '1') {
      $data['log_msg'] = $msg;
      $data['log_datetime'] = time();
      if (isset($phrase)) {
        $data['log_phrase'] = $phrase;
      }
      if (isset($phraseId)) {
        $data['log_phrase_id'] = (int)$phraseId;
      }
      if (isset($module)) {
        $data['log_module'] = $module;
      }
      return $this->databaseInsertRecord($this->tableLog, 'log_id', $data);
    } else {
      return TRUE;
    }
  }

  /**
  * Return translated phrase
  *
  * @param string $phrase Phrase
  * @param mixed $module Modul
  * @access public
  * @return mixed translation or FALSE
  */
  function getTranslation($phrase, $module) {
    $iPhrase = strToLower($phrase);
    $iModule = strToLower(((trim($module) != '') ? $module : 'default'));
    if (!isset($this->modules[$iModule]['phrases'])) {
      // module is not yet loaded - load it
      if (!$this->loadModule($iModule)) {
        // module doesn't exist
        if (!$this->getModuleId($iModule)) {
          $this->addModule($module);
        }
        $this->modules[$iModule]['phrases'] = array();
      }
    }
    if (isset($this->modules[$iModule]['phrases'])) {
      if (isset($this->modules[$iModule]['phrases'][$iPhrase])) {
        // translation from module
        return $this->modules[$iModule]['phrases'][$iPhrase];
      } elseif (isset($this->modules[$iModule]['error'][$iPhrase])) {
        // already known as erroneous
        return FALSE;
      } else {
        // translation doesn't exist in loaded module
        if ($translation = $this->loadTranslation($phrase)) {
          if (isset($translation[1])) {
            // translation found - linked to module
            $this->addRelation($iModule, $translation[0]);
            $this->modules[$iModule]['phrases'][$iPhrase] = $translation[1];
            return $translation[1];
          } else {
            // translation of this phrase doesn't exist - log it
            $this->modules[$iModule]['error'][$iPhrase] = TRUE;
            $this->addPhraseLogMsg(
              'Translation for phrase not found',
              $phrase,
              $translation[0],
              $module
            );
          }
        } else {
          // phrase doesn't exist - log it
          $this->modules[$iModule]['error'][$iPhrase] = TRUE;
          $this->addPhraseLogMsg(
            sprintf('Phrase not found (%s).', $phrase),
            $phrase,
            NULL,
            $module
          );
        }
      }
    }
    return FALSE;
  }

  /**
  * Get translation via function getTranslation()
  *
  * @param string $phrase Phrase
  * @param mixed $module Modul
  * @access public
  * @return string available translation will be returned, otherwise phrase will be returned
  */
  function getText($phrase, $module = NULL) {
    if (
      $result = $this->getTranslation(
        $phrase, (isset($module) ? $module : $this->getDefaultModule())
      )
    ) {
      return $result;
    } else {
      return $phrase;
    }
  }

  /**
  * Translate a string with format attributes
  *
  * @param string $phrase Phrase
  * @param mixed $module Modul
  * @param array $vals parameter
  * @access public
  * @return string
  */
  function getTextFmt($phrase, $vals, $module = NULL) {
    if (!is_array($vals)) {
      $vals = array($vals);
    }
    $result = $this->getTranslation(
      $phrase,
      isset($module) ? $module : $this->getDefaultModule()
    );
    if (empty($result)) {
      $result = $phrase;
    }
    if (isset($vals) && is_array($vals)) {
      return vsprintf($result, $vals);
    }
    return $result;
  }

  /**
  * Get default module from current url
  *
  * @access private
  * @return string
  */
  function getDefaultModule() {
    if (isset($this->defaultModule)) {
      return $this->defaultModule;
    } else {
      $fileNamePattern = '#^(([^\?]*)/)?([^?]+)(\.\d+)(\.(php|html))(\?.*)?#i';
      $pathNamePattern = '#^(([^\?]*)/)?([^?]+)(\?.*)?#';
      if (preg_match($fileNamePattern, $_SERVER['REQUEST_URI'], $regs)) {
        $result = basename($regs[3].$regs[5]);
      } elseif (preg_match($pathNamePattern, $_SERVER['REQUEST_URI'], $regs)) {
        $result = basename($regs[3]);
      } else {
        $result = basename($_SERVER['SCRIPT_FILENAME']);
      }
      $this->defaultModule = $result;
      return $result;
    }
  }
}

