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
* Wrapper class for php session functions. Allow to access them using oop.
*
* Additionally clean up the nameing and allow to mock them for tests.
*
* @package Papaya-Library
* @subpackage Session
*/
class PapayaSessionWrapper {

  /**
   * Register a {@see \PapayaSessionHandler} object, or more specific its methods in
   * {@see session_set_save_handler}.
   *
   * @param string $handler
   * @throws \InvalidArgumentException
   * @return bool
   */
  public function registerHandler($handler) {
    if (!class_exists($handler)) {
      throw new \InvalidArgumentException(
        sprintf('Invalid session handler class "%s".', $handler)
      );
    }
    return session_set_save_handler(
      array($handler, 'open'),
      array($handler, 'close'),
      array($handler, 'read'),
      array($handler, 'write'),
      array($handler, 'destroy'),
      array($handler, 'gc')
    );
  }

  /**
  * Get current session id.
  *
  * Returns an empty string if the session is not started yet.
  *
  * @see session_id()
  * @return string
  */
  public function getId() {
    return session_id();
  }

  /**
  * Set the session id. Only possible if the session is not startet yet.
  *
  * Returns the previous session id.
  *
  * @see session_id()
  * @param string $sessionId
  * @return string
  */
  public function setId($sessionId) {
    return session_id($sessionId);
  }

  /**
  * Get the current session name (parameter name).
  *
  * @see session_name()
  * @return string
  */
  public function getName() {
    return session_name();
  }

  /**
   * Change the session name (parameter name). Only possible if the session is not startet yet.
   *
   * Returns the previous session name value.
   *
   * @see session_name()
   * @param string $sessionName
   * @return string
   */
  public function setName($sessionName) {
    return session_name($sessionName);
  }

  /**
  * Start the session, create/loads the session.
  *
  * @see session_start()
  * @return boolean
  */
  public function start() {
    return session_start();
  }

  /**
  * Write and close current session. This write the values into the session container and closes
  * the session for the current request. Because a session is locked while open. This allows
  * other requests to be processed.
  *
  * @see session_write_close()
  */
  public function writeClose() {
    session_write_close();
    return;
  }

  /**
  * Create a new session id, but keep the values. Used for security reasons for example after
  * logins.
  *
  * @see session_regenerate_id()
  * @return boolean
  */
  public function regenerateId() {
    return session_regenerate_id(TRUE);
  }

  /**
  * Destroy the session, delete values and kill session.
  *
  * @see session_unset()
  * @see session_destroy()
  */
  public function destroy() {
    session_unset();
    session_destroy();
    $_SESSION = array();
    return;
  }

  /**
  * Return the current session cookie parameters.
  *
  * @see session_get_cookie_params()
  * @return array
  */
  public function getCookieParams() {
    return session_get_cookie_params();
  }

  /**
  * Change the session cookie parameters
  *
  * @see session_set_cookie_params()
  * @param array $cookieParams
  */
  public function setCookieParams(array $cookieParams) {
    session_set_cookie_params(
      $cookieParams['lifetime'],
      $cookieParams['path'],
      $cookieParams['domain'],
      $cookieParams['secure'],
      $cookieParams['httponly']
    );
  }

  /**
  * Get the session cache limiter
  *
  * @see session_cache_limiter()
  * @return string
  */
  public function getCacheLimiter() {
    return session_cache_limiter();
  }

  /**
   * Set the session cache limiter. Returns the previous value.
   *
   * @see session_cache_limiter()
   * @param string $cacheLimiter
   * @return string
   */
  public function setCacheLimiter($cacheLimiter) {
    return session_cache_limiter($cacheLimiter);
  }
}
