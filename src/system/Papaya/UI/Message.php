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
namespace Papaya\UI;

/**
 * Abstract/Basic superclass for the user messages.
 *
 * This are messages diplayed to the user on a page. They can have differnt kind of severities
 * and always have a event. This is a string identifier for the event the message is for.
 *
 * Occured is an boolean attribute, that is true if the message actually occured in the current
 * request. It is sometimes needed to output a message to the xml even if the event did not happen.
 * A typical case for this is a javascript action/event.
 *
 * @package Papaya-Library
 * @subpackage UI
 *
 * @property int $severity
 * @property bool $occured
 * @property string|\Papaya\UI\Text $event
 */
abstract class Message
  extends Control {
  const SEVERITY_INFORMATION = 0;

  const SEVERITY_WARNING = 1;

  const SEVERITY_ERROR = 2;

  const SEVERITY_CONFIRMATION = 3;

  private $_tagNames = [
    self::SEVERITY_INFORMATION => 'information',
    self::SEVERITY_WARNING => 'warning',
    self::SEVERITY_ERROR => 'error',
    self::SEVERITY_CONFIRMATION => 'confirmation'
  ];

  protected $_severity = self::SEVERITY_INFORMATION;

  protected $_event = '';

  protected $_occured = FALSE;

  protected $_declaredProperties = [
    'severity' => ['_severity', 'setSeverity'],
    'event' => ['_event', 'setEvent'],
    'occured' => ['_occured', 'setOccured']
  ];

  /**
   * Create object and store basic properties
   *
   * @param int $severity
   * @param string $event
   * @param bool $occured
   */
  public function __construct($severity, $event, $occured = FALSE) {
    $this->setSeverity($severity);
    $this->setEvent($event);
    $this->setOccured($occured);
  }

  /**
   * Append message to parent xml element and return it.
   *
   * @param \Papaya\XML\Element $parent
   *
   * @return \Papaya\XML\Element the appended message xml element
   */
  protected function appendMessageElement(\Papaya\XML\Element $parent) {
    return $parent->appendElement(
      $this->getTagName($this->_severity),
      [
        'event' => $this->event,
        'occured' => $this->occured ? 'yes' : 'no'
      ]
    );
  }

  /**
   * Validate and set the message severity.
   *
   * @throws \InvalidArgumentException
   *
   * @param int $severity
   */
  public function setSeverity($severity) {
    \Papaya\Utility\Constraints::assertInteger($severity);
    if (!\array_key_exists($severity, $this->_tagNames)) {
      throw new \InvalidArgumentException('Invalid severity for message.');
    }
    $this->_severity = $severity;
  }

  /**
   * Validate and set the message event identifier string.
   *
   * @param string $event
   */
  public function setEvent($event) {
    $event = (string)$event;
    \Papaya\Utility\Constraints::assertNotEmpty($event);
    $this->_event = $event;
  }

  /**
   * Validate and set the message occured status.
   *
   * @param bool $occured
   */
  public function setOccured($occured) {
    $this->_occured = (bool)$occured;
  }

  /**
   * Get the tag name. The xml element name depends on the severity.
   *
   * @param int $severity
   *
   * @return string
   */
  protected function getTagName($severity) {
    return $this->_tagNames[$severity];
  }
}
