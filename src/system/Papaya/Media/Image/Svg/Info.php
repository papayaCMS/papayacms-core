<?php
class PapayaMediaImageSvgInfo extends PapayaMediaImageInfo {

  public $forceDOM = FALSE;

  const XMLNS_SVG = 'http://www.w3.org/2000/svg';

  protected function fetchProperties() {
    $properties = [
      'is_valid' => FALSE,
      'width' => 0,
      'height' => 0
    ];
    if (!$this->forceDOM && class_exists('XMLReader')) {
      $reader = new XMLReader();
      if (@$reader->open($this->getFileName())) {
        $found = @$reader->read();
        while ($found && !($reader->localName === 'svg' && $reader->namespaceURI === self::XMLNS_SVG)) {
          $found = $reader->next('svg');
        }
        if ($found) {
          $properties['is_valid'] = TRUE;
          $properties['width'] = (int)$reader->getAttribute('width');
          $properties['height'] = (int)$reader->getAttribute('height');
        }
      }
    } else {
      $document = new PapayaXmlDocument();
      if (@$document->load($this->getFileName())) {
        $node = $document->documentElement;
        if ($node && $node->localName === 'svg' && $node->namespaceURI === self::XMLNS_SVG) {
          $properties['is_valid'] = TRUE;
          $properties['width'] = (int)$node->getAttribute('width');
          $properties['height'] = (int)$node->getAttribute('height');
        }
      }
    }
    return $properties;
  }
}