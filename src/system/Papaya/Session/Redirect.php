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

namespace Papaya\Session;
/**
 * Papaya Session Redirect, special response object for session redirects (needed to add/remove)
 * the session id to the url if the cookie is not available
 *
 * @package Papaya-Library
 * @subpackage Session
 */
class Redirect extends \Papaya\Response {

  /**
   * session name - used as parameter name, too.
   *
   * @var string
   */
  private $_sessionName = 'sid';
  /**
   * session id, can be empty
   *
   * @var string
   */
  private $_sessionId = '';
  /**
   * transportation target for the session id parameter
   *
   * @var integer
   */
  private $_transport = 0;
  /**
   * redirect reason (for debugging), creates an custom http header
   *
   * @var string
   */
  private $_reason = 'session';

  /**
   * url handling object
   *
   * @var \Papaya\URL
   */
  private $_url = NULL;

  /**
   * Initialize object and store parameters for later use
   *
   * @param string $sessionName
   * @param string $sessionId
   * @param integer $transport
   * @param string $reason
   */
  public function __construct($sessionName, $sessionId = '', $transport = 0, $reason = 'session') {
    $this->_sessionName = $sessionName;
    $this->_sessionId = $sessionId;
    $this->_transport = $transport;
    $this->_reason = $reason;
  }

  /**
   * Getter/Setter for the redirect target url object
   *
   * @param \Papaya\URL $url
   * @return \Papaya\URL
   */
  public function url(\Papaya\URL $url = NULL) {
    if (isset($url)) {
      $this->_url = $url;
    }
    if (is_null($this->_url)) {
      $this->_url = clone $this->papaya()->request->getURL();
    }
    return $this->_url;
  }

  /**
   * Prepare the redirect, compile target url, set statusm, cache and headers.
   */
  public function prepare() {
    $this->_setQueryParameter(
      $this->_sessionName, $this->_sessionId, $this->_transport & \Papaya\Session\Id::SOURCE_QUERY
    );
    $this->_setPathParameter(
      $this->_sessionName, $this->_sessionId, $this->_transport & \Papaya\Session\Id::SOURCE_PATH
    );
    $this->setStatus(302);
    $this->setCache('none');
    $this->headers()->set('X-Papaya-Redirect', $this->_reason);
    $this->headers()->set('Location', $this->url()->getURL());
  }

  /**
   * Send the redirect to the client (browser)
   *
   * @param bool $end
   * @param bool $force
   */
  public function send($end = FALSE, $force = FALSE) {
    $this->prepare();
    parent::send($end, $force);
  }

  /**
   * Set/Remove the session id query parameter
   *
   * @param string $sessionName
   * @param string $sessionId
   * @param boolean $include Include session id in query string
   */
  private function _setQueryParameter($sessionName, $sessionId, $include) {
    $application = $this->papaya();
    $query = new \Papaya\Request\Parameters\QueryString($application->request->getParameterGroupSeparator());
    $query->setString($this->url()->getQuery());
    $query->values()->merge(
      $application->request->getParameters(\Papaya\Request::SOURCE_QUERY)
    );
    if ($include) {
      $query->values()->set($sessionName, $sessionId);
    } else {
      $query->values()->remove($sessionName);
    }
    $this->url()->setQuery($query->getString());
  }

  /**
   * Set/Remove the session id into/from path
   *
   * @param string $sessionName
   * @param string $sessionId
   * @param boolean $include Include session id in query string
   */
  private function _setPathParameter($sessionName, $sessionId, $include) {
    $url = $this->url();
    $pattern = '(^/sid[^/]+)';
    $replacement = ($include && !empty($sessionId)) ? '/'.$sessionName.$sessionId : '';
    $path = $url->getPath();
    if (preg_match($pattern, $path)) {
      $url->setPath(preg_replace($pattern, $replacement, $path));
    } elseif ($include) {
      $url->setPath($replacement.$path);
    }
  }
}
