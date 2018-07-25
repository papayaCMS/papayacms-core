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
* Extract teaser image information from the given subtopic elements and creates a list
* of scaled teaser image tags.
*
* @package Papaya-Library
* @subpackage Ui-Content
*/
class PapayaUiContentTeaserImages extends \PapayaUiControl {

  /**
  * thumbnail width
  *
  * @var integer
  */
  private $_width = 0;

  /**
  * thumbnail height
  *
  * @var integer
  */
  private $_height = 0;

  /**
  * thumbnail resize mode (abs, max, min, mincrop)
  *
  * @var integer
  */
  private $_resizeMode = 'max';

  /**
  * teasers parent element node
  *
  * @var \PapayaXmlElement
  */
  private $_teasers = NULL;

  /**
  * Xpath expressions used to find and iterate the teaser images
  *
  * @var array
  */
  private $_pattern = array(
    'teaser_images' => 'teaser/image//*[name() = "img" or local-name() = "media"]',
    'subtopic_images' => 'subtopic/image//*[name() = "img" or local-name() = "media"]',
    'page_id' => 'string(ancestor::subtopic/@no|ancestor::teaser/@page-id)'
  );

  /**
  * Create object and store given parameters
  *
  * @param \PapayaXmlElement $teasers
  * @param integer $width
  * @param integer $height
  * @param string $resizeMode
  */
  public function __construct(\PapayaXmlElement $teasers, $width, $height, $resizeMode = 'max') {
    $this->_teasers = $teasers;
    $this->_width = $width;
    $this->_height = $height;
    $this->_resizeMode = $resizeMode;
  }

  /**
  * Append teaser thumbnail tags to given parent element.
  *
  * @param \PapayaXmlElement $parent
  * @return \PapayaXmlElement|NULL
  */
  public function appendTo(\PapayaXmlElement $parent) {
    /** @var \PapayaXmlDocument $targetDocument */
    $targetDocument = $parent->ownerDocument;
    $targetDocument->registerNamespaces(
      array(
        'papaya' => 'http://www.papaya-cms.com/ns/papayacms'
      )
    );
    /** @var \PapayaXmlDocument $dom */
    $dom = $this->_teasers->ownerDocument;
    $images = $dom->xpath()->evaluate($this->_pattern['teaser_images'], $this->_teasers);
    $names = array(
      'list' => 'teaser-thumbnails',
      'item' => 'thumbnail',
      'attribute' => 'page-id'
    );
    if ($images->length < 1) {
      $images = $dom->xpath()->evaluate($this->_pattern['subtopic_images'], $this->_teasers);
      $names = array(
        'list' => 'subtopicthumbs',
        'item' => 'thumb',
        'attribute' => 'topic'
      );
    }
    if ($images->length > 0) {
      $thumbs = $parent->appendElement($names['list']);
      /** @var \PapayaXmlElement $imageNode */
      foreach ($images as $imageNode) {
        $thumbNode = $thumbs
          ->appendElement(
            $names['item'],
            array(
              $names['attribute'] => $dom->xpath()->evaluate(
                $this->_pattern['page_id'], $imageNode
              )
            )
          )
          ->appendElement(
            'papaya:media',
            array(
              'src' => $imageNode->getAttribute('src'),
              'resize' => $this->_resizeMode
            )
          );
        if ($this->_width > 0) {
          $thumbNode->setAttribute('width', (int)$this->_width);
        }
        if ($this->_height > 0) {
          $thumbNode->setAttribute('height', (int)$this->_height);
        }
      }
      return $thumbs;
    }
    return NULL;
  }

}
