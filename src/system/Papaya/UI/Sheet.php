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
 * A sheet is a larger area to display richtext, like an email message or help texts
 *
 * @package Papaya-Library
 * @subpackage UI
 */
class Sheet extends Control {
  private $_title = '';

  /**
   * @var \Papaya\UI\Sheet\Subtitles
   */
  private $_subtitles;

  /**
   * @var \Papaya\XML\Document
   */
  private $_document;

  /**
   * @var \Papaya\XML\Element|\Papaya\XML\Appendable
   */
  private $_content;

  public function __construct() {
    $this->_document = new \Papaya\XML\Document();
    $this->_content = $this->_document->appendElement('text');
  }

  public function appendTo(\Papaya\XML\Element $parent) {
    $sheet = $parent->appendElement('sheet');
    $title = (string)$this->_title;
    if (!(empty($title) && 0 == \count($this->subtitles()))) {
      $header = $sheet->appendElement('header');
      if (!empty($title)) {
        $header->appendElement('title', [], $title);
      }
      $header->append($this->subtitles());
    }
    if ($this->_content instanceof \Papaya\XML\Element) {
      $sheet->appendChild(
        $parent->ownerDocument->importNode($this->_content, TRUE)
      );
    } else {
      $sheet->appendElement('text')->append($this->_content);
    }
    return $sheet;
  }

  public function title($title = NULL) {
    if (isset($title)) {
      $this->_title = $title;
    }
    return $this->_title;
  }

  /**
   * @param \Papaya\UI\Sheet\Subtitles|array $subtitles
   *
   * @return \Papaya\UI\Sheet\Subtitles
   */
  public function subtitles($subtitles = NULL) {
    if (isset($subtitles)) {
      if (\is_array($subtitles)) {
        $this->_subtitles = new \Papaya\UI\Sheet\Subtitles($subtitles);
      } else {
        \Papaya\Utility\Constraints::assertInstanceOf(\Papaya\UI\Sheet\Subtitles::class, $subtitles);
        $this->_subtitles = $subtitles;
      }
    } elseif (NULL === $this->_subtitles) {
      $this->_subtitles = new \Papaya\UI\Sheet\Subtitles();
    }
    return $this->_subtitles;
  }

  /**
   * @param \Papaya\XML\Appendable $content
   *
   * @return \Papaya\XML\Element|\Papaya\XML\Appendable $content
   */
  public function content($content = NULL) {
    if (isset($content)) {
      if ($content instanceof \Papaya\XML\Element) {
        $this->_document->replaceChild(
          $this->_document->importNode($content, TRUE),
          $this->_document->documentElement
        );
        $this->_content = $this->_document->documentElement;
      } else {
        \Papaya\Utility\Constraints::assertInstanceOf(\Papaya\XML\Appendable::class, $content);
        $this->_content = $content;
      }
    }
    return $this->_content;
  }
}
