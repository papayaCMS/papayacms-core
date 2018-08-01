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

/**
* A sheet is a larger area to display richtext, like an email message or help texts
*
* @package Papaya-Library
* @subpackage Ui
*/
class PapayaUiSheet extends \Papaya\Ui\Control {

  private $_title = '';

  /**
   * @var \PapayaUiSheetSubtitles
   */
  private $_subtitles = NULL;

  /**
   * @var \Papaya\Xml\Document
   */
  private $_document = NULL;

  /**
   * @var \Papaya\Xml\Element|\Papaya\Xml\Appendable
   */
  private $_content = NULL;

  public function __construct() {
    $this->_document = new \Papaya\Xml\Document();
    $this->_content = $this->_document->appendElement('text');
  }

  public function appendTo(\Papaya\Xml\Element $parent) {
    $sheet = $parent->appendElement('sheet');
    $title = (string)$this->_title;
    if (!(empty($title) && count($this->subtitles()) == 0)) {
      $header = $sheet->appendElement('header');
      if (!empty($title)) {
        $header->appendElement('title', array(), $title);
      }
      $header->append($this->subtitles());
    }
    if ($this->_content instanceof \Papaya\Xml\Element) {
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
   * @param \PapayaUiSheetSubtitles|array $subtitles
   * @return \PapayaUiSheetSubtitles
   */
  public function subtitles($subtitles = NULL) {
    if (isset($subtitles)) {
      if (is_array($subtitles)) {
        $this->_subtitles = new \PapayaUiSheetSubtitles($subtitles);
      } else {
        \Papaya\Utility\Constraints::assertInstanceOf(\PapayaUiSheetSubtitles::class, $subtitles);
        $this->_subtitles = $subtitles;
      }
    } elseif (NULL === $this->_subtitles) {
      $this->_subtitles = new \PapayaUiSheetSubtitles();
    }
    return $this->_subtitles;
  }

  /**
   * @param \Papaya\Xml\Appendable $content
   * @return \Papaya\Xml\Element|\Papaya\Xml\Appendable $content
   */
  public function content($content = NULL) {
    if (isset($content)) {
      if ($content instanceof \Papaya\Xml\Element) {
        $this->_document->replaceChild(
          $this->_document->importNode($content, TRUE),
          $this->_document->documentElement
        );
        $this->_content = $this->_document->documentElement;
      } else {
        \Papaya\Utility\Constraints::assertInstanceOf(\Papaya\Xml\Appendable::class, $content);
        $this->_content = $content;
      }
    }
    return $this->_content;
  }
}
