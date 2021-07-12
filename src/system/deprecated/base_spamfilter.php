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
* Spam filter base class, collect text and check for spam
*
* @package Papaya
* @subpackage Spamfilter
*/
class base_spamfilter extends base_db {

  /**
  * Stop words table name
  * @var string
  */
  var $tableStopWords = PAPAYA_DB_TBL_SPAM_STOP;
  /**
  * Ignore words table name
  * @var string
  */
  var $tableIgnoreWords = PAPAYA_DB_TBL_SPAM_IGNORE;
  /**
  * Logging table name
  * @var string
  */
  var $tableSpamLog = PAPAYA_DB_TBL_SPAM_LOG;
  /**
  * References table name
  * @var string
  */
  var $tableSpamReferences = PAPAYA_DB_TBL_SPAM_REFERENCES;
  /**
  * Spam words table name
  * @var string
  */
  var $tableSpamWords = PAPAYA_DB_TBL_SPAM_WORDS;
  /**
  * Spam categories table name
  * @var string
  */
  var $tableSpamCategories = PAPAYA_DB_TBL_SPAM_CATEGORIES;

  /**
  * Minimum token length
  * @var integer
  */
  var $minTokenLength = 3;
  /**
  * Maximum token length
  * @var string
  */
  var $maxTokenLength = 20;
  /**
  * Fatal token length
  * @var integer
  */
  var $blockTokenLength = 60;

  /**
  * @var bool case sensitivity
  */
  var $caseSensitive = FALSE;

  /**
  * Non word character codes
  */
  var $nonWordChars = '\\x00-\\x26\\x28-\\x2F\\x3A-\\x3F\\x5B-\\x5F\\x7B-\\x7F';

  /**
  * ignore words list
  * @var array
  */
  var $ignoreWords = array();
  /**
  * stop words list
  * @var array
  */
  var $stopWords = array();

  /**
  * Categories list
  * @var array
  */
  var $categories = array();

  /**
  * Get spamfilter instance (Singleton)
  * @access public
  * @return base_spamfilter
  */
  public static function getInstance() {
    static $spamFilter;
    if (!(isset($spamFilter) && is_object($spamFilter))) {
      $spamFilter = new base_spamfilter();
    }
    return $spamFilter;
  }

  /**
  * Initialize stop and ignore words list for current language
  * @access private
  * @param integer $lngId
  * @return void
  */
  function _initializeWordLists($lngId) {
    if (!isset($this->stopWords[$lngId])) {
      $this->stopWords[$lngId] = $this->_loadStopWords($lngId);
    }
    if (!isset($this->ignoreWords[$lngId])) {
      $this->ignoreWords[$lngId] = $this->_loadIgnoreWords($lngId);
    }
  }

  /**
  * Check string spam probability
  * @access public
  * @param string $string
  * @param integer $lngId
  * @return array
  */
  function check($string, $lngId) {
    $this->_initializeWordLists($lngId);
    $tokenData = $this->_getTokens($string, $lngId);
    $stopWordData = $this->_countStopWords($tokenData['tokens'], $lngId);
    $scores = $this->_categorize($tokenData['tokens'], $lngId);
    if (defined('PAPAYA_SPAM_SCOREMIN_PERCENT') && PAPAYA_SPAM_SCOREMIN_PERCENT > 0 &&
        PAPAYA_SPAM_SCOREMIN_PERCENT < 100) {
      $scoreMin = PAPAYA_SPAM_SCOREMIN_PERCENT / 100;
    } else {
      $scoreMin = 0.1;
    }
    if (defined('PAPAYA_SPAM_SCOREMAX_PERCENT') && PAPAYA_SPAM_SCOREMAX_PERCENT > 0 &&
        PAPAYA_SPAM_SCOREMAX_PERCENT < 100) {
      $scoreMax = PAPAYA_SPAM_SCOREMAX_PERCENT / 100;
    } else {
      $scoreMax = 0.9;
    }
    if (defined('PAPAYA_SPAM_STOPWORD_MAX') && PAPAYA_SPAM_STOPWORD_MAX > 0 &&
        PAPAYA_SPAM_STOPWORD_MAX < 100) {
      $stopWordMax = (int)PAPAYA_SPAM_STOPWORD_MAX;
    } else {
      $stopWordMax = 10;
    }
    if (isset($scores['HAM']) &&
        (float)$scores['HAM'] > $scoreMin &&
        isset($scores['SPAM']) &&
        (float)$scores['SPAM'] < $scoreMax &&
        $stopWordData[0] < $stopWordMax) {
      $isSpam = FALSE;
    } else {
      $isSpam = TRUE;
    }
    return array(
      'spam' => $isSpam,
      'scores' => $scores,
      'stopwordcount' => $stopWordData[0],
      'stopwords' => $stopWordData[1],
      'scoretokencount' => count($tokenData['tokens']),
      'smalltokencount' => $tokenData['smalltokens'],
      'largetokencount' => $tokenData['largetokens'],
      'blocktokencount' => $tokenData['blocktokens']
    );
  }

  /**
  * Log string for spam filter training
  * @access public
  * @param string $string
  * @param integer $lngId
  * @param string $info
  * @return void
  */
  function log($string, $lngId, $info = '') {
    $this->_logText($lngId, $string, $info);
  }

  /**
  * Split string to tokens, filter and return list
  * @access private
  * @param string $string
  * @param integer $lngId
  * @return array
  */
  function _getTokens($string, $lngId) {
    $result = array(
      'tokens' => array(),
      'smalltokens' => 0,
      'largetokens' => 0,
      'blocktokens' => 0
    );
    if ($tokens = preg_split('~['.$this->nonWordChars.']+~', $string)) {
      foreach ($tokens as $token) {
        if ($this->caseSensitive) {
          $token = trim($token);
        } else {
          $token = papaya_strings::strtolower(trim($token));
        }
        if ('' == $token || isset($this->ignoreWords[$lngId][$token])) {
          //ignore token
          continue;
        } elseif (strlen($token) < $this->minTokenLength) {
          ++$result['smalltokens'];
        } else {
          if (strlen($token) > $this->blockTokenLength) {
            ++$result['blocktokens'];
          } elseif (strlen($token) > $this->maxTokenLength) {
            ++$result['largetokens'];
          }
          if (isset($result['tokens'][$token])) {
            ++$result['tokens'][$token];
          } else {
            $result['tokens'][$token] = 1;
          }
        }
      }
    }
    return $result;
  }

  /**
  * Count stopwords and tokens
  * @access private
  * @param array $tokens
  * @param integer $lngId
  * @return array
  */
  function _countStopWords($tokens, $lngId) {
    $counts = array();
    $summary = 0;
    foreach ($tokens as $token => $count) {
      if (isset($this->stopWords[$lngId][$token])) {
        $counts[$token] = $count;
        $summary += $count;
      }
    }
    return array($summary, $counts);
  }

  /**
  * Load ignore words list form database
  * @access private
  * @param integer $lngId
  * @return array
  */
  function _loadIgnoreWords($lngId) {
    $result = array();
    $sql = "SELECT spamignore_word
              FROM %s
             WHERE spamignore_lngid = '%s'
             ORDER BY spamignore_word";
    $params = array($this->tableIgnoreWords, $lngId);
    if ($res = $this->databaseQueryFmt($sql, $params)) {
      while ($row = $res->fetchRow()) {
        $result[$row[0]] = TRUE;
      }
    }
    return $result;
  }

  /**
  * Load stop words list from database
  * @access private
  * @param integer $lngId
  * @return array
  */
  function _loadStopWords($lngId) {
    $result = array();
    $sql = "SELECT spamstop_word
              FROM %s
             WHERE spamstop_lngid = '%s'
             ORDER BY spamstop_word";
    $params = array($this->tableStopWords, $lngId);
    if ($res = $this->databaseQueryFmt($sql, $params)) {
      while ($row = $res->fetchRow()) {
        $result[$row[0]] = TRUE;
      }
    }
    return $result;
  }

  /**
  * Log text to database
  * @access private
  * @param integer $lngId
  * @param string $text
  * @param string $info
  * @return boolean
  */
  function _logText($lngId, $text, $info) {
    if (defined('PAPAYA_SPAM_LOG') && PAPAYA_SPAM_LOG && trim($text) != '') {
      $data = array(
        'spamlog_lngid' => $lngId,
        'spamlog_time' => time(),
        'spamlog_text' => $text,
        'spamlog_info' => $info
      );
      return (bool)$this->databaseInsertRecord($this->tableSpamLog, 'spamlog_id', $data);
    }
    return FALSE;
  }

  /**
  * Categorize token list
  * @access private
  * @param array $tokens
  * @param integer $lngId
  * @return array
  */
  function _categorize($tokens, $lngId) {
    $scores = array();
    $filter = new \Papaya\Spam\Filter\Statistical();
    $probability = $filter->classify('', $tokens, $lngId);
    $scores['HAM'] = 1 - $probability;
    $scores['SPAM'] = $probability;
    return $scores;
  }

  /**
  * Load categories list from database
  * @access private
  * @param integer $lngId
  * @return boolean
  */
  function _loadCategories($lngId) {
    $sql = "SELECT spamcategory_ident, spamcategory_probability,
                   spamcategory_words
              FROM %s
             WHERE spamcategory_lngid = '%s'";
    $params = array($this->tableSpamCategories, $lngId);
    if ($res = $this->databaseQueryFmt($sql, $params)) {
      while ($row = $res->fetchRow(DB_FETCHMODE_ASSOC)) {
        $this->categories[$lngId][$row['spamcategory_ident']] = $row;
        if (isset($this->categories['TOTALS'][$lngId])) {
          $this->categories['TOTALS'][$lngId] += $row['spamcategory_words'];
        } else {
          $this->categories['TOTALS'][$lngId] = $row['spamcategory_words'];
        }
      }
      return TRUE;
    }
    return FALSE;
  }

  /**
  * Load word scores from database
  * @access private
  * @param array $words
  * @param integer $lngId
  * @return array|FALSE
  */
  function _loadWords($words, $lngId) {
    $filter = $this->databaseGetSQLCondition('spamword', $words);
    $sql = "SELECT spamword, spamword_count, spamcategory_ident
              FROM %s
             WHERE spamword_lngid = '%s'
               AND $filter";
    if ($res = $this->databaseQueryFmt($sql, array($this->tableSpamWords, $lngId))) {
      $result = array(
        'EXISTS' => array(),
        'DATA' => array(),
      );
      while ($row = $res->fetchRow(DB_FETCHMODE_ASSOC)) {
        $result['EXISTS'][$row['spamword']] = TRUE;
        $result['DATA'][$row['spamcategory_ident']][$row['spamword']] = $row['spamword_count'];
      }
      return $result;
    }
    return FALSE;
  }

  /**
  * Rescale the results between 0 and 1.
  *
  * @author Ken Williams, ken@mathforum.org
  * @return array normalized scores (keys => category, values => scores)
  * @param array $scores (keys => category, values => scores)
  */
  function _rescale($scores) {
    // Scale everything back to a reasonable area in
    // logspace (near zero), un-loggify, and normalize
    $total = 0.0;
    $max = 0.0;
    foreach ($scores as $cat => $score) {
      if (is_infinite($score)) {
        foreach ($scores as $key => $value) {
          $scores[$key] = (is_infinite($value) || $key === $cat) ? 1 : 0;
        }
        return $scores;
      } elseif ($score >= $max) {
        $max = $score;
      }
    }
    foreach ($scores as $cat => $score) {
      $scores[$cat] = (float)exp($score - $max);
      $total += (float)pow($scores[$cat], 2);
    }
    $total = (float)sqrt($total);
    if ($total > 0) {
      foreach ($scores as $cat => $score) {
        if (is_infinite($score)) {
          $scores[$cat] = 1;
        } elseif (is_infinite($total)) {
          $scores[$cat] = 0;
        } else {
          $scores[$cat] = (float)$scores[$cat] / $total;
        }
      }
    } else {
      foreach ($scores as $cat => $score) {
        $scores[$cat] = 0;
      }
    }
    return $scores;
  }
}

