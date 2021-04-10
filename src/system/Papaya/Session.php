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
namespace Papaya {

  use Papaya\BaseObject\DeclaredProperties;
  use Papaya\BaseObject\Interfaces\Properties;
  use Papaya\Configuration\CMS;
  use Papaya\Session\ConsentCookie;
  use Papaya\Session\Id as SessionId;
  use Papaya\Session\Redirect as RedirectAlias;
  use Papaya\Session\Values as SessionValues;
  use Papaya\Session\Options as SessionOptions;
  use Papaya\Session\Wrapper as SessionWrapper;

  /**
   * Papaya Session Handling, initialize, start, close and destroy session, give access to the the
   * session values.
   *
   * @package Papaya-Library
   * @subpackage Session
   *
   * @property-read bool $active
   * @property-read string $name
   * @property-read string $id
   * @property-read SessionValues $values
   * @property-read SessionOptions $options
   */
  class Session implements Application\Access, Properties {
    use Application\Access\Aggregation;
    use DeclaredProperties;

    const ACTIVATION_ALWAYS = 1;

    const ACTIVATION_NEVER = 2;

    const ACTIVATION_DYNAMIC = 3;

    /**
     * Internal storage vor values subobject
     *
     * @var SessionValues
     */
    private $_values;

    /**
     * Session options
     *
     * @var SessionOptions
     */
    private $_options;

    /**
     * Session function wrapper
     *
     * @var SessionWrapper
     */
    private $_wrapper;

    /**
     * Session Identifier encapsulation
     *
     * @var SessionId
     */
    private $_id;

    /**
     * Session name
     */
    private $_sessionName = 'sid';

    /**
     * session ist started and active
     *
     * @var bool
     */
    private $_active = FALSE;

    /**
     * @var bool|null
     */
    private $_isAdministration;

    private $_consentCookie;

    /**
     * Set the session name (include sid)
     *
     * @param string $name
     */
    public function setName($name) {
      Utility\Constraints::assertString($name);
      Utility\Constraints::assertNotEmpty($name);
      $this->_sessionName = $name;
    }

    /**
     * check if the session is active
     *
     * @return bool
     */
    public function isActive() {
      return $this->_active;
    }

    /**
     * Allows to get/set the values subobject. The subobject provides an array access interface to
     * the session values.
     *
     * @param SessionValues $values
     *
     * @return SessionValues
     */
    public function values(Session\Values $values = NULL) {
      if (NULL !== $values) {
        $this->_values = $values;
      }
      if (NULL === $this->_values) {
        $this->_values = new Session\Values($this);
      }
      return $this->_values;
    }

    /**
     * Getter/Setter for session options object
     *
     * @param Session\Options $options
     *
     * @return Session\Options
     */
    public function options(Session\Options $options = NULL) {
      if (NULL !== $options) {
        $this->_options = $options;
      }
      if (NULL === $this->_options) {
        $this->_options = new Session\Options();
      }
      return $this->_options;
    }

    /**
     * Getter/Setter for session identifier object
     *
     * @param SessionId $id
     *
     * @return SessionId
     */
    public function id(Session\Id $id = NULL) {
      if (NULL !== $id) {
        $this->_id = $id;
      }
      if (NULL === $this->_id) {
        $this->_id = new Session\Id($this->_sessionName);
      }
      return $this->_id;
    }

    /**
     * Getter/Setter for session options object
     *
     * @param SessionWrapper $wrapper
     *
     * @return SessionWrapper
     */
    public function wrapper(Session\Wrapper $wrapper = NULL) {
      if (NULL !== $wrapper) {
        $this->_wrapper = $wrapper;
      }
      if (NULL === $this->_wrapper) {
        $this->_wrapper = new Session\Wrapper();
      }
      return $this->_wrapper;
    }

    /**
     * @return array
     */
    public function getPropertyDeclaration() {
      return [
        'active' => ['isActive'],
        'name' => ['_sessionName'],
        'id' => [function () { return (string)$this->id(); }],
        'isAdministration' => ['isAdministration'],
        'values' => ['values'],
        'options' => ['options'],
      ];
    }

    /**
     * For backwards compatibility add a shortcut to the values.
     *
     * @param string|array $name
     * @param mixed $value
     */
    public function setValue($name, $value) {
      $this->values->set($name, $value);
    }

    /**
     * For backwards compatibility add a shortcut to the values.
     *
     * @param string|array $name
     *
     * @param null $defaultValue
     * @param Filter|null $filter
     * @return mixed
     */
    public function getValue($name, $defaultValue = NULL, Filter $filter = NULL) {
      return $this->values->get($name, $defaultValue, $filter);
    }

    /**
     * Check if session is possible with this protocol and user agent.
     *
     * @return bool
     */
    public function isAllowed() {
      return $this->isProtocolAllowed() && !Utility\Server\Agent::isRobot();
    }

    /**
     * Check if the options allow the session on the current protocol.
     *
     * @return bool
     */
    public function isProtocolAllowed() {
      if ($this->isSecureOnly()) {
        if (Utility\Server\Protocol::isSecure()) {
          return TRUE;
        }
        return FALSE;
      }
      return TRUE;
    }

    /**
     * Check if the request should be secure only (delivered over https)
     *
     * PAPAYA_SESSION_SECURE sets both (page and administration interface) to secure mode.
     *
     * PAPAYA_UI_SECURE sets only the administration interface and previews to secure mode.
     *
     * @return bool
     */
    public function isSecureOnly() {
      $options = $this->papaya()->options;
      if ($options->get(\Papaya\Configuration\CMS::SESSION_SECURE, FALSE)) {
        return TRUE;
      }
      if (
        $options->get(\Papaya\Configuration\CMS::UI_SECURE, FALSE) &&
        $this->isAdministration()
      ) {
        return TRUE;
      }
      return FALSE;
    }

    /**
     * @param bool $isAdministration
     * @return bool
     */
    public function isAdministration($isAdministration = NULL) {
      if (
        NULL !== $isAdministration &&
        $isAdministration !== $this->_isAdministration
      ) {
        if ($this->isActive()) {
          throw new \LogicException('Active sessions can not be changed.');
        }
        if ($isAdministration) {
          $this->setName(
            'sid'.$this->papaya()->options->get(\Papaya\Configuration\CMS::SESSION_NAME, '').'admin'
          );
          $this->options->cache = Session\Options::CACHE_NONE;
        } else {
          $this->setName(
            'sid'.$this->papaya()->options->get(\Papaya\Configuration\CMS::SESSION_NAME, '')
          );
          $this->options->cache = $this->papaya()->options->get(
            'PAPAYA_SESSION_CACHE', Session\Options::CACHE_PRIVATE
          );
        }
        $this->_isAdministration = (bool)$isAdministration;
      }
      return $this->_isAdministration;
    }

    /**
     * Initialize session object, create or load session, create and returns an redirect reponse if
     * needed.
     *
     * If the method returns a redirect response, the caller should send it.
     *
     * @param bool|string $redirect
     *
     * @return null|RedirectAlias redirect response or null
     */
    public function activate($redirect = FALSE) {
      if (!$this->_active) {
        if ($this->isAllowed()) {
          $wrapper = $this->wrapper();
          $wrapper->setName($this->_sessionName);
          $this->configure();
          if ($this->id()->existsIn(Session\Id::SOURCE_ANY)) {
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
      if ($this->hasCookieConsent()) {
        $defaults = $wrapper->getCookieParameters();
        $wrapper->setCookieParameters(
          [
            'lifetime' => $defaults['lifetime'],
            'path' => $options->get(
              'PAPAYA_SESSION_PATH', '/', new Filter\NotEmpty()
            ),
            'domain' => $options->get(
              'PAPAYA_SESSION_DOMAIN', $defaults['domain'], new Filter\NotEmpty()
            ),
            'SameSite' => 'Strict',
            'secure' => $this->isSecureOnly(),
            'httponly' => $options->get(\Papaya\Configuration\CMS::SESSION_HTTP_ONLY, $defaults['httponly']),
          ]
        );
      } else {
        ini_set('session.use_cookies', '0');
      }
      $wrapper->setCacheLimiter($this->options()->cache);
    }

    private function hasCookieConsent() {
      $options = $this->papaya()->options;
      if (
        $this->isAdministration() ||
        $this->consentCookie()->hasLevel(ConsentCookie::LEVEL_BASIC)
      ) {
        return TRUE;
      }
    }

    public function consentCookie(ConsentCookie $consentCookie = NULL) {
      if (NULL !== $consentCookie) {
        $this->_consentCookie = $consentCookie;
      } elseif (NULL === $this->_consentCookie) {
        $this->_consentCookie = new ConsentCookie();
        $this->_consentCookie->papaya($this->papaya());
      }
      return $this->_consentCookie;
    }

    /**
     * Trigger redirects for session id storage/removal in browser if needed.
     *
     * @return null|RedirectAlias
     */
    public function redirectIfNeeded() {
      if (
        $this->_active &&
        !$this->id()->existsIn(Session\Id::SOURCE_COOKIE)
      ) {
        switch ($this->options()->fallback) {
        case Session\Options::FALLBACK_REWRITE :
          // put sid in path if it is not here and remove it from query string if it is there
          if (
            !$this->id()->existsIn(Session\Id::SOURCE_PATH) ||
            $this->id()->existsIn(Session\Id::SOURCE_QUERY)
          ) {
            return $this->_createRedirect(
              Session\Id::SOURCE_PATH, 'session rewrite active'
            );
          }
          break;
        case Session\Options::FALLBACK_PARAMETER :
          // remove sid from path if it is there
          if ($this->id()->existsIn(Session\Id::SOURCE_PATH)) {
            return $this->_createRedirect(
              Session\Id::SOURCE_QUERY, 'session rewrite inactive'
            );
          }
          break;
        }
        return NULL;
      }
      if (
      $this->id()->existsIn(
        Session\Id::SOURCE_PATH | Session\Id::SOURCE_QUERY
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
        $_SESSION = [];
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
     * @param string $targetURL
     *
     * @return RedirectAlias|false
     */
    public function regenerateId($targetURL = NULL) {
      if ($this->_active) {
        $this->wrapper()->regenerateId();
        if (NULL !== $targetURL || $this->id()->existsIn(Session\Id::SOURCE_PATH)) {
          $transports = [
            Session\Id::SOURCE_COOKIE,
            Session\Id::SOURCE_PATH,
            Session\Id::SOURCE_QUERY
          ];
          $transport = Session\Id::SOURCE_COOKIE;
          foreach ($transports as $transport) {
            if ($this->id()->existsIn($transport)) {
              break;
            }
          }
          $redirect = $this->_createRedirect($transport, 'session id regeneration');
          if (NULL !== $targetURL) {
            $redirect->url()->setURLString($targetURL);
          }
          return $redirect;
        }
      }
      return FALSE;
    }

    /**
     * Redirect to add or remove session id from url.
     *
     * @param int $transport
     * @param string $reason
     *
     * @return RedirectAlias
     */
    private function _createRedirect($transport = 0, $reason = 'session redirect') {
      // remove sid from path and/or query string
      $redirect = new Session\Redirect(
        $this->_sessionName, $this->wrapper()->getId(), $transport, $reason
      );
      $redirect->papaya($this->papaya());
      return $redirect;
    }
  }
}
