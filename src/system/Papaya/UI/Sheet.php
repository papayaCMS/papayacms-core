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

use Papaya\BaseObject\Interfaces\StringCastable;
use Papaya\Utility;
use Papaya\XML;

/**
 * A sheet is a larger area to display richtext, like an email message or help texts
 *
 * @package Papaya-Library
 * @subpackage UI
 */
class Sheet extends Control {
  /**
   * @var string|StringCastable
   */
  private $_title = '';

  /**
   * @var Sheet\Subtitles
   */
  private $_subtitles;

  /**
   * @var XML\Document
   */
  private $_document;

  /**
   * @var XML\Element|XML\Appendable
   */
  private $_content;

  public function __construct() {
    $this->_document = new XML\Document();
    $this->_content = $this->_document->appendElement('text');
  }

  /**
   * @param \Papaya\XML\Element $parent
   * @return \Papaya\XML\Element
   */
  public function appendTo(XML\Element $parent) {
    $sheet = $parent->appendElement('sheet');
    $title = \trim($this->_title);
    if (!('' === $title && 0 === \count($this->subtitles()))) {
      $header = $sheet->appendElement('header');
      if ('' !== $title) {
        $header->appendElement('title', [], $title);
      }
      $header->append($this->subtitles());
    }
    if ($this->_content instanceof XML\Element) {
      $sheet->appendChild(
        $parent->ownerDocument->importNode($this->_content, TRUE)
      );
    } else {
      $sheet->appendElement('text')->append($this->_content);
    }
    return $sheet;
  }

  /**
   * @param null|string|StringCastable $title
   * @return null|StringCastable|string
   */
  public function title($title = NULL) {
    if (NULL !== $title) {
      $this->_title = $title;
    }
    return $this->_title;
  }

  /**
   * @param Sheet\Subtitles|array $subtitles
   *
   * @return Sheet\Subtitles
   */
  public function subtitles($subtitles = NULL) {
    if (NULL !== $subtitles) {
      if (\is_array($subtitles)) {
        $this->_subtitles = new Sheet\Subtitles($subtitles);
      } else {
        Utility\Constraints::assertInstanceOf(Sheet\Subtitles::class, $subtitles);
        $this->_subtitles = $subtitles;
      }
    } elseif (NULL === $this->_subtitles) {
      $this->_subtitles = new Sheet\Subtitles();
    }
    return $this->_subtitles;
  }

  /**
   * @param XML\Appendable|XML\Element $content
   *
   * @return XML\Element|XML\Appendable $content
   */
  public function content($content = NULL) {
    if (NULL !== $content) {
      if ($content instanceof XML\Element) {
        $this->_document->replaceChild(
          $this->_document->importNode($content, TRUE),
          $this->_document->documentElement
        );
        $this->_content = $this->_document->documentElement;
      } else {
        Utility\Constraints::assertInstanceOf(XML\Appendable::class, $content);
        $this->_content = $content;
      }
    }
    return $this->_content;
  }
}
