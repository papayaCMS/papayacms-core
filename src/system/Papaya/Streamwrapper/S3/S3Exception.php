<?php
/*
 * papaya CMS
 *
 * @copyright 2000-2021 by papayaCMS project - All rights reserved.
 * @link http://www.papaya-cms.com/
 * @license http://www.gnu.org/licenses/old-licenses/gpl-2.0.html GNU General Public License, version 2
 *
 *  You can redistribute and/or modify this script under the terms of the GNU General Public
 *  License (GPL) version 2, provided that the copyright and license notes, including these
 *  lines, remain unmodified. papaya is distributed in the hope that it will be useful, but
 *  WITHOUT ANY WARRANTY; without even the implied warranty of MERCHANTABILITY or FITNESS
 *  FOR A PARTICULAR PURPOSE.
 */
namespace Papaya\Streamwrapper\S3 {

  class S3Exception extends \Papaya\Exception {

    const SEVERITY_INFO = \Papaya\Message::SEVERITY_INFO;
    const SEVERITY_WARNING = \Papaya\Message::SEVERITY_WARNING;
    const SEVERITY_ERROR = \Papaya\Message::SEVERITY_ERROR;

    private static $_LABELS = [
      self::SEVERITY_INFO => 'INFO',
      self::SEVERITY_WARNING => 'WARNING',
      self::SEVERITY_ERROR => 'ERROR'
    ];

    public function __construct($message, $severity = self::SEVERITY_WARNING) {
      $severityLabel = self::$_LABELS[$severity] ?? self::$_LABELS[self::SEVERITY_ERROR];
      parent::__construct(
        sprintf('S3 %s: %s', $severityLabel, $message)
      );
    }
  }
}
