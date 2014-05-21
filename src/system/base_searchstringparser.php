<?php
/**
* String parser for search input
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
* @version $Id: base_searchstringparser.php 39662 2014-03-20 15:48:30Z weinert $
*/

/**
* String parser for search input
* @package Papaya
* @subpackage Core
*/
class searchStringParser {

  /**
  * Tokens
  * @var array $tokens
  */
  var $tokens = NULL;
  /**
  * Ignore connector
  * @var boolean $ignoreConnector
  */
  var $ignoreConnector = TRUE;

  var $tokenMinLength = 3;

  /**
  * Add element token
  *
  * @param array $token
  * @access public
  * @return boolean
  */
  function addElementToken($token) {
    $this->tokens[] = array(
      'str' => $token['str'],
      'mode' => $token['mode'],
      'quotes' => (bool)$token['quotes']
    );
    return TRUE;
  }

  /**
  * Add token
  *
  * @param array $token
  * @access public
  * @return integer
  */
  function addToken($token) {
    if (isset($token) && is_array($token)) {
      if (!($token['quotes'])) {
        $str = trim(strtolower($token['str']));
        if ($this->ignoreConnector) {
          if ((strlen($str) >= $this->tokenMinLength) && ($str != 'or') && ($str != 'and')) {
            if ($this->addElementToken($token)) {
              $this->ignoreConnector = FALSE;
              return 1;
            }
          }
        } else {
          switch($str) {
          case 'and':
            $this->tokens[] = array('str' => 'AND', 'mode' => ':');
            $this->ignoreConnector = TRUE;
            break;
          case 'or':
            $this->tokens[] = array('str' => 'OR', 'mode' => ':');
            $this->ignoreConnector = TRUE;
            break;
          default:
            if (strlen($str) >= $this->tokenMinLength) {
              if ($this->addElementToken($token)) {
                $this->ignoreConnector = FALSE;
                return 1;
              }
            }
          }
        }
      } else {
        if (strlen($token['str']) >= $this->tokenMinLength) {
          if ($this->addElementToken($token)) {
            $this->ignoreConnector = FALSE;
            return 1;
          }
        }
      }
    }
    return 0;
  }

  /**
  * Create token
  *
  * @access public
  * @return array
  */
  function createToken() {
    return array('str' => '', 'mode' => '+' , 'quotes' => FALSE);
  }

  /**
  * Open token group
  *
  * @param integer $level
  * @access public
  * @return integer
  */
  function openTokenGroup($level) {
    $this->tokens[] = array('str' => $level + 1, 'mode' => '(');
    return $level + 1;
  }

  /**
  * Close token group
  *
  * @param int $level
  * @access public
  * @return integer
  */
  function closeTokenGroup($level) {
    if ($level > 0) {
      $lastToken = end($this->tokens);
      if (isset($lastToken) && ($lastToken['mode'] != '(') &&
          ($lastToken['mode'] != ':')) {
        $this->tokens[] = array('str' => $level, 'mode' => ')');
        return $level - 1;
      } elseif (isset($lastToken)) {
        array_pop($this->tokens);
        if ($lastToken['mode'] == '(') {
          return $this->closeTokenGroup($level - 1);
        } else {
          return $this->closeTokenGroup($level);
        }
      }
      return 0;
    } else {
      return 0;
    }
  }

  /**
  * Parse
  *
  * @param string $searchFor
  * @access public
  * @return integer
  */
  function parse($searchFor) {
    unset($this->tokens);
    $count = 0;
    $token = $this->createToken();
    $this->ignoreConnector = TRUE;
    $i = 0;
    $inToken = FALSE;
    $inQuotes = FALSE;
    $escaped = FALSE;
    $groupLevel = 0;
    $groupLevel = $this->openTokenGroup($groupLevel);
    while ($i < strlen($searchFor)) {
      $c = $searchFor[$i];
      switch ($c) {
      case '\\':
        if ($escaped && $inToken) {
          $token['str'] .= $c;
        }
        $escaped = !($escaped);
        break;
      case '+':
        if ($escaped) {
          $token['str'] .= $c;
          $escaped = FALSE;
        } elseif ($inToken) {
          $token['str'] .= $c;
        } else {
          $token['mode'] = '+';
        }
        break;
      case '-':
        if ($escaped) {
          $token['str'] .= $c;
          $escaped = FALSE;
        } elseif ($inToken) {
          $token['str'] .= $c;
        } else {
          $token['mode'] = '-';
        }
        break;
      case '"':
        if ($escaped && $inToken) {
          $token['str'] .= $c;
          $escaped = FALSE;
        } elseif ($inQuotes && $inToken) {
          $count += $this->addToken($token);
          $token = $this->createToken();
          $inToken = FALSE;
          $inQuotes = FALSE;
        } elseif ((!$inQuotes) && $inToken) {
          $token['str'] .= $c;
        } else {
          $token['quotes'] = TRUE;
          $inQuotes = TRUE;
          $inToken = TRUE;
        }
        break;
      case '(':
        if (!($escaped || $inQuotes)) {
          if ($inToken) {
            $count += $this->addToken($token);
            $token = $this->createToken();
            $inToken = FALSE;
          }
          $groupLevel = $this->openTokenGroup($groupLevel);
        } else {
          $token['str'] .= $c;
        }
        break;
      case ')':
        if (!($escaped || $inQuotes)) {
          if ($inToken) {
            $count += $this->addToken($token);
            $token = $this->createToken();
            $inToken = FALSE;
          }
          $groupLevel = $this->closeTokenGroup($groupLevel);
        } else {
          $token['str'] .= $c;
        }
        break;
      case ' ':
        if ($inToken && $inQuotes) {
          $token['str'] .= $c;
        } elseif ($inToken) {
          $count += $this->addToken($token);
          $token = $this->createToken();
          $inToken = FALSE;
        }
        break;
      default:
        $inToken = TRUE;
        $token['str'] .= $c;
      }
      $i++;
    }
    if ($inToken) {
      $count += $this->addToken($token);
    }
    $this->closeTokenGroup($groupLevel);
    return $count;
  }

  /**
   * Get filter line for SQL LIKE command
   *
   * @param array $fields
   * @access public
   * @return string
   */
  function getLikeFilterLine($fields) {
    $result = "";
    $connector = '';
    $indent = 0;
    foreach ($this->tokens as $token) {
      switch ($token['mode']) {
      case '(':
        $indent++;
        $result .= $connector.'('.LF;
        $connector = '';
        break;
      case ')':
        $indent--;
        $result .= ')'.LF;
        break;
      case '+':
        $result .= $connector.'(';
        $s = '';
        foreach ($fields as $field) {
          $result .= $s.sprintf(
            "(%s LIKE '%%%s%%')",
            $field,
            addslashes($token['str'])
          );
          $s = ' OR ';
        }
        $result .= ')';
        $connector = "\n AND \n";
        break;
      case '-':
        $result .= $connector.'(NOT(';
        $s = '';
        foreach ($fields as $field) {
          $result .= $s.sprintf(
            "(%s LIKE '%%%s%%')",
            $field,
            addslashes($token['str'])
          );
          $s = ' OR ';
        }
        $result .= '))';
        $connector = "\n AND \n";
        break;
      case ':':
        $connector = "\n ".$token['str'].LF;
        continue;
      }
    }
    if ($indent > 0) {
      $result .= str_repeat("\n)", $indent);
    }
    return $result;
  }

  /**
  * Get filters for SQL LIKE command
  *
  * @param mixed $fields
  * @access public
  * @return string
  */
  function getLikeFilters($fields) {
    $result = $this->getLikeFilterLine($fields).LF;
    return $result;
  }

  /**
  * Get filter line for MySQL MATCH command
  *
  * @param string $fieldString
  * @access public
  * @return string
  */
  function getMatchFilterLine($fieldString) {
    $result = "";
    $connector = '';
    $indent = 0;
    foreach ($this->tokens as $token) {
      switch ($token['mode']) {
      case '(':
        $indent++;
        $result .= $connector.'('.LF;
        $connector = '';
        break;
      case ')':
        $indent--;
        $result .= ')'.LF;
        break;
      case '+':
        $result .= sprintf(
          "%s(MATCH (%s) AGAINST ('%s'))",
          $connector,
          $fieldString,
          addslashes($token['str'])
        );
        $connector = "\n AND \n";
        break;
      case '-':
        $result .= sprintf(
          "%s(NOT(MATCH (%s) AGAINST ('%s')))",
          $connector,
          $fieldString,
          addslashes($token['str'])
        );
        $connector = "\n AND \n";
        break;
      case ':':
        $connector = "\n ".$token['str'].LF;
        continue;
      }
    }
    if ($indent > 0) {
      $result .= str_repeat("\n)", $indent);
    }
    return $result;
  }

  /**
  * Get filters for MySQL MATCH command
  *
  * @param mixed $fields
  * @access public
  * @return string
  */
  function getMatchFilters($fields) {
    $result = '';
    $fieldGroups = array();
    if (isset($fields) && is_array($fields)) {
      foreach ($fields as $field) {
        if (strpos($field, '.') !== FALSE) {
          $table = substr($field, 0, strpos($field, '.'));
        } else {
          $table = '';
        }
        $fieldGroups[$table][] = $field;
      }
      $fieldGroups = array_values($fieldGroups);
      for ($i = 0; $i < count($fieldGroups); $i++) {
        if ($i > 0) {
          $result .= 'OR '.$this->getMatchFilterLine(implode(',', $fieldGroups[$i])).LF;
        } else {
          $result .= $this->getMatchFilterLine(implode(',', $fieldGroups[$i])).LF;
        }
      }
    } else {
      $result .= $this->getMatchFilterLine($fields).LF;
    }
    return $result;
  }

  /**
  * Get Filters for MySQL MATCH Command in Boolean Mode (MySQL > 4.1)
  *
  * @param string $fieldString
  * @access public
  * @return string
  */
  function getBooleanFilterLine($fieldString) {
    $connector = '';
    $indent = 0;
    $matchString = '';
    foreach ($this->tokens as $token) {
      switch ($token['mode']) {
      case '(':
        $indent++;
        $matchString .= $connector.' (';
        $connector = '';
        break;
      case ')':
        $indent--;
        $matchString .= ') ';
        break;
      case '+':
        if ($token['quotes']) {
          $matchString .= ' +"'.addslashes($token['str']).'"';
        } else {
          $matchString .= ' +'.addslashes($token['str']);
        }
        break;
      case '-':
        if ($token['quotes']) {
          $matchString .= ' -"'.addslashes($token['str']).'"';
        } else {
          $matchString .= ' -'.addslashes($token['str']);
        }
        break;
      case ':':
        //$connector = " ".$token['str']." ";
        continue;
      }
    }
    if ($indent > 0) {
      $matchString .= str_repeat(" )", $indent);
    }
    return sprintf(
      "(MATCH (%s) AGAINST ('%s' IN BOOLEAN MODE))", $fieldString, $matchString
    );
  }

  /**
  * Get filters for MySQL MATCH Command in Boolean Mode (MySQL > 4.1)
  *
  * @param array $fields
  * @access public
  * @return string
  */
  function getBooleanFilters($fields) {
    $result = '';
    $fieldGroups = array();
    if (isset($fields) && is_array($fields)) {
      foreach ($fields as $field) {
        if (strpos($field, '.') !== FALSE) {
          $table = substr($field, 0, strpos($field, '.'));
        } else {
          $table = '';
        }
        $fieldGroups[$table][] = $field;
      }
      $fieldGroups = array_values($fieldGroups);
      for ($i = 0; $i < count($fieldGroups); $i++) {
        if ($i > 0) {
          $result .= 'OR '.$this->getBooleanFilterLine(implode(',', $fieldGroups[$i])).LF;
        } else {
          $result .= $this->getBooleanFilterLine(implode(',', $fieldGroups[$i])).LF;
        }
      }
    } else {
      $result .= $this->getBooleanFilterLine($fields).LF;
    }
    return $result;
  }

  /**
  * Get function
  *
  * @param string $searchFor
  * @param array $fields
  * @param string $mode optional, default value 'LIKE'
  * @access public
  * @return mixed string or FALSE
  */
  function get($searchFor, $fields, $mode = 'LIKE') {
    if ($this->parse($searchFor) > 0) {
      switch (strtoupper($mode)) {
      case 'MATCH':
      case 'FULLTEXT':
        return $this->getMatchFilters($fields);
        break;
      case 'BOOL':
      case 'BOOLEAN':
        return $this->getBooleanFilters($fields);
        break;
      default:
        return $this->getLikeFilters($fields);
      }
    }
    return FALSE;
  }

  /**
   * Get SQL
   *
   * @param string $searchFor
   * @param array $fields
   * @param int $mode
   * @return mixed string or FALSE
   */
  function getSQL($searchFor, $fields, $mode = 0) {
    $filter = '';
    if (isset($fields) && is_array($fields) && count($fields) > 0) {
      switch ($mode) {
      case 2:
        $filter = $this->get($searchFor, $fields, 'BOOL');
        break;
      case 1:
        $filter = $this->get($searchFor, $fields, 'MATCH');
        break;
      default:
        $filter = $this->get($searchFor, $fields, 'LIKE');
        break;
      }
    }
    return $filter;
  }

  /**
  * Get mnogo search
  *
  * @param string $searchFor
  * @access public
  * @return string
  */
  function getMnogoSearch($searchFor) {
    if ($this->parse($searchFor) > 0) {
      $connector = '';
      $indent = 0;
      $matchString = '';
      foreach ($this->tokens as $token) {
        switch ($token['mode']) {
        case '(':
          if ($indent > 0) {
            $matchString .= $connector.' (';
            $connector = '';
          }
          $indent++;
          break;
        case ')':
          $indent--;
          if ($indent > 0) {
            $matchString .= ') ';
          }
          break;
        case '+':
          if ($token['quotes']) {
            $matchString .= ' &('.$token['str'].')';
          } else {
            $matchString .= ' &'.$token['str'];
          }
          break;
        case '-':
          if ($token['quotes']) {
            $matchString .= ' ~('.$token['str'].')';
          } else {
            $matchString .= ' ~'.$token['str'];
          }
          break;
        case ':':
          continue;
        }
      }
      if ($indent > 1) {
        $matchString .= str_repeat(" )", $indent - 1);
      }
      return substr($matchString, 1);
    }
    return $searchFor;
  }

  /**
  * get a default search string for lucene based search
  *
  * @param string $searchFor
  * @access public
  * @return string
  */
  function getLuceneSearch($searchFor) {
    if ($this->parse($searchFor) > 0) {
      $connector = '';
      $indent = 0;
      $matchString = '';
      foreach ($this->tokens as $token) {
        switch ($token['mode']) {
        case '(':
          if ($indent > 0) {
            $matchString .= $connector.' (';
            $connector = '';
          }
          $indent++;
          break;
        case ')':
          $indent--;
          if ($indent > 0) {
            $matchString .= ') ';
          }
          break;
        case '+':
        case '-':
          if ($token['quotes']) {
            $matchString .= ' '.$token['mode'].'"'.$token['str'].'"';
          } else {
            $matchString .= ' '.$token['mode'].$token['str'];
          }
          break;
        case ':':
          continue;
        }
      }
      if ($indent > 1) {
        $matchString .= str_repeat(" )", $indent - 1);
      }
      return substr($matchString, 1);
    }
    return $searchFor;
  }
}

