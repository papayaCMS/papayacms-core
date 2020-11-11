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
  use Papaya\Configuration\CMS as CMSSettings;
  use Papaya\File\System\Factory as FileSystemFactory;
  use Papaya\Graphics\Color;
  use Papaya\Graphics\GD\Filter\CopyResampled;
  use Papaya\Graphics\GD\GDLibrary;
  use Papaya\Graphics\ImageTypes;
  use Papaya\Media\Thumbnail\Calculation;
  use Papaya\Media\Thumbnail\Calculation\Contain;
  use Papaya\Media\Thumbnail\Calculation\Cover;
  use Papaya\Media\Thumbnail\Calculation\CoverCrop;
  use Papaya\Media\Thumbnail\Calculation\Fixed;
  use Papaya\MemoryLimit;
  use Papaya\Message;
  use Papaya\Message\Logable;
  use Papaya\Utility\File\Path;

  class Thumbnails implements ApplicationAccess {

    use ApplicationAccess\Aggregation;

    /**
     * @var string
     */
    private $_fileId;

    /**
     * @var int
     */
    private $_fileRevision;

    private static $_extensions = [
      ImageTypes::MIMETYPE_GIF => '.gif',
      ImageTypes::MIMETYPE_JPEG => '.jpg',
      ImageTypes::MIMETYPE_PNG => '.png',
      ImageTypes::MIMETYPE_BMP => '.bmp',
      ImageTypes::MIMETYPE_WBMP => '.wbmp',
      ImageTypes::MIMETYPE_WEBP => '.webp'
    ];

    /**
     * @var Color
     */
    private $_backgroundColor;
    /**
     * @var FileSystemFactory
     */
    private $_fileSystem;
    /**
     * @var GDLibrary
     */
    private $_gd;

    /**
     * @param string $fileId
     * @param int $fileRevision
     */
    public function __construct($fileId, $fileRevision) {
      $this->_fileId = (string)$fileId;
      $this->_fileRevision = (int)$fileRevision;
    }

    public function createCalculation($targetWidth, $targetHeight, $mode = Calculation::MODE_CONTAIN) {
      $gd = $this->gd();
      list($sourceWidth, $sourceHeight) = $gd->getImageSize($this->getSourceFileName());
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

    public function createThumbnail(Calculation $calculation, $mimeType = NULL, $useCache = TRUE) {
      $gd = $this->gd();
      $sourceFileName = $this->getSourceFileName();
      if (!$this->fileSystem()->getFile($sourceFileName)->isReadable()) {
        $this->logError(
          Message::SEVERITY_ERROR,
          sprintf(
            'Can not create thumbnail, image file not found: %s', $sourceFileName
          )
        );
        return NULL;
      }
      if (NULL ===$mimeType) {
        $mimeType = $gd->identifyType($sourceFileName);
      }
      $targetFileName = $this->getTargetFileName($calculation, $mimeType);
      $targetFile = $this->fileSystem()->getFile($targetFileName);
      if ($useCache && $targetFile->isReadable()) {
        return new Thumbnail($targetFileName, NULL, $mimeType);
      }
      $sourceSize = $gd->getImageSize($sourceFileName);
      $targetSize = $calculation->getTargetSize();
      $requiredMemory = ($sourceSize[0] * $sourceSize[1] * 4) + ($targetSize[0] * $targetSize[1] * 4);
      $memoryLimit = new MemoryLimit();
      if (!$memoryLimit->increase($requiredMemory)) {
        $this->logError(
          Message::SEVERITY_ERROR,
          sprintf(
            'Can not increase memory limit for thumbnail create (%d needed)', $requiredMemory
          )
        );
        return NULL;
      }
      if ($source = $gd->load($sourceFileName)) {
        $targetDirectory = $targetFile->getDirectory();
        if (!$targetDirectory->exists()) {
          $targetDirectory->create();
        }
        $destination = $gd->create($targetSize[0], $targetSize[1], $this->getBackgroundColor());

        $destination->filter(
          new CopyResampled(
            $source, $calculation->getSource(), $calculation->getDestination()
          )
        );
        $destination->save($mimeType, $targetFileName);
        return new Thumbnail($targetFileName, NULL, $mimeType);
      }
      $this->logError(
        Message::SEVERITY_ERROR,
        sprintf(
          'Can not load media file: %s', $sourceFileName
        )
      );
      return NULL;
    }

    public function delete($ignoreRevision = FALSE) {
      $subDirectories = $this->papaya()->options->get(CMSSettings::MEDIADB_SUBDIRECTORIES, 0);
      $relativePath = '';
      for ($i = 0; $i < $subDirectories; $i++) {
        $relativePath .= '/'.substr($this->_fileId, $i, 1);
      }
      $startsWith = $this->_fileId.($ignoreRevision ? '' : 'v'.$this->_fileRevision);
      $this->deleteInPath(
        $startsWith,
        $this->papaya()->options->get(CMSSettings::PATH_MEDIAFILES, ''),
        $relativePath
      );
      $this->deleteInPath(
        $startsWith,
        $this->papaya()->options->get(CMSSettings::PATH_PUBLICFILES, ''),
        $relativePath
      );
    }

    private function deleteInPath($startsWith, $basePath, $relativePath) {
      if (empty($basePath)) {
        return;
      }
      $directory = $this->fileSystem()->getDirectory(Path::cleanup($basePath.$relativePath));
      if (!$directory->exists()) {
        return;
      }
      foreach ($directory->getEntries() as $fileInfo) {
        if (
          $fileInfo->isFile() &&
          0 === stripos($fileInfo->getBasename(), $startsWith)
        ) {
          $this->fileSystem()->getFile($fileInfo->getFilename())->unlink();
        }
      }
    }

    private function getTargetFileName(Calculation $calculation, $mimeType = ImageTypes::MIMETYPE_PNG) {
      $basePath = $this->papaya()->options->get(CMSSettings::PATH_THUMBFILES, '');
      $parameters = [
        'color' => $this->getBackgroundColor()->toHexString(TRUE)
      ];
      $fileName = sprintf(
        '%sv%d_%s_%s%s',
        $this->_fileId,
        $this->_fileRevision,
        $calculation->getIdentifier(),
        md5(json_encode($parameters)),
        $this->getExtension($mimeType)
      );
      return Path::cleanup($basePath.$this->getMediaSubPath()).$fileName;
    }

    private function getSourceFileName() {
      $basePath = $this->papaya()->options->get(CMSSettings::PATH_MEDIAFILES, '');
      $fileName = sprintf('%sv%d', $this->_fileId, $this->_fileRevision);
      return Path::cleanup($basePath.$this->getMediaSubPath()).$fileName;
    }

    private function getMediaSubPath() {
      $subDirectories = $this->papaya()->options->get(CMSSettings::MEDIADB_SUBDIRECTORIES, 0);
      $relativePath = '';
      for ($i = 0; $i < $subDirectories; $i++) {
        $relativePath .= '/'.substr($this->_fileId, $i, 1);
      }
      return $relativePath;
    }

    public function setBackgroundColor(Color $color) {
      $this->_backgroundColor = $color;
    }

    public function getBackgroundColor() {
      if (NULL === $this->_backgroundColor) {
        try {
          $colorString = $this->papaya()->options->get(CMSSettings::THUMBS_BACKGROUND, '#FFF0');
          $this->_backgroundColor = Color::createFromString($colorString);
        } catch (\InvalidArgumentException $e) {
          $this->_backgroundColor = Color::createGray(255, 0);
        }
      }
      return $this->_backgroundColor;
    }

    private function getExtension($mimeType) {
      $mimeType = strtolower($mimeType);
      return isset(self::$_extensions[$mimeType]) ? self::$_extensions[$mimeType] : '';
    }

    /**
    * Log error message if enabled
    *
    * @param int $severity Level
    * @param string $message Message
    */
    private function logError($severity, $message) {
      if ($this->papaya()->options->get(CMSSettings::LOG_ERROR_THUMBNAIL, TRUE)) {
        $this->papaya()->messages->log(
          $severity,
          Logable::GROUP_CONTENT,
          $message
        );
      }
    }

    public function gd(GDLibrary $gd = NULL) {
      if (isset($gd)) {
        $this->_gd = $gd;
      } elseif (NULL === $this->_gd) {
        $this->_gd = new GDLibrary();
      }
      return $this->_gd;
    }


    public function fileSystem(FileSystemFactory $fileSystem = NULL) {
      if (NULL !== $fileSystem) {
        $this->_fileSystem = $fileSystem;
      } elseif (NULL === $this->_fileSystem) {
        $this->_fileSystem = new FileSystemFactory();
      }
      return $this->_fileSystem;
    }
  }
}

