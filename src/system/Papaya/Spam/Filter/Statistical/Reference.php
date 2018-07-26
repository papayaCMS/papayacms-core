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
* The refennce list provides the statistical spam filter with the spam/ham count for a given list of
* words.
*
* Additionally it provides the total count of texts learned as spam and ham.
*
* @package Papaya-Library
* @subpackage Spam
*/
class PapayaSpamFilterStatisticalReference extends \Papaya\Database\BaseObject\Records {

  /**
  * buffer array for the text count loaded from database
  *
  * @var array(string=>integer)
  */
  private $_totals = array(
    'ham' => 0,
    'spam' => 0
  );

  /**
  * Loads the spam/ham counts for a given list of word in a language.
  *
  *
  * @param array $words
  * @param integer $languageId
  * @return boolean
  */
  public function load(array $words, $languageId) {
    $this->_records = array();
    $this->_recordCount = 0;
    $this->_totals = array(
      'ham' => 0,
      'spam' => 0
    );
    if (!empty($words)) {
      $filter = $this->databaseGetSqlCondition('spamword', $words);
      $sql = "SELECT spamword, spamword_count, spamcategory_ident
                FROM %s
               WHERE spamword_lngid = '%s'
                 AND $filter";
      $parameters = array(
        $this->databaseGetTableName('spamwords'),
        $languageId
      );
      if ($res = $this->databaseQueryFmt($sql, $parameters)) {
        while ($row = $res->fetchRow(\Papaya\Database\Result::FETCH_ASSOC)) {
          $word = $row['spamword'];
          if (!isset($this->_records[$word])) {
            $this->_records[$word] = array(
              'word' => $word,
              'ham' => 0,
              'spam' => 0
            );
          }
          $category = strtolower($row['spamcategory_ident']);
          $this->_records[$word][$category] = $row['spamword_count'];
        }
        $this->_recordCount = count($this->_records);
        $this->loadTotals($languageId);
        return TRUE;
      }
    }
    return FALSE;
  }

  /**
  * Load the text counts for both categories from database.
  *
  * @param integer $languageId
  */
  private function loadTotals($languageId) {
    $sql = "SELECT spamcategory_ident, count(*) text_count
              FROM %s
             WHERE spamreference_lngid = '%s'
             GROUP BY spamcategory_ident";
    $parameters = array(
      $this->databaseGetTableName('spamreferences'),
      $languageId
    );
    if ($res = $this->databaseQueryFmt($sql, $parameters)) {
      while ($row = $res->fetchRow(\Papaya\Database\Result::FETCH_ASSOC)) {
        $category = strtolower($row['spamcategory_ident']);
        $this->_totals[$category] = $row['text_count'];
      }
    }
  }

  /**
  * Return the total count of texts learned as ham.
  *
  * @return integer
  */
  public function getHamCount() {
    return $this->_totals['ham'];
  }

  /**
  * Return the total count of texts learned as spam.
  *
  * @return integer
  */
  public function getSpamCount() {
    return $this->_totals['spam'];
  }

}
