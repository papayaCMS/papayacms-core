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

use Papaya\CMS\CMSConfiguration as CMSSettings;
use Papaya\Graphics\Color;
use Papaya\Graphics\ImageTypes;


/**
* Thumbnail creation
*
* @package Papaya
* @subpackage Images-Scale
*/
class base_thumbnail extends base_object {

  /**
  * output filetype
  * @var string $thumbnailType
  */
  public $thumbnailType;

  public $lastThumbSize = [0,0];

  /**
   * @var  base_mediadb
   */
  private $mediaDB;

  private $_fileTypes = [
    1 => ImageTypes::MIMETYPE_GIF,
    2 => ImageTypes::MIMETYPE_JPEG,
    3 => ImageTypes::MIMETYPE_PNG,
  ];

  /**
  * Get thumbnail for image
  *
  * @param string $fileId
  * @param integer $fileVersion
  * @param integer $width
  * @param integer $height
  * @param string $mode
  * @param array $params
  * @return string|NULL
  */
  public function getThumbnail(
    $fileId, $fileVersion, $width = NULL, $height = NULL, $mode = NULL, $params = NULL
  ) {
    $width = (NULL !== $width)
      ? $width : (int)$this->papaya()->options->get(CMSSettings::MEDIADB_THUMBSIZE, 0);
    $height = (NULL !== $height)
      ? $height : (int)$this->papaya()->options->get(CMSSettings::MEDIADB_THUMBSIZE, 0);
    if (!empty($params['bgcolor'])) {
      $bgColor = $params['bgcolor'];
    } else {
      $bgColor = $this->papaya()->options->get(CMSSettings::THUMBS_BACKGROUND, '#FFFFFF');
    }
    if (NULL === $mode) {
      $mode = Papaya\Media\Thumbnail\Calculation::MODE_CONTAIN;
    }
    $fileType = $this->papaya()->options->get(CMSSettings::THUMBS_FILETYPE, $this->thumbnailType);
    $mimeType = isset($this->_fileTypes[$fileType]) ? $this->_fileTypes[$fileType] : NULL;
    $this->mediaDB = base_mediadb::getInstance();
    $file = $this->mediaDB->getFile($fileId, $fileVersion);
    $fileVersion = ($fileVersion > 0) ? $fileVersion : $file['current_version_id'];
    $thumbnails = new \Papaya\Media\Thumbnails($fileId, $fileVersion);
    if (!empty($bgColor)) {
      $thumbnails->setBackgroundColor(Color::createFromString($bgColor));
    }
    $calculation = $thumbnails->createCalculation([$width, $height], $mode);
    $thumbnail = $thumbnails->createThumbnail($calculation, $mimeType);
    if ($thumbnail) {
      $this->lastThumbSize = $calculation->getTargetSize();
      $thumbnail->setName($file['file_name']);
      return $thumbnail;
    }
    return NULL;
  }
}
