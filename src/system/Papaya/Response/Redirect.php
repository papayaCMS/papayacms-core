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

namespace Papaya\Response;

/**
 * @package Papaya-Library
 * @subpackage Response
 */
class Redirect extends \Papaya\Response {
  private $_location = '';

  private $_reason = '';

  /**
   * @param string $location location url
   * @param int $status redirect status code (default 302)
   * @param string $reason A reason send as an X-Header
   */
  public function __construct($location, $status = 302, $reason = '') {
    $this->_location = $location;
    $this->_reason = $reason;
    $this->setStatus($status);
  }

  public function send($end = TRUE, $force = TRUE) {
    $headers = $this->headers();
    if (!isset($headers['Location'])) {
      if (!empty($this->_reason)) {
        $headers['X-Papaya-Status'] = $this->_reason;
      }
      $headers['Expires'] = \gmdate('D, d M Y H:i:s', (\time() - 31536000)).' GMT';
      $headers['Cache-Control'] = 'no-store, no-cache, must-revalidate, post-check=0, pre-check=0';
      $headers['Pragma'] = 'no-cache';
      $headers['Location'] = $this->_location;
    }
    parent::send($end, $force);
  }
}
