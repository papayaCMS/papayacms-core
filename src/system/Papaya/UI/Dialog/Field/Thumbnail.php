<?php
/*
 * papaya CMS
 *
 * @copyright 2000-2020 by papayaCMS project - All rights reserved.
 * @link http://www.papaya-cms.com/
 * @license http://www.gnu.org/licenses/old-licenses/gpl-2.0.html GNU General Public License, version 2
 *
 *  You can redistribute and/or modify this script under the terms of the GNU General Public
 *  License (GPL) version 2, provided that the copyright and license notes, including these
 *  lines, remain unmodified. papaya is distributed in the hope that it will be useful, but
 *  WITHOUT ANY WARRANTY; without even the implied warranty of MERCHANTABILITY or FITNESS
 *  FOR A PARTICULAR PURPOSE.
 */

namespace Papaya\UI\Dialog\Field {

  use Papaya\UI;
  use Papaya\UI\Reference\Thumbnail as ThumbnailReference;
  use Papaya\XML;

  class Thumbnail extends UI\Dialog\Field {
    /**
     * Message image
     *
     * @var string
     */
    protected $_fileId = '';

    /**
     * Image width
     *
     * @var int
     */
    protected $_width = 100;

    /**
     * Image height
     *
     * @var int
     */
    protected $_height = 100;

    /**
     * Resize mode
     *
     * @var string
     */
    protected $_mode = 'max';

    /**
     * @var \base_thumbnail
     */
    protected $_thumbnail;

    /**
     * @var ThumbnailReference
     */
    protected $_thumbnailReference;

    /**
     * Create object and assign needed values
     *
     * @param string $fileId
     * @param string $caption
     * @param int $width
     * @param int $height
     * @param string $mode
     */
    public function __construct(
      $fileId, $caption = NULL, $width = 100, $height = 100, $mode = 'max'
    ) {
      $this->_fileId = $fileId;
      if (NULL !== $caption) {
        $this->setCaption($caption);
      }
      $this->_width = $width;
      $this->_height = $height;
      $this->_mode = $mode;
    }

    /**
     * Append image field to dialog xml dom
     *
     * @param XML\Element $parent
     */
    public function appendTo(XML\Element $parent) {
      $field = $this->_appendFieldTo($parent);

      $thumbnail = $this->thumbnailGenerator()->getThumbnail(
        $this->_fileId,
        NULL,
        $this->_width,
        $this->_height,
        $this->_mode
      );

      $this->thumbnailReference()->setThumbnailMode($this->_mode);
      $this->thumbnailReference()->setThumbnailSize($this->_width.'x'.$this->_height);
      $this->thumbnailReference()->setMediaUri($thumbnail);

      $field->appendElement(
        'image',
        [
          'src' => $this->thumbnailReference()->get(),
          'mode' => $this->_mode
        ]
      );
    }

    /**
     * @param \base_thumbnail $thumbnail
     *
     * @return \base_thumbnail
     */
    public function thumbnailGenerator(\base_thumbnail $thumbnail = NULL) {
      if (NULL !== $thumbnail) {
        $this->_thumbnail = $thumbnail;
      } elseif (NULL === $this->_thumbnail) {
        $this->_thumbnail = new \base_thumbnail();
      }
      return $this->_thumbnail;
    }

    /**
     * @param ThumbnailReference $thumbnailReference
     *
     * @return ThumbnailReference
     */
    public function thumbnailReference(ThumbnailReference $thumbnailReference = NULL) {
      if (NULL !== $thumbnailReference) {
        $this->_thumbnailReference = $thumbnailReference;
      } elseif (NULL === $this->_thumbnailReference) {
        $this->_thumbnailReference = new ThumbnailReference();
        $this->_thumbnailReference->papaya($this->papaya());
        $this->_thumbnailReference->setThumbnailMode($this->_mode);
        $this->_thumbnailReference->setThumbnailSize($this->_width.'x'.$this->_height);
        $this->_thumbnailReference->setExtension('png');
        $this->_thumbnailReference->setPreview($this->papaya()->request->isPreview);
        $this->_thumbnailReference->load($this->papaya()->request);
      }
      return $this->_thumbnailReference;
    }
  }
}
