<?php

class PapayaParserTagImage extends PapayaParserTag {
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
   * Append the generated papaya:media element to a parent node
   *
   * @param PapayaXmlElement $parent
   */
  public function appendTo(PapayaXmlElement $parent) {
    $attributes = [];
    if (!empty($this->_source)) {
      $attributes['src'] = $this->_source;
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

  /**
   * Parse attributes from string
   *
   * @param string $mediaPropertyString this is the string the dialog type image(?)
   *                    contains like "32242...,max,200,300"
   * @param integer $width optional, default value 0
   * @param integer $height optional, default value 0
   * @param string $alt optional, default value ''
   * @param string $resize optional, default value NULL
   * @param string $subTitle optional, default value ''
   */
  public function parseString(
      $mediaPropertyString = '', $width = 0, $height = 0, $alt = '', $resize = NULL, $subTitle = ''
  ) {
    if (preg_match($this->_papayaTagPattern, $mediaPropertyString, $regs)) {
      $this->parseMediaTag($mediaPropertyString);
    } elseif (
        preg_match(
          '~^([^.,]+(\.\w+)?)(,(\d+)(,(\d+)(,(\w+))?)?)?$~i',
          $mediaPropertyString,
          $regs
        )
      ) {
      $this->_source = $regs[1];
      if ($width > 0) {
        $this->_width = $width;
      } elseif (isset($regs[4])) {
        $this->_width = (int)$regs[4];
      }
      if ($height > 0) {
        $this->_height = $height;
      } elseif (isset($regs[6])) {
        $this->_height = (int)$regs[6];
      }
      if (!empty($resize)) {
        $this->_resize = $resize;
      } elseif (isset($regs[8])) {
        $this->_resize = $regs[8];
      }
      if (!empty($alt)) {
        $this->_alt = $alt;
      }
      if (!empty($subTitle)) {
        $this->_subTitle = $subTitle;
      }
    }
  }

  private function parseMediaString() {
    // TO DO: parse an existing papaya:* tag
  }
}