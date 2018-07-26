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
* Thumbnail creation
*
* @package Papaya
* @subpackage Images-Scale
*/
class base_thumbnail extends base_object {
  /**
  * base path for thumbnails
  * @var string $thumbnailDirectory
  */
  var $thumbnailDirectory = PAPAYA_PATH_THUMBFILES;

  /**
  * take the first letters of the filename to create subdirectories
  * @var integer
  */
  var $subDirectories = PAPAYA_MEDIADB_SUBDIRECTORIES;

  /**
  * thumbnail width
  * @var string $width
  */
  var $width = PAPAYA_MEDIADB_THUMBSIZE;

  /**
  * thumbnail height
  * @var string $height
  */
  var $height = PAPAYA_MEDIADB_THUMBSIZE;

  /**
  * resize mode (max = maximal, maxfill = maximum filled, min = minimal,
  * mincrop = minimal cropped, abs = absolute)
  * @var string $resizeFlag
  */
  var $resizeFlag = 'max';

  /**
  * ignore cache and create
  * @var string $createAll
  */
  var $createAll = FALSE;

  /**
  * output filetype
  * @var string $thumbnailType
  */
  var $thumbnailType = PAPAYA_THUMBS_FILETYPE;

  /**
  * jpeg quality for thumbnail output
  * @var string $jpegQuality
  */
  var $jpegQuality = PAPAYA_THUMBS_JPEGQUALITY;

  /**
  * path to a template file for thumbnails
  * @var string $tempFileName
  */
  var $tempFileName = './border.jpg';

  /**
  * supported features in gd
  * @var string $imageTypes
  */
  var $imageTypes;

  /**
  * force the extension in thumnail filename
  * @var boolean
  */
  var $forceExtension = TRUE;

  /**
  * error messages
  * @var string $error
  */
  var $error;

  /**
  * Background color in rgb
  * @var array $backgroundColor
  */
  var $backgroundColor = array('r' => 255, 'g' => 255, 'b' => 255);

  /**
  * save last calculated thumbsize in buffer
  * @var array
  */
  var $lastThumbSize = array(0,0);

  /**
  * store the memoty limit if it is changed, restore it in desctructor
  *
  * @var NULL|string
  */
  private static $_configuredMemoryLimit = NULL;

  /**
   * @var int
   */
  private $cropWidth = 0;

  /**
   * @var int
   */
  private $cropHeight = 0;

  /**
   * @var int
   */
  private $xOffset = 0;

  /**
   * @var int
   */
  private $yOffset = 0;

  /**
   * @var  base_mediadb
   */
  private $mediaDB = NULL;

  /**
  * PHP5 constructor
  *
  * @access public
  */
  function __construct() {
    if (function_exists('gd_info')) {
      $gdInfo = gd_info();
      $this->imageTypes = array(
        'gif_read' => $gdInfo['GIF Read Support'],
        'gif_write' => $gdInfo['GIF Create Support'],
        'png' => $gdInfo['PNG Support'],
      );
      if (isset($gdInfo['JPEG Support'])) {
        $this->imageTypes['jpg'] = $gdInfo['JPEG Support'];
      } elseif (isset($gdInfo['JPG Support'])) {
        $this->imageTypes['jpg'] = $gdInfo['JPG Support'];
      } else {
        $this->imageTypes['jpg'] = FALSE;
      }
    } elseif (function_exists('imagetypes')) {
      $imageTypes = imagetypes();
      $this->imageTypes = array(
        'gif_read' => $imageTypes & IMG_GIF,
        'gif_write' => $imageTypes & IMG_GIF,
        'png' => $imageTypes & IMG_PNG,
        'jpg' => $imageTypes & IMG_JPG
      );
    } else {
      $this->imageTypes = array(
        'gif_read' => FALSE,
        'gif_write' => FALSE,
        'png' => FALSE,
        'jpg' => FALSE
      );
    }
  }

  /**
  * Make sure that GD supports the image type specified in $type
  *
  * @param integer $type image type
  * @param boolean $readOnly Only read access needed
  * @return boolean TRUE if the image type is supported by GD, otherwise FALSE
  */
  function checkTypeInGD($type, $readOnly = FALSE) {
    switch ($type) {
    case 1:
      if ($readOnly) {
        if (!$this->imageTypes['gif_read']) {
          $this->setError(MSG_WARNING, 'GIF (read) not supported.');
          return FALSE;
        }
      } elseif (!$this->imageTypes['gif_write']) {
        $this->setError(MSG_WARNING, 'GIF (write) not supported.');
        return FALSE;
      }
      break;
    case 2:
      if (!$this->imageTypes['jpg']) {
        $this->setError(MSG_WARNING, 'JPEG not supported.');
        return FALSE;
      }
      break;
    case 3:
      if (!$this->imageTypes['png']) {
        $this->setError(MSG_WARNING, 'PNG not supported.');
        return FALSE;
      }
      break;
    default:
      $this->setError(MSG_WARNING, 'Unknown image file format.');
      return FALSE;
    }
    return TRUE;
  }

  /**
   * Calculates thumbnail size
   *
   * @param integer $orgWidth original width
   * @param integer $orgHeight original height
   * @param integer $newWidth
   * @param integer $newHeight
   * @param integer $mode resize mode (abs, max, min, mincrop)
   * @access public
   * @return array array($newWidth, $newHeight)
   */
  function calcSize($orgWidth, $orgHeight, $newWidth, $newHeight, $mode = NULL) {
    $this->lastThumbSize = array(0, 0);
    if ($orgWidth > 0 && $orgHeight > 0) {
      if ($newWidth < 1) {
        $newWidth = $orgWidth;
      }
      if ($newHeight < 1) {
        $newHeight = $orgHeight;
      }
      $iMode = isset($mode) ? $mode : $this->resizeFlag;
      if ($iMode == 'abs' ||
          $iMode == 'maxfill' ||
          $iMode == 'mincrop' ||
          $iMode == 'crop') {
        return array($newWidth, $newHeight);
      } else {
        $divWidth = $orgWidth / $newWidth;
        $divHeight = $orgHeight / $newHeight;
        if ($iMode == 'min') {
          if ($divWidth <= $divHeight) {
            $newHeight = round($orgHeight / $divWidth);
          } else {
            $newWidth = round($orgWidth / $divHeight);
          }
        } else {
          if ($divWidth >= $divHeight) {
            $newHeight = round($orgHeight / $divWidth);
          } else {
            $newWidth = round($orgWidth / $divHeight);
          }
        }
      }
      $this->lastThumbSize = array($newWidth, $newHeight);
    }
    return $this->lastThumbSize;
  }

  /**
  * Copies the file $fileName into memory
  *
  * @param string $fileName filename of picturefile
  * @param integer $fileType picturetype of file
  * @access public
  * @return resource|FALSE new created picture in memory
  */
  function loadFile($fileName, $fileType) {
    $result = FALSE;
    switch ($fileType) {
    case 1:
      $result = @imagecreatefromGIF($fileName);
      break;
    case 2:
      $result = @imagecreatefromJPEG($fileName);
      break;
    case 3:
      $result = @imagecreatefromPNG($fileName);
      break;
    }
    return $result;
  }

  /**
  * saves an image
  *
  * @param resource $im image resource
  * @param string $fileName target file name
  * @param integer $fileType target file type
  * @return FALSE|string absolute file name
  */
  function saveFile($im, $fileName, $fileType) {
    if (!(substr($fileName, 0, 1) == '/' || substr($fileName, 1, 1) == ':')) {
      // allows for complete path
      $fileLocation = $this->thumbnailDirectory.$fileName;
    } else {
      $fileLocation = $fileName;
    }

    if ($extension = $this->getThumbFileExt()) {
      $extension = '.'.$extension;
      $currentExtension = strtolower(substr($fileLocation, -4));
      if ($this->forceExtension) {
        if (substr($currentExtension, 0, 1) != '.' ||
            $currentExtension != $extension) {
          $fileLocation .= $extension;
        } else {
          $fileLocation = substr($fileLocation, 0, -4).$extension;
        }
      }
      /* use a temporary file because the image functions
         don't support stream wrappers */
      $temporaryFile = tempnam(PAPAYA_PATH_CACHE, 'tmp-thumbnail-');
      $saved = FALSE;
      switch ($fileType) {
      case 1:
        $saved = imageGIF($im, $temporaryFile);
        break;
      case 2:
        $saved = imageJPEG($im, $temporaryFile, $this->jpegQuality);
        break;
      case 3:
        $saved = imagePNG($im, $temporaryFile);
        break;
      }
      if (TRUE !== $saved) {
        return FALSE;
      }
      if (TRUE !== copy($temporaryFile, $fileLocation)) {
        return FALSE;
      }
      unlink($temporaryFile);
      return $fileName;
    }
    return FALSE;
  }

  /**
   * creates a filename for the thumbnail cache
   *
   * @access private
   * @param string $fileName
   * @param int $versionId
   * @param integer $width image width
   * @param integer $height image height
   * @param integer $imageType image file type
   * @param string $filter filter (resize mode)
   * @param array $params
   * @return string
   */
  function getThumbFileId(
    $fileName, $versionId, $width, $height, $imageType, $filter, $params = NULL
  ) {
    $result = $fileName.'v'.$versionId.'_'.$filter.'_'.$width.'x'.$height;
    $attrs = array();
    if (isset($params) && is_array($params)) {
      $attrs = $params;
    }
    if (empty($params['bgcolor']) &&
        defined('PAPAYA_THUMBS_BACKGROUND') &&
        PAPAYA_THUMBS_BACKGROUND != '') {
      $attrs['bgcolor'] = PAPAYA_THUMBS_BACKGROUND;
    }
    if (defined('PAPAYA_THUMBS_TRANSPARENT') && PAPAYA_THUMBS_TRANSPARENT) {
      $attrs['transparent'] = 'yes';
    }
    if (count($attrs) > 0) {
      $result .= '_'.md5(serialize($attrs));
    }
    return $result.'.'.$this->getThumbFileExt();
  }

  /**
   * Returns the absolute path to the file specified by the parameter
   * $fileName
   *
   * @param string $fileName string the name of the image file
   * @return string $path string the absolute file path
   */
  function getThumbFilePath($fileName) {
    $path = '';
    if ($this->subDirectories > 0) {
      for ($i = 0; $i < $this->subDirectories; $i++) {
        if (is_dir($this->thumbnailDirectory.$path.$fileName[$i]) ||
            mkdir($this->thumbnailDirectory.$path.$fileName[$i])) {
          $path .= $fileName[$i].'/';
        }
      }
    }
    return $path;
  }

  /**
  * Returns the filename of the thumbnail of the image file specified
  * by $fileName and additional parameters.
  *
  * @param string $fileName Name of the image file
  * @param int $versionId version number
  * @param int $width  thumbnail width
  * @param int $height  thumbnail height
  * @param int $imageType
  * @param int $filter
  * @param array $params
  * @return string the name of the thumbfile
  */
  function getThumbFileName(
    $fileName, $versionId, $width, $height, $imageType, $filter, $params = NULL
  ) {
    $result = $this->getThumbFilePath($fileName).
      $this->getThumbFileId($fileName, $versionId, $width, $height, $imageType, $filter, $params);
    return $result;
  }

  /**
  * Returns the file name ending for the given thumbnail type. The thumbnail
  * type is an integer value which will be mapped to a string.
  *
  * @return string File ending for thumbnail type
  */
  function getThumbFileExt() {
    switch ($this->thumbnailType) {
    case 1:
      return 'gif';
    case 2:
      return 'jpg';
    case 3:
      return 'png';
    default:
      $this->setError(MSG_WARNING, 'Thumbnail format is not supported.');
      return FALSE;
    }
  }

  /**
  * Convert memory amounts to bytes
  *
  * @param string $val
  * @access public
  * @return integer
  */
  function returnBytes($val) {
    return PapayaUtilBytes::fromString($val);
  }

  /**
   * Delete all thumbnail files for a fileId
   *
   * @param string $fileId
   * @return boolean
   */
  function deleteThumbs($fileId) {
    $fileId = strtolower($fileId);
    $fileIdLength = strlen($fileId);
    $path = '';
    if ($this->subDirectories > 0) {
      for ($i = 0; $i < $this->subDirectories; $i++) {
        $path .= $fileId[$i].'/';
      }
    }
    $this->deleteThumbFiles($this->thumbnailDirectory.$path, $fileId, $fileIdLength);
    if (defined('PAPAYA_PATH_PUBLICFILES') &&
        trim(PAPAYA_PATH_PUBLICFILES) != '' &&
        is_dir($_SERVER['DOCUMENT_ROOT'].PAPAYA_PATH_PUBLICFILES)) {
      $this->deleteThumbFiles(
        $_SERVER['DOCUMENT_ROOT'].PAPAYA_PATH_PUBLICFILES.$path,
        $fileId,
        $fileIdLength
      );
    }
  }

  /**
  * Delete thumbnail files
  *
  * @param string $directory
  * @param string $fileId
  * @param string $fileIdLength
  * @return void
  */
  function deleteThumbFiles($directory, $fileId, $fileIdLength) {
    if (is_dir($directory)) {
      if (function_exists('glob')) {
        if ($thumbFiles = glob($directory.$fileId.'*')) {
          foreach ($thumbFiles as $thumbFile) {
            unlink($thumbFile);
          }
        }
      } else {
        if ($dh = opendir($directory)) {
          while (is_string($thumbFile = readdir($dh))) {
            $thumbFileId = strtolower(substr($thumbFile, 0, $fileIdLength));
            if ($thumbFileId == $fileId) {
              unlink($directory.$thumbFile);
            }
          }
          closedir($dh);
        }
      }
    }
  }

  /**
  * Get thumbnail for image
  *
  * @param string $fileId
  * @param integer $fileVersion
  * @param integer $width
  * @param integer $height
  * @param string $mode
  * @param array $params
  * @return string
  */
  function getThumbnail(
    $fileId, $fileVersion, $width = NULL, $height = NULL, $mode = NULL, $params = NULL
  ) {
    $this->width = (NULL !== $width)  ? $width  : PAPAYA_MEDIADB_THUMBSIZE;
    $this->height = (NULL !== $height) ? $height : PAPAYA_MEDIADB_THUMBSIZE;

    if (NULL !== $mode) {
      $this->resizeFlag = $mode;
    }

    if (isset($params['cropwidth']) && NULL !== $params['cropwidth'] &&
        isset($params['cropwidth']) && NULL !== $params['cropheight']) {
      $this->cropWidth = $params['cropwidth'];
      $this->cropHeight = $params['cropheight'];
      $this->xOffset = empty($params['x_offset']) ? 0 : (int)$params['x_offset'];
      $this->yOffset = empty($params['y_offset']) ? 0 : (int)$params['y_offset'];
      // $this->resizeFlag = 'max';
    }
    if (isset($params['bgcolor']) && $params['bgcolor'] != '') {
      $bgColor = $params['bgcolor'];
    } elseif (defined('PAPAYA_THUMBS_BACKGROUND') && PAPAYA_THUMBS_BACKGROUND != '') {
      $bgColor = PAPAYA_THUMBS_BACKGROUND;
    } else {
      $bgColor = '#FFFFFF';
    }

    $this->mediaDB = base_mediadb::getInstance();
    $file = $this->mediaDB->getFile($fileId);
    $fileVersion = ($fileVersion > 0) ? $fileVersion : $file['current_version_id'];

    return $this->getThumb(
      $fileId,
      $fileVersion,
      $bgColor,
      array(
        'width' => $file['width'],
        'height' => $file['height'],
        'type' => $this->mediaDB->mimeToInteger($file['mimetype'])
      )
    );
  }

  /**
   * Get thumb (filename or image resource)
   *
   * @param string $fileId
   * @param integer $versionId
   * @param string $bgColor
   * @param array $fileData
   * @return FALSE|string thumb file name
   */
  function getThumb($fileId, $versionId, $bgColor = NULL, array $fileData = NULL) {
    if (empty($bgColor) &&
        defined('PAPAYA_THUMBS_BACKGROUND') &&
        PAPAYA_THUMBS_BACKGROUND != '') {
      $bgColor = PAPAYA_THUMBS_BACKGROUND;
    } elseif (empty($bgColor)) {
      $bgColor = '#FFFFFF';
    }

    if (($srcFileName = $this->mediaDB->getFileName($fileId, $versionId)) &&
        file_exists($srcFileName)) {
      $this->backgroundColor = $this->htmlToColor($bgColor);

      // load image data
      if ($fileData) {
        $orgWidth = $fileData['width'];
        $orgHeight = $fileData['height'];
        $orgType = $fileData['type'];
      } else {
        list($orgWidth, $orgHeight, $orgType) = @getimagesize($srcFileName);
      }

      // if the file is no image, quit
      if (!$orgType) {
        $this->setError(MSG_WARNING, 'No image.');
        return FALSE;
      }

      $thumbParams = NULL;

      /**
      * prepare image if necessary (crop, mincrop)
      */
      if (isset($this->cropWidth) && isset($this->cropHeight) &&
          $this->cropWidth > 0 && $this->cropHeight > 0) {
        $thumbCropName = $this->getThumbFileName(
          $fileId,
          $versionId,
          $this->cropWidth,
          $this->cropHeight,
          $this->thumbnailType,
          $this->resizeFlag,
          array(
            'cropwidth' => $this->cropWidth,
            'cropheight' => $this->cropHeight,
            'x_offset' => $this->xOffset,
            'y_offset' => $this->yOffset,
          )
        );
        $this->imageCrop(
          $srcFileName,
          $thumbCropName,
          $orgType,
          $this->cropWidth,
          $this->cropHeight,
          $this->xOffset,
          $this->yOffset
        );
        $srcFileName = $this->thumbnailDirectory.$thumbCropName;

        list($orgWidth, $orgHeight, $orgType) = @getimagesize($srcFileName);

        $thumbParams = array(
          'cropwidth' => $this->cropWidth,
          'cropheight' => $this->cropHeight,
          'x_offset' => $this->xOffset,
          'y_offset' => $this->yOffset,
        );
      }

      list($thumbWidth, $thumbHeight) =
        $this->calcSize($orgWidth, $orgHeight, $this->width, $this->height);

      $thumbFileName = $this->getThumbFileName(
        $fileId,
        $versionId,
        $thumbWidth,
        $thumbHeight,
        $this->thumbnailType,
        $this->resizeFlag,
        $thumbParams
      );

      //check cache
      if (file_exists($this->thumbnailDirectory.$thumbFileName)) {
        //get times for original file and thumbnail file
        $orgFileTime = filectime($srcFileName);
        $thumbFileTime = filectime($this->thumbnailDirectory.$thumbFileName);
        //force create through attribute
        if (!$this->createAll && $thumbFileTime > $orgFileTime) {
          // use cached thumbnail
          $this->lastThumbSize = array($thumbWidth, $thumbHeight);
          return $this->getThumbFileId(
            $fileId,
            $versionId,
            $thumbWidth,
            $thumbHeight,
            $this->thumbnailType,
            $this->resizeFlag,
            $thumbParams
          );
        }
      }

      //check image file type
      if ($this->checkTypeInGD($orgType, TRUE)) {
        //create thumbnail
        if ($this->resizeFlag == 'maxfill') {
          list($scaledWidth, $scaledHeight) =
            $this->calcSize($orgWidth, $orgHeight, $this->width, $this->height, 'max');
          $this->lastThumbSize = array($scaledWidth, $scaledHeight);
          $thumbFileMax = $this->getThumbFileName(
            $fileId,
            $versionId,
            $scaledWidth,
            $scaledHeight,
            $this->thumbnailType,
            'max'
          );
          $this->imageScale(
            $srcFileName,
            $thumbFileMax,
            $orgType,
            $orgWidth,
            $orgHeight,
            $scaledWidth,
            $scaledHeight
          );
          $cropped = $this->imageCrop(
            $this->thumbnailDirectory.$thumbFileMax,
            $thumbFileName,
            $this->thumbnailType,
            $thumbWidth,
            $thumbHeight
          );
          if ($cropped) {
            $this->lastThumbSize = array($thumbWidth, $thumbHeight);
            return $this->getThumbFileId(
              $fileId,
              $versionId,
              $thumbWidth,
              $thumbHeight,
              $this->thumbnailType,
              $this->resizeFlag,
              $thumbParams
            );
          }
        } elseif ($this->resizeFlag == 'mincrop') {
          list($scaledWidth, $scaledHeight) =
            $this->calcSize($orgWidth, $orgHeight, $this->width, $this->height, 'min');
          $this->lastThumbSize = array($scaledWidth, $scaledHeight);
          $thumbFileMin = $this->getThumbFileName(
            $fileId,
            $versionId,
            $scaledWidth,
            $scaledHeight,
            $this->thumbnailType,
            'min'
          );
          $this->imageScale(
            $srcFileName,
            $thumbFileMin,
            $orgType,
            $orgWidth,
            $orgHeight,
            $scaledWidth,
            $scaledHeight
          );
          $cropped = $this->imageCrop(
            $this->thumbnailDirectory.$thumbFileMin,
            $thumbFileName,
            $this->thumbnailType,
            $thumbWidth,
            $thumbHeight
          );
          if ($cropped) {
            $this->lastThumbSize = array($thumbWidth, $thumbHeight);
            return $this->getThumbFileId(
              $fileId,
              $versionId,
              $thumbWidth,
              $thumbHeight,
              $this->thumbnailType,
              $this->resizeFlag,
              $thumbParams
            );
          }
        } else {
          $this->lastThumbSize = array($thumbWidth, $thumbHeight);
          $scaled = $this->imageScale(
            $srcFileName,
            $thumbFileName,
            $orgType,
            $orgWidth,
            $orgHeight,
            $thumbWidth,
            $thumbHeight
          );
          if ($scaled) {
            return $this->getThumbFileId(
              $fileId,
              $versionId,
              $thumbWidth,
              $thumbHeight,
              $this->thumbnailType,
              $this->resizeFlag,
              $thumbParams
            );
          }
        }
      }
      return FALSE;
    } elseif (empty($srcFileName)) {
      $this->setError(MSG_WARNING, 'Unable to create thumbnail. Image file record not found.');
      return FALSE;
    } else {
      $this->setError(
        MSG_WARNING,
        sprintf(
          'Unable to create thumbnail. Image file "%s" not found.',
          $srcFileName
        )
      );
      return FALSE;
    }
  }

  /**
  * Set error message
  *
  * @param int $level Level
  * @param string $msg Message
  */
  function setError($level, $msg) {
    unset($this->error);
    $this->error = array('level' => $level, 'msg' => $msg);
    if ($this->papaya()->options->get('PAPAYA_LOG_ERROR_THUMBNAIL', TRUE)) {
      $message = new \PapayaMessageLog(
        $level,
        PapayaMessageLogable::GROUP_CONTENT,
        $msg
      );
      $message
        ->context()
        ->append(
          new \PapayaMessageContextBacktrace(2)
        );
      $this->papaya()->messages->dispatch($message);
    }
  }

  /**
   * scale an image
   *
   * @param string $srcFileName
   * @param string $destFileName
   * @param integer $orgType source image type
   * @param integer $orgWidth orignal width
   * @param integer $orgHeight original height
   * @param integer $thumbWidth thumbnail width
   * @param integer $thumbHeight thumbnail height
   * @access public
   * @return mixed cache filename or FALSE
   */
  function imageScale(
    $srcFileName, $destFileName, $orgType, $orgWidth, $orgHeight, $thumbWidth, $thumbHeight
  ) {
    $memoryNeeded = (
      ($orgWidth * $orgHeight * 4) + ($thumbWidth * $thumbHeight * 4)
    ) * 2;
    if (function_exists('memory_get_usage')) {
      $memoryNeeded += memory_get_usage();
    } else {
      $memoryNeeded += 4194304;
    }
    if ($memoryLimit = @ini_get('memory_limit')) {
      if (is_null(self::$_configuredMemoryLimit)) {
        self::$_configuredMemoryLimit = $memoryLimit;
      }
      $memoryLimit = $this->returnBytes($memoryLimit);
    } else {
      $memoryLimit = 8388608;
    }
    if (extension_loaded('suhosin') &&
        defined('PAPAYA_THUMBS_MEMORYCHECK_SUHOSIN') &&
        PAPAYA_THUMBS_MEMORYCHECK_SUHOSIN) {
      if ($suhosinMemoryLimit = @ini_get('suhosin.memory_limit')) {
        $suhosinMemoryLimit = $this->returnBytes($suhosinMemoryLimit);
      } else {
        $suhosinMemoryLimit = 0;
      }
      if ($suhosinMemoryLimit == 0) {
        $suhosinMemoryLimit = $memoryLimit;
      }
      if ($memoryNeeded >= $suhosinMemoryLimit) {
        $this->setError(
          MSG_WARNING,
          sprintf(
            'Can not scale "%s" from %dx%d to %dx%d. Not enough memory available.'.
            ' Needed: %s bytes Available: %s bytes.'.
            ' Increase blocked by Suhoshin in function "%s".',
            $srcFileName,
            $orgWidth,
            $orgHeight,
            $thumbWidth,
            $thumbHeight,
            number_format($memoryNeeded, 0, '.', ','),
            number_format($suhosinMemoryLimit, 0, '.', ','),
            __METHOD__
          )
        );
        return FALSE;
      }
    }
    if ($memoryLimit != -1 && $memoryLimit < $memoryNeeded) {
      ini_set('memory_limit', $memoryNeeded);
      $memoryLimit = $this->returnBytes(ini_get('memory_limit'));
    }
    $thumb = FALSE;
    if ($memoryLimit == -1 || $memoryLimit >= $memoryNeeded) {
      if ($im = $this->loadFile($srcFileName, $orgType)) {
        $fillBackground = TRUE;
        if (function_exists('imagecreatetruecolor')) {
          $thumb = @imagecreatetruecolor($thumbWidth, $thumbHeight);
          // jpgs (type 2) do not support transparency
          if ($thumb && $this->thumbnailType != 2) {
            if ($this->thumbnailType == 3 &&
                defined('PAPAYA_THUMBS_TRANSPARENT') &&
                PAPAYA_THUMBS_TRANSPARENT) {
              $fillBackground = FALSE;
            }
          }
        }
        if (!$thumb) {
          $thumb = imagecreate($thumbWidth, $thumbHeight);
        }
        if (isset($this->backgroundColor) && $fillBackground) {
          imagesavealpha($thumb, FALSE);
          imagealphablending($thumb, FALSE);
          $bgColorIdx = imagecolorallocatealpha(
            $thumb,
            $this->backgroundColor['r'],
            $this->backgroundColor['g'],
            $this->backgroundColor['b'],
            0
          );
          imagefilledrectangle($thumb, 0, 0, $thumbWidth, $thumbHeight, $bgColorIdx);
          imagealphablending($thumb, TRUE);
        } else {
          imagesavealpha($thumb, TRUE);
          imagealphablending($thumb, FALSE);
          $bgColor = imagecolorallocatealpha($thumb, 220, 220, 220, 127);
          imagefill($thumb, 0, 0, $bgColor);
          imagealphablending($thumb, TRUE);
        }
        if ($thumb) {
          if (function_exists('imagecopyresampled')) {
            imagecopyresampled(
              $thumb, $im, 0, 0, 0, 0, $thumbWidth, $thumbHeight, $orgWidth, $orgHeight
            );
          } else {
            imagecopyresized(
              $thumb, $im, 0, 0, 0, 0, $thumbWidth, $thumbHeight, $orgWidth, $orgHeight
            );
          }
          if (file_exists($this->tempFileName)) {
            list($tmpWidth, $tmpHeight, $tmpType) =
              getimagesize($this->tempFileName);
            if ($tmp = $this->loadFile($this->tempFileName, $tmpType)) {
              if (NULL !== $this->xOffset) {
                $left = $this->xOffset;
              } else {
                $left = round(($tmpWidth - $thumbWidth) / 2);
              }
              if (NULL !== $this->yOffset) {
                $top = $this->yOffset;
              } else {
                $top = round(($tmpHeight - $thumbHeight) / 2);
              }
              imagecopy(
                $tmp, $thumb, $left, $top, 0, 0, $thumbWidth, $thumbHeight
              );
              imagedestroy($thumb);
              $thumb = $tmp;
            }
          }
          $saved = $this->saveFile($thumb, $destFileName, $this->thumbnailType);
          imagedestroy($im);
          imagedestroy($thumb);
          return $saved;
        } else {
          $this->setError(
            MSG_WARNING,
            'Cannot create thumbnail. In function base_thumbnail::imageScale()'
          );
          imagedestroy($im);
          return FALSE;
        }
      } else {
        $this->setError(
          MSG_WARNING,
          'Cannot read image file. In function base_thumbnail::imageScale()'
        );
        return FALSE;
      }
    } else {
      $this->setError(
        MSG_WARNING,
        sprintf(
          'Can not scale "%s" from %dx%d to %dx%d. Not enough memory available.'.
          ' Needed: %s bytes Available: %s bytes. Increase blocked in function "%s".',
          $srcFileName,
          $orgWidth,
          $orgHeight,
          $thumbWidth,
          $thumbHeight,
          number_format($memoryNeeded, 0, '.', ','),
          number_format($memoryLimit, 0, '.', ','),
          __METHOD__
        )
      );
      return FALSE;
    }
  }

  /**
   * crop image
   *
   * @param string $srcFileName source
   * @param string $destFileName destination
   * @param int $orgType
   * @param integer $width Width
   * @param integer $height Height
   * @param null|int $xOffset
   * @param null|int $yOffset
   * @return boolean Success
   */
  function imageCrop(
    $srcFileName, $destFileName, $orgType, $width, $height, $xOffset = NULL, $yOffset = NULL
  ) {
    $thumb = FALSE;
    $fillBackground = FALSE;
    if (is_file($srcFileName) && ($im = $this->loadFile($srcFileName, $orgType))) {
      if (function_exists('imagecreatetruecolor')) {
        $thumb = @imagecreatetruecolor($width, $height);
        $fillBackground = TRUE;
        // jpgs do not support transparency
        if ($thumb && $this->thumbnailType != 2) {
          if ($this->thumbnailType == 3 &&
              defined('PAPAYA_THUMBS_TRANSPARENT') &&
              PAPAYA_THUMBS_TRANSPARENT) {
            $fillBackground = FALSE;
          }
        }
      }
      if (!$thumb) {
        $thumb = imagecreate($width, $height);
      }
      if (isset($this->backgroundColor) && $fillBackground) {
        imagealphablending($thumb, FALSE);
        imagesavealpha($thumb, FALSE);
        imagepalettecopy($thumb, $im);
        $bgColorIdx = imagecolorallocate(
          $thumb,
          $this->backgroundColor['r'],
          $this->backgroundColor['g'],
          $this->backgroundColor['b']
        );
        imagefilledrectangle($thumb, 0, 0, $width, $height, $bgColorIdx);
        imagealphablending($thumb, TRUE);
      } else {
        imagealphablending($thumb, FALSE);
        imagesavealpha($thumb, TRUE);
        imagepalettecopy($thumb, $im);
        $transparent = imagecolorallocatealpha($thumb, 220, 220, 220, 127);
        imagefill($thumb, 0, 0, $transparent);
        imagealphablending($thumb, TRUE);
      }
      if ($thumb) {
        $orgWidth = imagesx($im);
        $orgHeight = imagesy($im);
        if ($orgWidth > $width) {
          $orgLeft = (NULL !== $xOffset) ? $xOffset : round(($orgWidth - $width) / 2);
          $left = 0;
        } else {
          $left = (NULL !== $xOffset) ? $xOffset : round(($width - $orgWidth) / 2);
          $orgLeft = 0;
          $width = $orgWidth;
        }
        if ($orgHeight > $height) {
          $orgTop = (NULL !== $yOffset) ? $yOffset : round(($orgHeight - $height) / 2);
          $top = 0;
        } else {
          $top = (NULL !== $yOffset) ? $yOffset : round(($height - $orgHeight) / 2);
          $orgTop = 0;
          $height = $orgHeight;
        }
        imagecopy($thumb, $im, $left, $top, $orgLeft, $orgTop, $width, $height);
        $saved = $this->saveFile($thumb, $destFileName, $this->thumbnailType);
        imagedestroy($im);
        imagedestroy($thumb);
        return $saved;
      } else {
        $this->setError(MSG_WARNING, 'Cannot create thumbnail.');
        imagedestroy($im);
        return FALSE;
      }
    } else {
      $this->setError(MSG_WARNING, 'Cannot read image.');
      return FALSE;
    }
  }

  /**
  * rotates an image by a given degree
  *
  * @param string $srcFileName location of source image
  * @param string $destFileName location of destination image
  * @param integer $orgType image type
  * @param integer $degrees degrees to rotate the image by; multiple of 90
  * @return boolean whether the operation could be accomplished
  */
  function imageRotate($srcFileName, $destFileName, $orgType, $degrees = 90) {
    if (function_exists('imagerotate') && is_file($srcFileName) &&
        ($im = $this->loadFile($srcFileName, $orgType)) && $degrees % 90 == 0) {
      $rotatedImage = imagerotate($im, $degrees, 0);
      header('Content-type: image/jpeg');
      $saved = $this->saveFile($rotatedImage, $destFileName, $this->thumbnailType);
      imagedestroy($im);
      return $saved;
    } else {
      $this->setError(MSG_WARNING, 'Image rotation failed.');
      return FALSE;
    }
  }

  /**
  * Convert html-color to rgb-color
  *
  * @param string $htmlColor
  * @access public
  * @return array $result rgb values
  */
  function htmlToColor($htmlColor) {
    if (strpos($htmlColor, '#') === FALSE) {
      $offset = 0;
    } else {
      $offset = 1;
    }
    $result['r'] = hexdec(substr($htmlColor, $offset, 2));
    $result['g'] = hexdec(substr($htmlColor, $offset + 2, 2));
    $result['b'] = hexdec(substr($htmlColor, $offset + 4, 2));
    return $result;
  }

  /**
  * restore the original memory limit
  */
  public function __destruct() {
    if (isset(self::$_configuredMemoryLimit)) {
      @ini_set('memory_limit', self::$_configuredMemoryLimit);
      self::$_configuredMemoryLimit = NULL;
    }
  }
}
