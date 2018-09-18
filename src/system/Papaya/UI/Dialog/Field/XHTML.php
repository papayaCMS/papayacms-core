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

namespace Papaya\UI\Dialog\Field;

/**
 * A field that outputs xhtml inside the dialog.
 *
 * @package Papaya-Library
 * @subpackage UI
 */
class XHTML extends \Papaya\UI\Dialog\Field {
  /**
   * XHTML content
   *
   * @var string
   */
  private $_content;

  private $_dom;

  /**
   * Create object and assign needed values.
   *
   * @param string|\Papaya\UI\Text|\Papaya\XML\Element $content
   */
  public function __construct($content = NULL) {
    if (isset($content)) {
      $this->content($content);
    }
  }

  /**
   * Getter/Setter for xhtml content.
   *
   * @param string|\Papaya\UI\Text|\Papaya\XML\Element $content
   * @throws \InvalidArgumentException
   * @return \Papaya\XML\Element
   */
  public function content($content = NULL) {
    if (isset($content)) {
      if ($content instanceof \Papaya\XML\Element) {
        $this->_content = $content;
      } elseif (\is_string($content) || $content instanceof \Papaya\UI\Text) {
        $this->content()->appendXML((string)$content);
      } else {
        throw new \InvalidArgumentException('Content must be string or valid xml element object');
      }
    } elseif (\is_null($this->_content)) {
      $this->_dom = new \Papaya\XML\Document();
      $this->_content = $this->_dom->appendElement('xhtml');
    }
    return $this->_content;
  }

  /**
   * Append xhtml field to dialog xml dom.
   *
   * @param \Papaya\XML\Element $parent
   */
  public function appendTo(\Papaya\XML\Element $parent) {
    $field = $this->_appendFieldTo($parent);
    if ($this->content()->hasChildNodes()) {
      $field->appendChild(
        $field->ownerDocument->importNode($this->content(), TRUE)
      );
    }
  }
}
