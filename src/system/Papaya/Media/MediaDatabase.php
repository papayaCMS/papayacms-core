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
namespace Papaya\Media {

  use Papaya\Application\Access as ApplicationAccess;
  use Papaya\Media\Thumbnail\Calculation;
  use Papaya\Media\Thumbnail\Calculation\Contain;
  use Papaya\Media\Thumbnail\Calculation\Cover;
  use Papaya\Media\Thumbnail\Calculation\CoverCrop;
  use Papaya\Media\Thumbnail\Calculation\Fixed;
  use Papaya\CMS\Reference\Media as MediaReference;

  class MediaDatabase implements ApplicationAccess {

    use ApplicationAccess\Aggregation;

    public function createThumbnailGenerator($fileId, $revision, $name = '') {
      return new Thumbnails($fileId, $revision, $name);
    }

    public function createCalculation(array $targetSize, array $sourceSize, $mode = Calculation::MODE_CONTAIN) {
      list($targetWidth, $targetHeight) = $targetSize;
      list($sourceWidth, $sourceHeight) = $sourceSize;
      switch ($mode) {
      case CALCULATION::MODE_CONTAIN:
        return new Contain($sourceWidth, $sourceHeight, $targetWidth, $targetHeight);
      case CALCULATION::MODE_CONTAIN_PADDED:
        return new Contain($sourceWidth, $sourceHeight, $targetWidth, $targetHeight, TRUE);
      case CALCULATION::MODE_COVER:
        return new Cover($sourceWidth, $sourceHeight, $targetWidth, $targetHeight);
      case CALCULATION::MODE_COVER_CROPPED:
        return new CoverCrop($sourceWidth, $sourceHeight, $targetWidth, $targetHeight);
      case CALCULATION::MODE_FIX:
      default:
        return new Fixed($sourceWidth, $sourceHeight, $targetWidth, $targetHeight);
      }
    }

    public function createReference($fileId, $revision, $name = '') {
      $reference = new MediaReference();
      $reference->papaya($this->papaya());
      $reference->setMediaId($fileId);
      $reference->setMediaVersion($revision);
      $reference->setTitle($name);
      return $reference;
    }
  }
}
