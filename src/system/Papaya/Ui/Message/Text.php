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

namespace Papaya\Ui\Message;
/**
 * User message with an xml fragment as message content.
 *
 * The given string is append as a text node. If it contains xml the special chars will be escaped.
 *
 * @property integer $severity
 * @property string $event
 * @property boolean $occured
 * @property string $content
 *
 * @package Papaya-Library
 * @subpackage Ui
 */
class Text extends \Papaya\Ui\Message {

  private $_content = '';

  protected $_declaredProperties = array(
    'severity' => array('_severity', 'setSeverity'),
    'event' => array('_event', 'setEvent'),
    'occured' => array('_occured', 'setOccured'),
    'content' => array('getContent', 'setContent')
  );


  /**
   * Create object and store poroperties including the xml fragment string
   *
   * @param integer $severity
   * @param string $event
   * @param string $content
   * @param boolean $occured
   */
  public function __construct($severity, $event, $content, $occured = FALSE) {
    parent::__construct($severity, $event, $occured);
    $this->setContent($content);
  }

  /**
   * Use the parent method to append the element and append the text content to the new
   * message xml element node.
   *
   * @param \Papaya\Xml\Element $parent
   * @return \Papaya\Xml\Element
   */
  public function appendTo(\Papaya\Xml\Element $parent) {
    $message = parent::appendMessageElement($parent);
    if ($content = $this->getContent()) {
      $message->appendText($content);
    }
    return $message;
  }

  /**
   * Set the content string. This can be an object, if it is castable.
   *
   * @param string|\PapayaUiString $content
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
