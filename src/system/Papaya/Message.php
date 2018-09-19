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
namespace Papaya;

/**
 * Papaya Message, abstract superclass for all messages
 *
 * @package Papaya-Library
 * @subpackage Messages
 */
interface Message {
  /**
   * @var int
   */
  const SEVERITY_INFO = 0;

  /**
   * @var int
   */
  const SEVERITY_WARNING = 1;

  /**
   * @var int
   */
  const SEVERITY_ERROR = 2;

  /**
   * @var int
   */
  const SEVERITY_DEBUG = 3;

  /**
   * @var int
   */
  const SEVERITY_EMERGENCY = 4;

  /**
   * @var int
   */
  const SEVERITY_ALERT = 5;

  /**
   * @var int
   */
  const SEVERITY_CRITICAL = 6;

  /**
   * @var int
   */
  const SEVERITY_NOTICE = 7;

  /**
   * Information message type
   *
   * @deprecated use SEVERITY_INFO
   *
   * @var int
   */
  const TYPE_INFO = 0;

  /**
   * Warning message type
   *
   * @deprecated use SEVERITY_WARNING
   *
   * @var int
   */
  const TYPE_WARNING = 1;

  /**
   * Error message type
   *
   * @deprecated use SEVERITY_ERROR
   *
   * @var int
   */
  const TYPE_ERROR = 2;

  /**
   * Error message type
   *
   * @deprecated use SEVERITY_DEBUG
   *
   * @var int
   */
  const TYPE_DEBUG = 3;

  /**
   * Get type of message (info, warning, error)
   *
   * @deprecated
   *
   * @return int
   */
  public function getType();

  /**
   * Get type of message (info, warning, error)
   *
   * @return int
   */
  public function getSeverity();

  /**
   * Get message string
   *
   * @return string
   */
  public function getMessage();
}
