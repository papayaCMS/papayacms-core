<?php
class PapayaMediaImageSvgInfo {

  public $forceDOM = FALSE;

  const XMLNS_SVG = 'http://www.w3.org/2000/svg';

  private $_fileName;
  private $_properties;

  public function __construct($svgFileName) {
    $this->_fileName = $svgFileName;
  }

  private function getSvgProperties() {
    if (NULL === $this->_properties) {
      $this->_properties = [
        'is_svg' => FALSE,
        'width' => 0,
        'height' => 0
      ];
      if (!$this->forceDOM && class_exists('XMLReader')) {
        $reader = new XMLReader();
        if (@$reader->open($this->_fileName)) {
          $found = @$reader->read();
          while ($found && !($reader->localName === 'svg' && $reader->namespaceURI === self::XMLNS_SVG)) {
            $found = $reader->next('svg');
          }
          if ($found) {
            $this->_properties['is_svg'] = TRUE;
            $this->_properties['width'] = (int)$reader->getAttribute('width');
            $this->_properties['height'] = (int)$reader->getAttribute('height');
          }
        }
      } else {
        $document = new PapayaXmlDocument();
        if (@$document->load($this->_fileName)) {
          $node = $document->documentElement;
          if ($node && $node->localName === 'svg' && $node->namespaceURI === self::XMLNS_SVG) {
            $this->_properties['is_svg'] = TRUE;
            $this->_properties['width'] = (int)$node->getAttribute('width');
            $this->_properties['height'] = (int)$node->getAttribute('height');
          }
        }
      }
    }
    return $this->_properties;
  }

  public function isSvg() {
    return $this->getSvgProperties()['is_svg'];
  }

  public function getWidth() {
    return $this->getSvgProperties()['width'];

  }

  public function getHeight() {
    return $this->getSvgProperties()['height'];
  }

}