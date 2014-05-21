<?php
/**
* Papaya Session Handling, initialize, start, close and destroy session, give access to the the
* session values.
*
* @copyright 2010 by papaya Software GmbH - All rights reserved.
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
* @subpackage Session
* @version $Id: Session.php 39727 2014-04-07 18:02:48Z weinert $
*/

/**
* Papaya Session Handling, initialize, start, close and destroy session, give access to the the
* session values.
*
* @package Papaya-Library
* @subpackage Session
*
* @property-read boolean $active
* @property-read string $name
* @property-read string $id
* @property-read PapayaSessionValues $values
* @property-read PapayaSessionOptions $options
*/
class PapayaSession extends PapayaObject {

  const ACTIVATION_ALWAYS = 1;
  const ACTIVATION_NEVER = 2;
  const ACTIVATION_DYNAMIC = 3;

  /**
  * Internal storage vor values subobject
  * @var PapayaSessionValues
  */
  private $_values = NULL;

  /**
  * Session options
  * @var PapayaSessionOptions
  */
  private $_options = NULL;

  /**
  * Session function wrapper
  * @var PapayaSessionWrapper
  */
  private $_wrapper = NULL;

  /**
  * Session Identifier encapsulation
  * @var PapayaSessionId
  */
  private $_id = NULL;

  /**
  * Session name
  */
  private $_sessionName = 'sid';

  /**
  * session ist started and active
  * @var boolean
  */
  private $_active = FALSE;


  /**
  * Set the session name (include sid)
  *
  * @param string $name
  */
  public function setName($name) {
    PapayaUtilConstraints::assertString($name);
    PapayaUtilConstraints::assertNotEmpty($name);
    $this->_sessionName = $name;
  }

  /**
  * check if the session is active
  *
  * @return boolean
  */
  public function isActive() {
    return $this->_active;
  }

  /**
  * Allows to get/set the values subobject. The subobject provides an array access interface to
  * the session values.
  *
  * @param PapayaSessionValues $values
  * @return PapayaSessionValues
  */
  public function values(PapayaSessionValues $values = NULL) {
    if (isset($values)) {
      $this->_values = $values;
    }
    if (is_null($this->_values)) {
      $this->_values = new PapayaSessionValues($this);
    }
    return $this->_values;
  }

  /**
  * Getter/Setter for session options object
  *
  * @param PapayaSessionOptions $options
  * @return PapayaSessionOptions
  */
  public function options(PapayaSessionOptions $options = NULL) {
    if (isset($options)) {
      $this->_options = $options;
    }
    if (is_null($this->_options)) {
      $this->_options = new PapayaSessionOptions();
    }
    return $this->_options;
  }

  /**
   * Getter/Setter for session identifier object
   *
   * @param \PapayaSessionId $id
   * @return PapayaSessionId
   */
  public function id(PapayaSessionId $id = NULL) {
    if (isset($id)) {
      $this->_id = $id;
    }
    if (is_null($this->_id)) {
      $this->_id = new PapayaSessionId($this->_sessionName);
    }
    return $this->_id;
  }

  /**
   * Getter/Setter for session options object
   *
   * @param PapayaSessionWrapper $wrapper
   * @return PapayaSessionWrapper
   */
  public function wrapper(PapayaSessionWrapper $wrapper = NULL) {
    if (isset($wrapper)) {
      $this->_wrapper = $wrapper;
    }
    if (is_null($this->_wrapper)) {
      $this->_wrapper = new PapayaSessionWrapper();
    }
    return $this->_wrapper;
  }

  /**
   * Read access to dynamic properties like "values" and "options".
   *
   * By implementing "values" as property a direct array access to the values is possible:
   * $this->papaya()->session->values['name'];
   *
   * @param string $name
   * @throws UnexpectedValueException
   * @return mixed
   */
  public function __get($name) {
    switch ($name) {
    case 'active' :
      return $this->isActive();
    case 'name' :
      return $this->_sessionName;
    case 'id' :
      return (string)$this->id();
    case 'values' :
      return $this->values();
    case 'options' :
      return $this->options();
    }
    throw new UnexpectedValueException(
      sprintf(
        'Invalid property "%s" in class "%s"', $name, get_class($this)
      )
    );
  }

  /**
  * Prohibit write access to all undeclared properties
  *
  * @throws LogicException
  * @param string $name
  * @param mixed $value
  */
  public function __set($name, $value) {
    throw new LogicException(
      sprintf(
        'All dynamic properties are read only in class "%s"', get_class($this)
      )
    );
  }

  /**
  * For backwards compatibility add a shortcut to the values.
  *
  * @param string $name
  * @param mixed $value
  */
  public function setValue($name, $value) {
    $this->values->set($name, $value);
  }

  /**
  * For backwards compatibility add a shortcut to the values.
  *
  * @param string $name
  * @return mixed
  */
  public function getValue($name) {
    return $this->values->get($name);
  }

  /**
  * Check if session is possible with this protocol and user agent.
  *
  * @return boolean
  */
  public function isAllowed() {
    return $this->isProtocolAllowed() && !PapayaUtilServerAgent::isRobot();
  }

  /**
  * Check if the options allow the session on the current protocol.
  *
  * @return boolean
  */
  public function isProtocolAllowed() {
    if ($this->isSecureOnly()) {
      if (PapayaUtilServerProtocol::isSecure()) {
        return TRUE;
      } else {
        return FALSE;
      }
    } else {
      return TRUE;
    }
  }

  /**
  * Check if the request should be secure only (delivered over https)
  *
  * PAPAYA_SESSION_VALUE sets both (page and administration interface) to secure mode.
  *
  * PAPAYA_UI_SECURE sets only the administration interface to secure mode.
  *
  * @return boolean
  */
  public function isSecureOnly() {
    $options = $this->papaya()->options;
    if ($options->get('PAPAYA_SESSION_SECURE', FALSE)) {
      return TRUE;
    } elseif ($options->get('PAPAYA_ADMIN_PAGE', FALSE) &&
              $options->get('PAPAYA_UI_SECURE', FALSE)) {
      return TRUE;
    }
    return FALSE;
  }

  /**
   * Initialize session object, create or load session, create and returns an redirect reponse if
   * needed.
   *
   * If the method returns a redirect response, the caller should send it.
   *
   * @param bool|string $redirect
   * @return NULL|PapayaSessionRedirect redirect response or null
   */
  public function activate($redirect = FALSE) {
    if (!$this->_active) {
      if ($this->isAllowed()) {
        $wrapper = $this->wrapper();
        $wrapper->setName($this->_sessionName);
        $this->configure();
        if ($this->id()->existsIn(PapayaSessionId::SOURCE_ANY)) {
          $wrapper->setId((string)$this->id());
        }
        $this->_active = $wrapper->start();
      }
      if ($redirect) {
        return $this->redirectIfNeeded();
      }
    }
    return NULL;
  }

  private function configure() {
    $options = $this->papaya()->options;
    $wrapper = $this->wrapper();
    $defaults = $wrapper->getCookieParams();
    $wrapper->setCookieParams(
      array(
        'lifetime' => $defaults['lifetime'],
        'path' => $options->get(
          'PAPAYA_SESSION_PATH', '/', new PapayaFilterNotEmpty()
        ),
        'domain' => $options->get(
          'PAPAYA_SESSION_DOMAIN', $defaults['domain'], new PapayaFilterNotEmpty()
        ),
        'secure' => $this->isSecureOnly(),
        'httponly' => $options->get('PAPAYA_SESSION_HTTP_ONLY', $defaults['httponly']),
      )
    );
    $wrapper->setCacheLimiter($this->options()->cache);
  }

  /**
   * Trigger redirects for session id storage/removal in browser if needed.
   *
   * @return null|PapayaSessionRedirect
   */
  public function redirectIfNeeded() {
    if ($this->_active &&
        !$this->id()->existsIn(PapayaSessionId::SOURCE_COOKIE)) {
      switch ($this->options()->fallback) {
      case PapayaSessionOptions::FALLBACK_REWRITE :
        // put sid in path if it is not here and remove it from query string if it is there
        if (!$this->id()->existsIn(PapayaSessionId::SOURCE_PATH) ||
            $this->id()->existsIn(PapayaSessionId::SOURCE_QUERY)) {
          return $this->_createRedirect(
            PapayaSessionId::SOURCE_PATH, 'session rewrite active'
          );
        }
        break;
      case PapayaSessionOptions::FALLBACK_PARAMETER :
        // remove sid from path if it is there
        if ($this->id()->existsIn(PapayaSessionId::SOURCE_PATH)) {
          return $this->_createRedirect(
            PapayaSessionId::SOURCE_QUERY, 'session rewrite inactive'
          );
        }
        break;
      }
      return NULL;
    } elseif (
      $this->id()->existsIn(
        PapayaSessionId::SOURCE_PATH | PapayaSessionId::SOURCE_QUERY
      )
    ) {
      return $this->_createRedirect();
    }
    return NULL;
  }

  /**
  * Close session if active, to write data and release the lock
  */
  public function close() {
    if ($this->_active) {
      $wrapper = $this->wrapper();
      $wrapper->writeClose();
      $this->_active = FALSE;
    }
  }

  /**
  * Reset the session data (only if the session if active)
  */
  public function reset() {
    if ($this->_active) {
      $_SESSION = array();
    }
  }

  /**
  * Destroy the active session (delete the data container)
  */
  public function destroy() {
    if ($this->_active) {
      $this->wrapper()->destroy();
      $this->_active = FALSE;
    }
  }

  /**
   * Create a new session id, redirect if session id is in path
   *
   * @param string $targetUrl
   * @return \PapayaSessionRedirect|FALSE
   */
  public function regenerateId($targetUrl = NULL) {
    if ($this->_active) {
      $this->wrapper()->regenerateId();
      if (isset($targetUrl) || $this->id()->existsIn(PapayaSessionId::SOURCE_PATH)) {
        $transports = array(
          PapayaSessionId::SOURCE_COOKIE,
          PapayaSessionId::SOURCE_PATH,
          PapayaSessionId::SOURCE_QUERY
        );
        $transport = PapayaSessionId::SOURCE_COOKIE;
        foreach ($transports as $transport) {
          if ($this->id()->existsIn($transport)) {
            break;
          }
        }
        $redirect = $this->_createRedirect($transport, 'session id regeneration');
        if (isset($targetUrl)) {
          $redirect->url()->setUrl($targetUrl);
        }
        return $redirect;
      }
    }
    return FALSE;
  }

  /**
   * Redirect to add or remove session id from url.
   *
   * @param integer $transport
   * @param string $reason
   * @return \PapayaSessionRedirect
   */
  private function _createRedirect($transport = 0, $reason = 'session redirect') {
    // remove sid from path and/or query string
    $redirect = new PapayaSessionRedirect(
      $this->_sessionName, $this->wrapper()->getId(), $transport, $reason
    );
    $redirect->papaya($this->papaya());
    return $redirect;
  }
}