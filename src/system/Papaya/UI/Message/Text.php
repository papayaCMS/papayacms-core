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
namespace Papaya\UI\Message;

use Papaya\BaseObject;
use Papaya\UI;
use Papaya\XML;

/**
 * User message with an xml fragment as message content.
 *
 * The given string is append as a text node. If it contains xml the special chars will be escaped.
 *
 * @property int $severity
 * @property string $event
 * @property bool $occurred
 * @property string $content
 *
 * @package Papaya-Library
 * @subpackage UI
 */
class Text extends UI\Message {
  /**
   * @var string|BaseObject\Interfaces\StringCastable
   */
  private $_content = '';

  protected $_declaredProperties = [
    'severity' => ['_severity', 'setSeverity'],
    'event' => ['_event', 'setEvent'],
    'occured' => ['_occurred', 'setOccurred'],
    'occurred' => ['_occurred', 'setOccurred'],
    'content' => ['getContent', 'setContent']
  ];

  /**
   * Create object and store properties including the xml fragment string
   *
   * @param int $severity
   * @param string $event
   * @param string $content
   * @param bool $occurred
   */
  public function __construct($severity, $event, $content, $occurred = FALSE) {
    parent::__construct($severity, $event, $occurred);
    $this->setContent($content);
  }

  /**
   * Use the parent method to append the element and append the text content to the new
   * message xml element node.
   *
   * @param XML\Element $parent
   *
   * @return XML\Element
   */
  public function appendTo(XML\Element $parent) {
    $message = parent::appendMessageElement($parent);
    if ($content = $this->getContent()) {
      $message->appendText($content);
    }
    return $message;
  }

  /**
   * Set the content string. This can be an object, if it is castable.
   *
   * @param string|BaseObject\Interfaces\StringCastable $content
   */
  public function setContent($content) {
    $this->_content = $content;
  }

  /**
   * Get the content string. It it was an object, it will be casted to string.
   *
   * @return string
   */
  public function getContent() {
    return (string)$this->_content;
  }
}
