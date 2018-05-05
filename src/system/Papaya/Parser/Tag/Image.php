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

class PapayaParserTagImage extends \PapayaParserTag {
  /**
   * @var string
   */
  private $_mediaPropertyString = '';

  /**
   * @var string
   */
  private $_source = '';

  /**
   * @var int
   */
  private $_width = 0;

  /**
   * @var int
   */
  private $_height = 0;

  /**
   * @var string
   */
  private $_alt = '';

  /**
   * @var string
   */
  private $_resize = '';

  /**
   * @var string
   */
  private $_subTitle = '';

  /**
   * Papaya tag pattern
   * @var string
   */
  private $_papayaTagPattern = '/<(papaya|ndim):([a-z]\w+)\s?([^>]*)\/?>(<\/(\1):(\2)>)?/ims';

  /**
   * Constructor
   *
   * @param string $mediaPropertyString this is the string the dialog type image(?)
   *                    contains like "32242...,max,200,300"
   * @param integer $width optional, default value 0
   * @param integer $height optional, default value 0
   * @param string $alt optional, default value ''
   * @param string $resize optional, default value NULL
   * @param string $subTitle optional, default value ''
   */
  public function __construct(
    $mediaPropertyString, $width = 0, $height = 0, $alt = '', $resize = NULL, $subTitle = ''
  ) {
    $this->_mediaPropertyString = $mediaPropertyString;
    $this->_width = $width;
    $this->_height = $height;
    $this->_alt = $alt;
    $this->_resize = $resize;
    $this->_subTitle = $subTitle;
  }

  /**
   * Append the generated papaya:media element to a parent node
   *
   * @param \PapayaXmlElement $parent
   */
  public function appendTo(\PapayaXmlElement $parent) {
    $this->parseImageData();
    $attributes = [];
    if (!empty($this->_source)) {
      $attributes['src'] = $this->_source;
    } else {
      return;
    }
    if ($this->_width > 0) {
      $attributes['width'] = $this->_width;
    }
    if ($this->_height > 0) {
      $attributes['height'] = $this->_height;
    }
    if (!empty($this->_alt)) {
      $attributes['alt'] = $this->_alt;
    }
    if (!empty($this->_resize)) {
      $attributes['resize'] = $this->_resize;
    }
    if (!empty($this->_subTitle)) {
      $attributes['subtitle'] = $this->_subTitle;
    }
    $document = $parent->ownerDocument;
    $imageTag = $document->createElementNS('http://www.papaya-cms.com/namespace/papaya', 'papaya:media');
    foreach ($attributes as $name => $value) {
      $imageTag->setAttribute($name, $value);
    }
    $parent->appendChild($imageTag);
  }

  private function parseImageData() {
    if (preg_match($this->_papayaTagPattern, $this->_mediaPropertyString, $regs)) {
      $this->parseMediaTag($this->_mediaPropertyString);
    } elseif (
        preg_match(
          '~^([^.,]+(\.\w+)?)(,(\d+)(,(\d+)(,(\w+))?)?)?$~i',
          $this->_mediaPropertyString,
          $regs
        )
      ) {
      $this->_source = \papaya_strings::escapeHTMLChars($regs[1]);
      if ($this->_width == 0 && isset($regs[4])) {
        $this->_width = (int)$regs[4];
      }
      if ($this->_height == 0 && isset($regs[6])) {
        $this->_height = (int)$regs[6];
      }
      if (empty($this->_resize) && isset($regs[8])) {
        $this->_resize = \papaya_strings::escapeHTMLChars($regs[8]);
      }
    }
  }

  private function parseMediaString() {
    // TO DO: parse an existing papaya:* tag
  }
}
