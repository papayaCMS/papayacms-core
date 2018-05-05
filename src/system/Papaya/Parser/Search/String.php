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

class PapayaParserSearchString implements \IteratorAggregate {

  /**
   * @var string
   */
  public $searchFor;

  /**
   * @var array
   */
  private $_tokens = [];
  /**
   * @var boolean
   */
  private $_ignoreConnector = TRUE;

  public function __construct($searchFor) {
    $this->searchFor = $searchFor;
    $this->parse($searchFor);
  }

  private function parse($searchFor) {
    $count = 0;
    $token = $this->createToken();
    $this->_ignoreConnector = TRUE;
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
          $token['value'] .= $c;
        }
        $escaped = !($escaped);
        break;
      case '+':
        if ($escaped) {
          $token['value'] .= $c;
          $escaped = FALSE;
        } elseif ($inToken) {
          $token['value'] .= $c;
        } else {
          $token['mode'] = '+';
        }
        break;
      case '-':
        if ($escaped) {
          $token['value'] .= $c;
          $escaped = FALSE;
        } elseif ($inToken) {
          $token['value'] .= $c;
        } else {
          $token['mode'] = '-';
        }
        break;
      case '"':
        if ($escaped && $inToken) {
          $token['value'] .= $c;
          $escaped = FALSE;
        } elseif ($inQuotes && $inToken) {
          $count += $this->addToken($token);
          $token = $this->createToken();
          $inToken = FALSE;
          $inQuotes = FALSE;
        } elseif ((!$inQuotes) && $inToken) {
          $token['value'] .= $c;
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
          $token['value'] .= $c;
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
          $token['value'] .= $c;
        }
        break;
      case ' ':
        if ($inToken && $inQuotes) {
          $token['value'] .= $c;
        } elseif ($inToken) {
          $count += $this->addToken($token);
          $token = $this->createToken();
          $inToken = FALSE;
        }
        break;
      default:
        $inToken = TRUE;
        $token['value'] .= $c;
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
   * Add element token
   *
   * @param array $token
   * @access public
   * @return boolean
   */
  private function addElementToken($token) {
    $this->_tokens[] = array(
      'mode' => $token['mode'],
      'value' => $token['value'],
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
  private function addToken($token) {
    if (isset($token) && is_array($token)) {
      if (!($token['quotes'])) {
        $str = trim(strtolower($token['value']));
        if ($this->_ignoreConnector) {
          if ((strlen($str) > 0) && ($str != 'or') && ($str != 'and')) {
            if ($this->addElementToken($token)) {
              $this->_ignoreConnector = FALSE;
              return 1;
            }
          }
        } else {
          switch($str) {
          case 'and':
            $this->_tokens[] = array('mode' => ':', 'value' => 'AND');
            $this->_ignoreConnector = TRUE;
            break;
          case 'or':
            $this->_tokens[] = array('mode' => ':', 'value' => 'OR');
            $this->_ignoreConnector = TRUE;
            break;
          default:
            if (strlen($str) > 0) {
              if ($this->addElementToken($token)) {
                $this->_ignoreConnector = FALSE;
                return 1;
              }
            }
          }
        }
      } else {
        if (strlen($token['value']) > 0) {
          if ($this->addElementToken($token)) {
            $this->_ignoreConnector = FALSE;
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
  private function createToken() {
    return array('mode' => '+' , 'value' => '', 'quotes' => FALSE);
  }

  /**
   * Open token group
   *
   * @param integer $level
   * @access public
   * @return integer
   */
  function openTokenGroup($level) {
    $this->_tokens[] = array('mode' => '(', 'value' => $level + 1);
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
      $lastToken = end($this->_tokens);
      if (isset($lastToken) && ($lastToken['mode'] != '(') &&
        ($lastToken['mode'] != ':')) {
        $this->_tokens[] = array('mode' => ')', 'value' => $level);
        return $level - 1;
      } elseif (isset($lastToken)) {
        array_pop($this->_tokens);
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

  public function getIterator() {
    return new \ArrayIterator($this->_tokens);
  }

}
