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

  use Papaya\Response\Content as ResponseContent;
  use Papaya\Response\Headers as ResponseHeaders;
  use Papaya\Response\Helper as ResponseHelper;
  use Papaya\Response\Status as ResponseStatus;

  /**
   * @package Papaya-Library
   * @subpackage Response
   */
  class Response implements Application\Access {
    use Application\Access\Aggregation;


    const CACHE_NONE = 'no-cache';
    const CACHE_PRIVATE = 'private';
    const CACHE_PUBLIC = 'public';
    /**
     * Status codes
     *
     * @var array
     */
    private static $_statusCodes = ResponseStatus::LABELS;

    /**
     * Response status code
     *
     * @var int
     */
    private $_status = ResponseStatus::OK_200;

    /**
     * Response http headers
     *
     * @var ResponseHeaders
     */
    private $_headers;

    /**
     * Response content
     *
     * @var ResponseContent
     */
    private $_content;

    /**
     * @var string
     */
    private $_contentType;

    /**
     * @var string
     */
    private $_contentEncoding;

    /**
     * Helper object (wraps php functions)
     *
     * @var ResponseHelper
     */
    private $_helper;

    private $_isSent = FALSE;

    /**
     * Get response helper
     *
     * @param ResponseHelper $helper
     *
     * @return ResponseHelper
     */
    public function helper(ResponseHelper $helper = NULL) {
      if (NULL !== $helper) {
        $this->_helper = $helper;
      } elseif (NULL === $this->_helper) {
        $this->_helper = new ResponseHelper();
      }
      return $this->_helper;
    }

    /**
     * Get response http headers list
     *
     * @param ResponseHeaders $headers
     *
     * @return ResponseHeaders
     */
    public function headers(ResponseHeaders $headers = NULL) {
      if (NULL !== $headers) {
        $this->_headers = $headers;
      } elseif (NULL === $this->_headers) {
        $this->_headers = new ResponseHeaders();
      }
      return $this->_headers;
    }

    /**
     * Get/Set response content object
     *
     * @param ResponseContent $content
     *
     * @return ResponseContent
     */
    public function content(ResponseContent $content = NULL) {
      if (NULL !== $content) {
        $this->_content = $content;
      } elseif (NULL === $this->_content) {
        $this->_content = $this->createContent();
      }
      return $this->_content;
    }

    protected function createContent() {
      return new Response\Content\Text('');
    }

    /**
     * Set response status
     *
     * @param int $status
     *
     * @throws \UnexpectedValueException
     */
    public function setStatus($status) {
      if (isset(self::$_statusCodes[$status])) {
        $this->_status = $status;
      } else {
        throw new \UnexpectedValueException('Unknown response status code: '.$status);
      }
    }

    /**
     * Get response status
     *
     * @return int
     */
    public function getStatus() {
      return $this->_status;
    }

    /**
     * Set Content-Type header
     *
     * @param string $contentType
     * @param string $encoding
     */
    public function setContentType($contentType, $encoding = 'UTF-8') {
      $this->_contentType = $contentType;
      $this->_contentEncoding = $encoding;
      $contentType .= empty($encoding) ? '' : '; charset='.$encoding;
      $this->headers()->set('Content-Type', $contentType);
    }

    /**
     * Set Content-Type header
     *
     * @return string
     */
    public function getContentType() {
      return $this->_contentType;
    }

    /**
     * Set Content-Type header
     *
     * @return string
     */
    public function getContentEncoding() {
      return $this->_contentEncoding;
    }

    /**
     * Set caching headers
     *
     * @param string $cacheMode nocache, private, public
     * @param int $cachePeriod
     * @param int|null $cacheStartTime
     * @param int|null $currentTime
     */
    public function setCache(
      $cacheMode, $cachePeriod = 0, $cacheStartTime = NULL, $currentTime = NULL
    ) {
      if ($cachePeriod > 0 && \in_array($cacheMode, [self::CACHE_PRIVATE, self::CACHE_PUBLIC], TRUE)) {
        if (NULL === $currentTime) {
          $currentTime = \time();
        }
        if (NULL === $cacheStartTime) {
          $cacheStartTime = $currentTime;
          $cachePeriodDelta = $cachePeriod;
        } else {
          $cachePeriodDelta = $cachePeriod - ($currentTime - $cacheStartTime);
        }
        $this->headers()->set(
          'Cache-Control',
          \sprintf(
            '%s, max-age=%s, pre-check=%s, no-transform',
            $cacheMode,
            $cachePeriodDelta,
            $cachePeriodDelta
          )
        );
        $this->headers()->set('Pragma', '');
        $this->headers()->set(
          'Expires', \gmdate('D, d M Y H:i:s', $cacheStartTime + $cachePeriod).' GMT'
        );
        $this->headers()->set(
          'Last-Modified', \gmdate('D, d M Y H:i:s', $cacheStartTime).' GMT'
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
     *
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
          if (\is_array($value)) {
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
     *
     * @param int|string $status
     */
    public function sendStatus($status = 0) {
      if (empty($status)) {
        $status = $this->_status;
      }
      if (!isset(self::$_statusCodes[$status])) {
        $status = 200;
      }
      $isCgiApi = \in_array(\strtolower(PHP_SAPI), ['cgi', 'cgi-fcgi', 'fpm-fcgi'], TRUE);
      $prefix = $isCgiApi ? 'Status: ' : 'HTTP/1.1 ';
      $this->helper()->header($prefix.$status.' '.self::$_statusCodes[$status], TRUE, $status);
    }

    /**
     * Send HTTP header
     *
     * @param string $header
     * @param bool|null $disableXHeaders
     * @param bool $force
     */
    public function sendHeader($header, $disableXHeaders = NULL, $force = FALSE) {
      if (NULL === $disableXHeaders) {
        $disableXHeaders = (bool)$this->papaya()->options->get(
          'PAPAYA_DISABLE_XHEADERS', FALSE
        );
      }
      if ($force || !$this->helper()->headersSent()) {
        if (
          $disableXHeaders &&
          0 === \strpos($header, 'X-')
        ) {
          return;
        }
        $header = \str_replace(["\r", "\n"], '', $header);
        $this->helper()->header($header);
      }
    }

    public function duplicate() {
      return clone $this;
    }

    public function __clone() {
      if (NULL !== $this->_headers) {
        $this->_headers = clone $this->_headers;
      }
      if (NULL !== $this->_content) {
        $this->_content = clone $this->_content;
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
}
