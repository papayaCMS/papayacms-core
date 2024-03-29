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
namespace Papaya\CMS\Output\Teaser {

  use Papaya\UI;
  use Papaya\XML;

  /**
   * Extract teaser image information from the given subtopic elements, creates a list
   * of scaled teaser image tags or replaces the teaser images.
   *
   * @package Papaya-Library
   * @subpackage UI-Content
   */
  class Images extends UI\Control {
    /**
     * thumbnail width
     *
     * @var int
     */
    private $_width;

    /**
     * thumbnail height
     *
     * @var int
     */
    private $_height;

    /**
     * thumbnail resize mode (abs, max, min, mincrop)
     *
     * @var int
     */
    private $_resizeMode = 'max';

    /**
     * Xpath expressions used to find and iterate the teaser images
     *
     * @var array
     */
    private $_pattern = [
      'images' => '*[self::teaser or self::subtopic]/image//*[self::img or local-name() = "media"]',
      'teaser_images' => 'teaser/image//*[name() = "img" or local-name() = "media"]',
      'subtopic_images' => 'subtopic/image//*[name() = "img" or local-name() = "media"]',
      'page_id' => 'string(ancestor::subtopic/@no|ancestor::teaser/@page-id)'
    ];

    /**
     * Create object and store given parameters
     *
     * @param int $width
     * @param int $height
     * @param string $resizeMode
     */
    public function __construct($width, $height, $resizeMode = 'max') {
      $this->_width = (int)$width;
      $this->_height = (int)$height;
      $this->_resizeMode = $resizeMode;
    }

    public function replaceIn(XML\Element $parent) {
      /** @var \Papaya\XML\Document $document */
      $document = $parent->ownerDocument;
      $document->registerNamespaces(
        [
          'papaya' => XML\Document::XMLNS_PAPAYA
        ]
      );
      $images = $document->xpath()->evaluate($this->_pattern['images'], $parent);
      /** @var \DOMElement $imageNode */
      foreach ($images as $imageNode) {
        $imageNode
          ->parentNode
          ->insertBefore(
            $thumbNode = $document->createElement('papaya:media'),
            $imageNode
          );
        $imageNode->parentNode->removeChild($imageNode);
        $thumbNode->setAttribute('src', $imageNode->getAttribute('src'));
        $thumbNode->setAttribute('resize', $this->_resizeMode);
        if ($this->_width > 0) {
          $thumbNode->setAttribute('width', (int)$this->_width);
        }
        if ($this->_height > 0) {
          $thumbNode->setAttribute('height', (int)$this->_height);
        }
      }
    }

    /**
     * Append teaser thumbnail tags to given parent element.
     *
     * @param XML\Element $parent
     *
     * @param XML\Element|null $teasers
     * @return XML\Element|null
     */
    public function appendTo(XML\Element $parent, XML\Element $teasers = NULL) {
      /** @var \Papaya\XML\Document $document */
      $document = $parent->ownerDocument;
      $document->registerNamespaces(
        [
          'papaya' => XML\Document::XMLNS_PAPAYA
        ]
      );
      $source = $teasers ?: $parent;
      $sourceDocument = $source->ownerDocument;
      $images = $sourceDocument->xpath()->evaluate($this->_pattern['teaser_images'], $source);
      $names = [
        'list' => 'teaser-thumbnails',
        'item' => 'thumbnail',
        'attribute' => 'page-id'
      ];
      if ($images->length < 1) {
        $images = $sourceDocument->xpath()->evaluate($this->_pattern['subtopic_images'], $source);
        $names = [
          'list' => 'subtopicthumbs',
          'item' => 'thumb',
          'attribute' => 'topic'
        ];
      }
      if ($images->length > 0) {
        $thumbs = $parent->appendElement($names['list']);
        /** @var XML\Element $imageNode */
        foreach ($images as $imageNode) {
          $thumbNode = $thumbs
            ->appendElement(
              $names['item'],
              [
                $names['attribute'] => $sourceDocument->xpath()->evaluate(
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
      return NULL;
    }
  }
}
