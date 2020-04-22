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
namespace Papaya\UI {

  use Papaya\BaseObject\Interfaces\StringCastable;
  use Papaya\Utility;
  use Papaya\Utility\Arrays as ArrayUtilities;
  use Papaya\XML;
  use Papaya\XML\Appendable as XMLAppendable;
  use Papaya\XML\Element as XMLElement;

  /**
   * A sheet is a larger area to display richtext, like an email message or help texts
   *
   * @package Papaya-Library
   * @subpackage UI
   * @property int $padding
   */
  class Sheet extends Control {

    const PADDING_NONE = 0;
    const PADDING_SMALL = 10;
    const PADDING_MEDIUM = 20;
    const PADDING_LARGE = 50;

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
     * @var XMLElement|XMLAppendable
     */
    private $_content;
    /**
     * @var bool
     */
    protected $_padding = self::PADDING_NONE;

    public function __construct() {
      $this->_document = new XML\Document();
      $this->_content = $this->_document->appendElement('text');
    }

    /**
     * @param XMLElement $parent
     * @return XMLElement
     */
    public function appendTo(XMLElement $parent) {
      $sheet = $parent->appendElement(
        'sheet',
        ['padding' => $this->_padding > 0 ?  ((int)$this->_padding).'px' : NULL]
      );
      $title = \trim($this->_title);
      if (!('' === $title && 0 === \count($this->subtitles()))) {
        $header = $sheet->appendElement('header');
        if ('' !== $title) {
          $header->appendElement('title', [], $title);
        }
        $header->append($this->subtitles());
      }
      if ($this->_content instanceof XMLElement) {
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
     * @param XMLAppendable|XMLElement $content
     *
     * @return XMLElement|XMLAppendable $content
     */
    public function content($content = NULL) {
      if (NULL !== $content) {
        if ($content instanceof XMLElement) {
          $this->_document->replaceChild(
            $this->_document->importNode($content, TRUE),
            $this->_document->documentElement
          );
          $this->_content = $this->_document->documentElement;
        } else {
          Utility\Constraints::assertInstanceOf(XMLAppendable::class, $content);
          $this->_content = $content;
        }
      }
      return $this->_content;
    }

    public function getPropertyDeclaration() {
      return ArrayUtilities::merge(
        parent::getPropertyDeclaration(),
        [
          'padding' => ['_padding', '_padding']
        ]
      );
    }
  }
}
