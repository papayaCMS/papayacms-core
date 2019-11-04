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
namespace Papaya\Parser\Search;

class Text implements \IteratorAggregate {

  const TOKEN_OPERATOR = ':';
  const TOKEN_INCLUDE = '+';
  const TOKEN_EXCLUDE = '-';
  const TOKEN_PARENTHESIS_START = '(';
  const TOKEN_PARENTHESIS_END = ')';
  /**
   * @var string
   */
  public $searchFor;

  /**
   * @var array
   */
  private $_tokens = [];

  /**
   * @var bool
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
    while ($i < \strlen($searchFor)) {
      $c = $searchFor[$i];
      switch ($c) {
        case '\\':
          if ($escaped && $inToken) {
            $token['value'] .= $c;
          }
          $escaped = !$escaped;
        break;
        case '+':
          if ($escaped) {
            $token['value'] .= $c;
            $escaped = FALSE;
          } elseif ($inToken) {
            $token['value'] .= $c;
          } else {
            $token['mode'] = self::TOKEN_INCLUDE;
          }
        break;
        case '-':
          if ($escaped) {
            $token['value'] .= $c;
            $escaped = FALSE;
          } elseif ($inToken) {
            $token['value'] .= $c;
          } else {
            $token['mode'] = self::TOKEN_EXCLUDE;
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
   */
  private function addElementToken($token) {
    $this->_tokens[] = [
      'mode' => $token['mode'],
      'value' => $token['value'],
      'quotes' => (bool)$token['quotes']
    ];
  }

  /**
   * Add token
   *
   * @param array $token
   *
   * @return int
   */
  private function addToken($token) {
    if (NULL !== $token && \is_array($token)) {
      if (!$token['quotes']) {
        $searchString = \strtolower(\trim($token['value']));
        if ($this->_ignoreConnector) {
          if (
            ('or' !== $searchString) &&
            ('and' !== $searchString) &&
            ('' !== $searchString)
          ) {
            $this->addElementToken($token);
            $this->_ignoreConnector = FALSE;
            return 1;
          }
        } else {
          switch ($searchString) {
            case 'and':
              $this->_tokens[] = ['mode' => self::TOKEN_OPERATOR, 'value' => 'AND'];
              $this->_ignoreConnector = TRUE;
            break;
            case 'or':
              $this->_tokens[] = ['mode' => self::TOKEN_OPERATOR, 'value' => 'OR'];
              $this->_ignoreConnector = TRUE;
            break;
            default:
              if ('' !== $searchString) {
                $this->addElementToken($token);
                $this->_ignoreConnector = FALSE;
                return 1;
              }
          }
        }
      } elseif ('' !== (string)$token['value']) {
        $this->addElementToken($token);
        $this->_ignoreConnector = FALSE;
        return 1;
      }
    }
    return 0;
  }

  /**
   * Create token
   *
   * @return array
   */
  private function createToken() {
    return ['mode' => self::TOKEN_INCLUDE, 'value' => '', 'quotes' => FALSE];
  }

  /**
   * Open token group
   *
   * @param int $level

   * @return int
   */
  private function openTokenGroup($level) {
    $this->_tokens[] = ['mode' => self::TOKEN_PARENTHESIS_START, 'value' => $level + 1];
    return $level + 1;
  }

  /**
   * Close token group
   *
   * @param int $level
   * @return int
   */
  private function closeTokenGroup($level) {
    if ($level > 0) {
      $lastToken = \end($this->_tokens);
      if (
        NULL !== $lastToken &&
        (self::TOKEN_PARENTHESIS_START !== $lastToken['mode']) &&
        (self::TOKEN_OPERATOR !== $lastToken['mode'])
      ) {
        $this->_tokens[] = ['mode' => self::TOKEN_PARENTHESIS_END, 'value' => $level];
        return $level - 1;
      }
      if (NULL !== $lastToken) {
        \array_pop($this->_tokens);
        if (self::TOKEN_PARENTHESIS_START === $lastToken['mode']) {
          return $this->closeTokenGroup($level - 1);
        }
        return $this->closeTokenGroup($level);
      }
    }
    return 0;
  }

  public function getIterator() {
    return new \ArrayIterator($this->_tokens);
  }
}
