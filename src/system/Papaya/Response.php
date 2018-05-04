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
* @package Papaya-Library
* @subpackage Response
*/
class PapayaResponse extends PapayaObject {

  /**
  * Status codes
  * @var array
  */
  private $_statusCodes = array(
    100 => 'Continue',
    101 => 'Switching Protocols',
    102 => 'Processing',
    200 => 'OK',
    201 => 'Created',
    202 => 'Accepted',
    203 => 'Non-Authoritative Information',
    204 => 'No Content',
    205 => 'Reset Content',
    206 => 'Partial Content',
    207 => 'Multi-Status',
    300 => 'Multiple Choices',
    301 => 'Moved Permanently',
    302 => 'Found',
    303 => 'See Other',
    304 => 'Not Modified',
    305 => 'Use Proxy',
    307 => 'Temporary Redirect',
    400 => 'Bad Request',
    401 => 'Authorization Required',
    402 => 'Payment Required',
    403 => 'Forbidden',
    404 => 'Not Found',
    405 => 'Method Not Allowed',
    406 => 'Not Acceptable',
    407 => 'Proxy Authentication Required',
    408 => 'Request Time-out',
    409 => 'Conflict',
    410 => 'Gone',
    411 => 'Length Required',
    412 => 'Precondition Failed',
    413 => 'Request Entity Too Large',
    414 => 'Request-URI Too Large',
    415 => 'Unsupported Media Type',
    416 => 'Requested Range Not Satisfiable',
    417 => 'Expectation Failed',
    422 => 'Unprocessable Entity',
    423 => 'Locked',
    424 => 'Failed Dependency',
    425 => 'No code',
    426 => 'Upgrade Required',
    500 => 'Internal Server Error',
    501 => 'Method Not Implemented',
    502 => 'Bad Gateway',
    503 => 'Service Temporarily Unavailable',
    504 => 'Gateway Time-out',
    505 => 'HTTP Version Not Supported',
    506 => 'Variant Also Negotiates',
    507 => 'Insufficient Storage',
    510 => 'Not Extended'
  );

  /**
  * Response status code
  * @var integer
  */
  private $_status = 200;

  /**
  * Response http headers
  * @var PapayaResponseHeaders
  */
  private $_headers = NULL;

  /**
  * Response content
  * @var PapayaResponseContent
  */
  private $_content = NULL;

  /**
  * Helper object (wraps php functions)
  * @var PapayaResponseHelper
  */
  private $_helper = NULL;

  private $_isSent = FALSE;

  /**
  * Get response helper
  *
  * @param PapayaResponseHelper $helper
  * @return PapayaResponseHelper
  */
  public function helper(PapayaResponseHelper $helper = NULL) {
    if (isset($helper)) {
      $this->_helper = $helper;
    }
    if (is_null($this->_helper)) {
      $this->_helper = new \PapayaResponseHelper();
    }
    return $this->_helper;
  }

  /**
  * Get response http headers list
  *
  * @param PapayaResponseHeaders $headers
  * @return PapayaResponseHeaders
  */
  public function headers(PapayaResponseHeaders $headers = NULL) {
    if (isset($headers)) {
      $this->_headers = $headers;
    }
    if (is_null($this->_headers)) {
      $this->_headers = new \PapayaResponseHeaders();
    }
    return $this->_headers;
  }

  /**
   * Get/Set response content object
   *
   * @param PapayaResponseContent $content
   * @return \PapayaResponseContent
   */
  public function content(PapayaResponseContent $content = NULL) {
    if (isset($content)) {
      $this->_content = $content;
    }
    if (is_null($this->_content)) {
      $this->_content = new \PapayaResponseContentString('');
    }
    return $this->_content;
  }

  /**
   * Set response status
   * @param integer $status
   * @throws UnexpectedValueException
   */
  public function setStatus($status) {
    if (isset($this->_statusCodes[$status])) {
      $this->_status = $status;
    } else {
      throw new \UnexpectedValueException('Unknown response status code: '.$status);
    }
  }

  /**
  * Get response status
  * @return integer
  */
  public function getStatus() {
    return $this->_status;
  }

  /**
   * Set Content-Type header
   * @param string$contentType
   * @param string $encoding
   * @return void
   */
  public function setContentType($contentType, $encoding = 'UTF-8') {
    $contentType .= empty($encoding) ? '' : '; charset='.$encoding;
    $this->headers()->set('Content-Type', $contentType);
  }

  /**
  * Set caching headers
  *
  * @param string $cacheMode nocache, private, public
  * @param integer $cachePeriod
  * @param integer|NULL $cacheStartTime
  * @param integer|NULL $currentTime
  */
  public function setCache(
    $cacheMode, $cachePeriod = 0, $cacheStartTime = NULL, $currentTime = NULL
  ) {
    if (in_array($cacheMode, array('private', 'public')) &&
        $cachePeriod > 0) {
      if (is_null($currentTime)) {
        $currentTime = time();
      }
      if (is_null($cacheStartTime)) {
        $cacheStartTime = $currentTime;
        $cachePeriodDelta = $cachePeriod;
      } else {
        $cachePeriodDelta = $cachePeriod - ($currentTime - $cacheStartTime);
      }
      $this->headers()->set(
        'Cache-Control',
        sprintf(
          '%s, max-age=%s, pre-check=%s, no-transform',
          $cacheMode,
          $cachePeriodDelta,
          $cachePeriodDelta
        )
      );
      $this->headers()->set('Pragma', '');
      $this->headers()->set(
        'Expires', gmdate('D, d M Y H:i:s', $cacheStartTime + $cachePeriod).' GMT'
      );
      $this->headers()->set(
        'Last-Modified', gmdate('D, d M Y H:i:s', $cacheStartTime).' GMT'
      );
    } else {
      $this->headers()->set(
        'Cache-Control',
        'no-store, no-cache, must-revalidate, post-check=0, pre-check=0, no-transform'
      );
      $this->headers()->set('Pragma', 'no-cache');
      $this->headers()->set('Expires', 'Thu, 19 Nov 1981 08:52:00 GMT');
    }
  }

  /**
   * Was the response already sent to the browser
   *
   * @return bool
   */
  public function isSent() {
    return $this->_isSent;
  }

  /**
   * Send response to browser
   * @param bool $end
   * @param bool $force force sending (ignore if it was already sent)
   */
  public function send($end = FALSE, $force = FALSE) {
    if ($force || !$this->_isSent) {
      $this->_isSent = TRUE;
      $disableXHeaders = $this->papaya()->options->get(
        'PAPAYA_DISABLE_XHEADERS', FALSE
      );
      $this->sendStatus($this->_status);
      if (($length = $this->content()->length()) >= 0) {
        $this->headers()->set('Content-Length', $length);
      }
      foreach ($this->headers() as $name => $value) {
        if (is_array($value)) {
          foreach ($value as $subValue) {
            $this->sendHeader($name.': '.$subValue, $disableXHeaders, FALSE);
          }
        } else {
          $this->sendHeader($name.': '.$value, $disableXHeaders, FALSE);
        }
      }
      $this->content()->output();
    }
    //@codeCoverageIgnoreStart
    if ($end) {
      $this->end();
    }
    //@codeCoverageIgnoreEnd
  }

  /**
   * Send HTTP status header
   * @param int|string $status
   */
  public function sendStatus($status = 0) {
    if (empty($status)) {
      $status = $this->_status;
    }
    if (!isset($this->_statusCodes[$status])) {
      $status = 200;
    }
    $isCgiApi = (in_array(strtolower(PHP_SAPI), array('cgi', 'cgi-fcgi', 'fpm-fcgi')));
    $prefix = $isCgiApi ? 'Status: ' : 'HTTP/1.1 ';
    $this->helper()->header($prefix.$status.' '.$this->_statusCodes[$status], TRUE, $status);
  }

  /**
  * Send HTTP header
  * @param string $header
  * @param boolean|NULL $disableXHeaders
  * @param boolean $force
  * @return void
  */
  public function sendHeader($header, $disableXHeaders = NULL, $force = FALSE) {
    if (is_null($disableXHeaders)) {
      $disableXHeaders = $this->papaya()->options->get(
        'PAPAYA_DISABLE_XHEADERS', FALSE
      );
    }
    if ($force || !$this->helper()->headersSent()) {
      if ($disableXHeaders &&
          substr($header, 0, 2) == 'X-') {
        return;
      }
      $header = str_replace(array("\r", "\n"), '', $header);
      $this->helper()->header($header);
    }
  }

  //@codeCoverageIgnoreStart
  /**
  * End/Exit the request
  */
  public function end() {
    exit();
  }
  //@codeCoverageIgnoreEnd
}
