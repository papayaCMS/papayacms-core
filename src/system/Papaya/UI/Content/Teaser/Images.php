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
namespace Papaya\UI\Content\Teaser;

/**
 * Extract teaser image information from the given subtopic elements and creates a list
 * of scaled teaser image tags.
 *
 * @package Papaya-Library
 * @subpackage UI-Content
 */
class Images extends \Papaya\UI\Control {
  /**
   * thumbnail width
   *
   * @var int
   */
  private $_width = 0;

  /**
   * thumbnail height
   *
   * @var int
   */
  private $_height = 0;

  /**
   * thumbnail resize mode (abs, max, min, mincrop)
   *
   * @var int
   */
  private $_resizeMode = 'max';

  /**
   * teasers parent element node
   *
   * @var \Papaya\XML\Element
   */
  private $_teasers;

  /**
   * Xpath expressions used to find and iterate the teaser images
   *
   * @var array
   */
  private $_pattern = [
    'teaser_images' => 'teaser/image//*[name() = "img" or local-name() = "media"]',
    'subtopic_images' => 'subtopic/image//*[name() = "img" or local-name() = "media"]',
    'page_id' => 'string(ancestor::subtopic/@no|ancestor::teaser/@page-id)'
  ];

  /**
   * Create object and store given parameters
   *
   * @param \Papaya\XML\Element $teasers
   * @param int $width
   * @param int $height
   * @param string $resizeMode
   */
  public function __construct(\Papaya\XML\Element $teasers, $width, $height, $resizeMode = 'max') {
    $this->_teasers = $teasers;
    $this->_width = $width;
    $this->_height = $height;
    $this->_resizeMode = $resizeMode;
  }

  /**
   * Append teaser thumbnail tags to given parent element.
   *
   * @param \Papaya\XML\Element $parent
   *
   * @return \Papaya\XML\Element|null
   */
  public function appendTo(\Papaya\XML\Element $parent) {
    /** @var \Papaya\XML\Document $targetDocument */
    $targetDocument = $parent->ownerDocument;
    $targetDocument->registerNamespaces(
      [
        'papaya' => 'http://www.papaya-cms.com/ns/papayacms'
      ]
    );
    /** @var \Papaya\XML\Document $dom */
    $dom = $this->_teasers->ownerDocument;
    $images = $dom->xpath()->evaluate($this->_pattern['teaser_images'], $this->_teasers);
    $names = [
      'list' => 'teaser-thumbnails',
      'item' => 'thumbnail',
      'attribute' => 'page-id'
    ];
    if ($images->length < 1) {
      $images = $dom->xpath()->evaluate($this->_pattern['subtopic_images'], $this->_teasers);
      $names = [
        'list' => 'subtopicthumbs',
        'item' => 'thumb',
        'attribute' => 'topic'
      ];
    }
    if ($images->length > 0) {
      $thumbs = $parent->appendElement($names['list']);
      /** @var \Papaya\XML\Element $imageNode */
      foreach ($images as $imageNode) {
        $thumbNode = $thumbs
          ->appendElement(
            $names['item'],
            [
              $names['attribute'] => $dom->xpath()->evaluate(
                $this->_pattern['page_id'], $imageNode
              )
            ]
          )
          ->appendElement(
            'papaya:media',
            [
              'src' => $imageNode->getAttribute('src'),
              'resize' => $this->_resizeMode
            ]
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
    return;
  }
}
