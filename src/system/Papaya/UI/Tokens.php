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
namespace Papaya\UI;

use Papaya\Application;
use Papaya\Utility;

/**
 * Provides some function to get random values
 *
 * @package Papaya-Library
 * @subpackage UI
 */
class Tokens implements Application\Access, \IteratorAggregate {
  use Application\Access\Aggregation;

  /**
   * Maximum of token in list until the GC is triggered.
   *
   * @var int
   */
  private $_maximum;

  /**
   * Actual tokens
   *
   * @var array
   */
  protected $_tokens;

  /**
   * Construct the object and initialize the maximum token count.
   *
   * The integrated GC will reduce the token list by half. So dont make the limit the small.
   *
   * @param int $maximum
   */
  public function __construct($maximum = 200) {
    Utility\Constraints::assertInteger($maximum);
    $this->_maximum = (int)$maximum;
  }

  public function getMaximum(): int {
    return $this->_maximum;
  }

  public function getIterator(): \ArrayIterator {
    return new \ArrayIterator($this->_tokens);
  }

  /**
   * Create a new random token and append it to the list.
   *
   * @param mixed $for
   * @param int $expires Seconds until the token expires
   *
   * @return string|null $token New token
   */
  public function create($for = '', $expires = -1) {
    Utility\Constraints::assertInteger($expires);
    if (!isset($this->papaya()->session) ||
      !$this->papaya()->session->isActive()) {
      return NULL;
    }
    $this->loadTokens();
    if (NULL !== $this->_tokens && \count($this->_tokens) >= $this->_maximum) {
      $this->cleanup();
    }
    do {
      $token = $this->getTokenHash();
    } while (isset($this->_tokens[$token]));
    $this->_tokens[$token] = [
      ($expires < 0) ? NULL : \time() + $expires,
      $this->getVerification($for)
    ];
    $this->storeTokens();
    return $token;
  }

  /**
   * Validate a token and remove it
   *
   * This check if a tokens exists, is not expired and is for the given action.
   *
   * If the function return TRUE, the token is removed from the list, a second call will always
   * return FALSE.
   *
   * @param string $token
   * @param mixed $for
   *
   * @return bool
   */
  public function validate($token, $for = '') {
    Utility\Constraints::assertString($token);
    if (!$this->papaya()->session->isActive()) {
      return TRUE;
    }
    $this->loadTokens();
    if (isset($this->_tokens[$token])) {
      [$validUntil, $verification] = $this->_tokens[$token];
      if (NULL === $validUntil || $validUntil > \time()) {
        if ($verification === $this->getVerification($for)) {
          unset($this->_tokens[$token]);
          $this->storeTokens();
          return TRUE;
        }
      }
    }
    return FALSE;
  }

  /**
   * Cleanup old tokens
   *
   * The function will first remove tokens which are already expired. After this it will reduce the
   * list to half of the maximum property be removing tokens from the begin of the list.
   */
  protected function cleanup() {
    $now = \time();
    foreach ($this->_tokens as $key => [$validUntil]) {
      if (NULL !== $validUntil && $now > $validUntil) {
        unset($this->_tokens[$key]);
      }
    }
    $reduceTo = \ceil($this->_maximum / 2);
    $count = \count($this->_tokens);
    if ($count > $reduceTo) {
      \array_splice($this->_tokens, 0, $count - $reduceTo);
    }
  }

  /**
   * Get a random md5 hash
   *
   * @return string
   */
  protected function getTokenHash() {
    return \md5(Utility\Random::getId());
  }

  /**
   * Load token list from session
   *
   * Initialize the token list from session if it is not already done or if it is forced.
   *
   * @param mixed $force
   */
  protected function loadTokens($force = FALSE) {
    if (NULL === $this->_tokens || $force) {
      $this->_tokens = $this->papaya()->session->values->get($this) ?: [];
    }
  }

  /**
   * Store token into session
   */
  protected function storeTokens() {
    $this->papaya()->session->values->set($this, $this->_tokens);
  }

  /**
   * Get a verification string from a given mixed value.
   *
   * If $for is an object the class is used. If it is an string it is used directly.
   *
   * If it is an array each element is used like given diretly but an array element will
   * be serialized (to avoid recursion).
   *
   * @param mixed $for
   *
   * @return string md5 checksum
   */
  protected function getVerification($for) {
    $result = '';
    if (\is_array($for)) {
      $result = '';
      foreach ($for as $part) {
        if (\is_object($part)) {
          $result .= '_'.\get_class($part);
        } elseif (\is_array($part)) {
          $result .= '_'.\md5(\serialize($part));
        } else {
          $result .= '_'.((string)$part);
        }
      }
      $result = \substr($result, 1);
    } elseif (\is_object($for)) {
      $result = \get_class($for);
    } elseif (\is_string($for)) {
      $result = (string)$for;
    }
    return \md5($result);
  }
}
