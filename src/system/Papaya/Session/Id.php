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
* Papaya Session Id Handling, Read the session from different source, check if they exists in
* different sources.
*
* @package Papaya-Library
* @subpackage Session
*/
class PapayaSessionId extends \Papaya\Application\BaseObject {

  const SOURCE_ANY = 0;
  const SOURCE_COOKIE = 1;
  const SOURCE_PATH = 2;
  const SOURCE_QUERY = 4;
  const SOURCE_BODY = 8;
  // SOURCE QUERY | SOURCE_BODY
  const SOURCE_PARAMETER = 12;

  private $_name = 'sid';
  private $_id = NULL;

  private $_validationPattern = '([a-zA-Z\d,-]{20,40})';

  /**
  * The session id needs a name. The default name is 'sid'. Use the constrcutor argument to
  * change it.
  *
  * @param string $name
  */
  public function __construct($name = 'sid') {
    \PapayaUtilConstraints::assertString($name);
    \PapayaUtilConstraints::assertNotEmpty($name);
    $this->_name = $name;
  }

  /**
  * Allow to cast the object into an string returning the current session id.
  *
  * @return string
  */
  public function __toString() {
    return $this->getId();
  }

  /**
  * Read the session id from the different sources an return it. The sources are
  * cookie, path (rewrite) and parameters (post and get).
  *
  * @return string
  */
  public function getId() {
    switch (TRUE) {
    case (isset($this->_id)) :
      return $this->_id;
    case ($id = $this->_readCookie()) :
    case ($id = $this->_readPath()) :
    case ($id = $this->_readBody()) :
    case ($id = $this->_readQuery()) :
      return $this->_id = $id;
    }
    return '';
  }

  /**
  * Resturn the sesion name stored on object creation.
  *
  * @return string
  */
  public function getName() {
    return $this->_name;
  }

  /**
  * Test if the session id exists in any of the given sources.
  *
  * @param integer $source
  * @return boolean
  */
  public function existsIn($source = self::SOURCE_ANY) {
    switch (TRUE) {
    case ($source == self::SOURCE_ANY) :
      return (boolean)$this->getId();
    case (($source & self::SOURCE_COOKIE) && $this->_readCookie()) :
      return TRUE;
    case (($source & self::SOURCE_PATH) && $this->_readPath()) :
      return TRUE;
    case (($source & self::SOURCE_QUERY) && $this->_readQuery()) :
      return TRUE;
    case (($source & self::SOURCE_BODY) && $this->_readBody()) :
      return TRUE;
    }
    return FALSE;
  }

  /**
   * Validate the syntax of a session id. Return id if valid, NULL if not.
   *
   * @param string|NULL $id
   * @return NULL|string
   */
  public function validate($id) {
    if (preg_match($this->_validationPattern, $id)) {
      return $id;
    } else {
      return NULL;
    }
  }

  /**
  * Read a valid session id from the cookies if available.
  *
  * return string|NULL
  */
  private function _readCookie() {
    $id = $this->papaya()->request->getParameter(
      $this->_name, '', NULL, Papaya\Request::SOURCE_COOKIE
    );
    if ($id && $this->_isCookieUnique()) {
      return $this->validate($id);
    }
    return NULL;
  }

  /**
  * Read a valid session id from the url path of the request uri if available.
  *
  * return string|NULL
  */
  private function _readPath() {
    $parameter = $this->papaya()->request->getParameter(
      'session', '', NULL, Papaya\Request::SOURCE_PATH
    );
    if (0 === strpos($parameter, $this->_name)) {
      $id = substr($parameter, strlen($this->_name));
      return $this->validate($id);
    } elseif ($this->_name != 'sid' && 0 === strpos($parameter, 'sid')) {
      $id = substr($parameter, 3);
      return $this->validate($id);
    }
    return NULL;
  }

  /**
  * Read a valid session id from the querystring of the request uri if available.
  *
  * return string|NULL
  */
  private function _readQuery() {
    $id = $this->papaya()->request->getParameter(
      $this->_name, '', NULL, Papaya\Request::SOURCE_QUERY
    );
    return $this->validate($id);
  }

  /**
  * Read a valid session id from the request body if available.
  *
  * return string|NULL
  */
  private function _readBody() {
    $id = $this->papaya()->request->getParameter(
      $this->_name, '', NULL, Papaya\Request::SOURCE_BODY
    );
    return $this->validate($id);
  }

  /**
  * Validate that the cookie is unique. If a cookie was send for a higher level (domain, path)
  * it is possible that the browser sends more than one sid cookie back. Here is no way to identify
  * the right one.
  *
  * If this happens you have to change the session name to resolve the conflict. The system will
  * ignore the cookies until you resolved the conflict.
  *
  * If no cookie is providied the method will return TRUE, too.
  *
  * @return boolean
  */
  public function _isCookieUnique() {
    $pattern = '((?:^|;\s*)'.preg_quote($this->_name).'=(?<sid>[^\s;=]+))';
    if (!empty($_SERVER['HTTP_COOKIE']) &&
        substr_count($_SERVER['HTTP_COOKIE'], $this->_name.'=') > 1 &&
        preg_match_all($pattern, $_SERVER['HTTP_COOKIE'], $cookieMatches, PREG_PATTERN_ORDER)) {
      if (count(array_unique($cookieMatches['sid'])) > 1) {
        return FALSE;
      }
    }
    return TRUE;
  }
}
