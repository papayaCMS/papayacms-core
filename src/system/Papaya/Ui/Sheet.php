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
class PapayaUiSheet extends PapayaUiControl {

  private $_title = '';

  /**
   * @var PapayaUiSheetSubtitles
   */
  private $_subtitles = NULL;

  /**
   * @var PapayaXmlDocument
   */
  private $_document = NULL;

  /**
   * @var PapayaXmlElement|PapayaXmlAppendable
   */
  private $_content = NULL;

  public function __construct() {
    $this->_document = new \PapayaXmlDocument();
    $this->_content = $this->_document->appendElement('text');
  }

  public function appendTo(PapayaXmlElement $parent) {
    $sheet = $parent->appendElement('sheet');
    $title = (string)$this->_title;
    if (!(empty($title) && count($this->subtitles()) == 0)) {
      $header = $sheet->appendElement('header');
      if (!empty($title)) {
        $header->appendElement('title', array(), $title);
      }
      $header->append($this->subtitles());
    }
    if ($this->_content instanceof \PapayaXmlElement) {
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
   * @param PapayaUiSheetSubtitles $subtitles
   * @return PapayaUiSheetSubtitles
   */
  public function subtitles(PapayaUiSheetSubtitles $subtitles = NULL) {
    if (isset($subtitles)) {
      if (is_array($subtitles)) {
        $this->_subtitles = new \PapayaUiSheetSubtitles($subtitles);
      } else {
        PapayaUtilConstraints::assertInstanceOf('PapayaUiSheetSubtitles', $subtitles);
        $this->_subtitles = $subtitles;
      }
    } elseif (NULL === $this->_subtitles) {
      $this->_subtitles = new \PapayaUiSheetSubtitles();
    }
    return $this->_subtitles;
  }

  /**
   * @param PapayaXmlAppendable $content
   * @return PapayaXmlElement|PapayaXmlAppendable $content
   */
  public function content($content = NULL) {
    if (isset($content)) {
      if ($content instanceof \PapayaXmlElement) {
        $this->_document->replaceChild(
          $this->_document->importNode($content, TRUE),
          $this->_document->documentElement
        );
        $this->_content = $this->_document->documentElement;
      } else {
        PapayaUtilConstraints::assertInstanceOf('PapayaXmlAppendable', $content);
        $this->_content = $content;
      }
    }
    return $this->_content;
  }
}
