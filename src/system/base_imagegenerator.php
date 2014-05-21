<?php
/**
* Generate dynamic images
*
* @copyright 2002-2007 by papaya Software GmbH - All rights reserved.
* @link http://www.papaya-cms.com/
* @license http://www.gnu.org/licenses/old-licenses/gpl-2.0.html GNU General Public License, version 2
*
* You can redistribute and/or modify this script under the terms of the GNU General Public
* License (GPL) version 2, provided that the copyright and license notes, including these
* lines, remain unmodified. papaya is distributed in the hope that it will be useful, but
* WITHOUT ANY WARRANTY; without even the implied warranty of MERCHANTABILITY or FITNESS
* FOR A PARTICULAR PURPOSE.
*
* @package Papaya
* @subpackage Core
* @version $Id: base_imagegenerator.php 39647 2014-03-20 10:35:27Z weinert $
*/

/**
* Generate dynamic images
*
* @package Papaya
* @subpackage Core
*/
class base_imagegenerator extends base_db {

  /**
  * Parameter name
  * @var string $paramName
  */
  var $paramName = 'img';

  /**
  * papaya database table image configurations
  * @var string $tableImageConfs
  */
  var $tableImageConfs = PAPAYA_DB_TBL_IMAGES;
  /**
  * papaya database table modules
  * @var string $tableModules
  */
  var $tableModules = PAPAYA_DB_TBL_MODULES;
  /**
  * papaya database table module groups
  * @var string $tableModuleGroups
  */
  var $tableModuleGroups = PAPAYA_DB_TBL_MODULEGROUPS;

  /**
  * Last error message
  * @var string $lastError
  */
  var $lastError = '';

  /**
  * selected image configuration data
  * @var array $imageConfs
  */
  var $imageConf = NULL;

  /**
  * in public mode, the images are cached on server or client
  * @var boolean
  */
  var $publicMode = TRUE;

  protected $_validFormats = array(
    IMAGETYPE_GIF => 'GIF',
    IMAGETYPE_JPEG => 'JPEG',
    IMAGETYPE_PNG => 'PNG'
  );

  /**
  * Load image by ident
  *
  * @param integer $imageIdent
  * @access public
  * @return boolean
  */
  function loadByIdent($imageIdent) {
    unset($this->imageConf);
    $sql = "SELECT i.image_id, i.image_ident, i.image_title, i.image_data, i.image_modified,
                   i.image_format, i.image_cachemode, i.image_cachetime,
                   m.module_guid, m.module_path, m.module_file,
                   m.module_class, m.module_title
              FROM %s i
              LEFT OUTER JOIN %s m
                ON (m.module_guid = i.module_guid)
             WHERE i.image_ident = '%s'";
    $params = array($this->tableImageConfs, $this->tableModules, $imageIdent);
    if ($res = $this->databaseQueryFmt($sql, $params)) {
      if ($row = $res->fetchRow(DB_FETCHMODE_ASSOC)) {
        $this->imageConf = $row;
        return TRUE;
      }
    }
    return FALSE;
  }

  /**
  * load image identifiers by their plugin id (guid)
  *
  * I needed this for captcha image selection for the feedback forms
  *
  * @author David Rekowski <info@papaya-cms.com>
  * @param string $guid image generator module guid
  * @return mixed $result list of dynamic images of the given id, FALSE if none found
  */
  function getIdentifiersByGUID($guid) {
    $guidCondition = $this->databaseGetSQLCondition('m.module_guid', $guid);
    $sql = "SELECT i.image_id, i.image_ident, i.image_title,
                   m.module_guid, m.module_title
             FROM %s i
             LEFT OUTER JOIN %s m
               ON (m.module_guid = i.module_guid)
            WHERE $guidCondition";
    if ($res = $this->databaseQueryFmt($sql, array($this->tableImageConfs, $this->tableModules))) {
      $result = array();
      while ($row = $res->fetchRow(DB_FETCHMODE_ASSOC)) {
        $result[] = $row;
      }
      return $result;
    }
    return FALSE;
  }

  /**
  * Generates image
  *
  * @param boolean $outputImage optional, default value TRUE
  * @param mixed $params optional, default value NULL
  * @param integer $format optional, default value NULL
  * @access public
  * @return mixed string cache file name or boolean FALSE
  */
  function generateImage($outputImage = TRUE, $params = NULL, $format = NULL) {
    if (isset($this->imageConf) && isset($this->imageConf['module_guid'])) {
      $parent = NULL;
      $moduleObj = $this->papaya()->plugins->get(
        $this->imageConf['module_guid'],
        $parent,
        $this->imageConf['image_data']
      );
      if (isset($moduleObj) && is_object($moduleObj)) {
        $moduleObj->paramName = $this->paramName;
        if (isset($params)) {
          $this->params = $params;
        } else {
          $this->initializeParams();
        }
        if ($moduleObj->setAttributes($this->params)) {
          $imageFormat = $this->validateFormat(
            isset($format) ? $format : $this->imageConf['image_format']
          );
          $cacheId = $moduleObj->getCacheId();
          switch ($this->imageConf['image_cachemode']) {
          case 1 :
            $cacheTime = $this->papaya()->options->get(
              'PAPAYA_CACHE_TIME_FILES', 0
            );
            break;
          case 2 :
            $cacheTime = (int)$this->imageConf['image_cachetime'];
            break;
          default :
            $cacheTime = 0;
          }
          if ($this->publicMode && $cacheTime > 0) {
            $cache = PapayaCache::getService($this->papaya()->options);
            $imageData = $cache->read(
              'dynamic_images',
              $this->imageConf['image_ident'],
              array($cacheId, $imageFormat),
              $cacheTime,
              $this->imageConf['image_modified']
            );
            if ($imageData) {
              if ($outputImage) {
                $cacheCreated = $cache->created(
                  'dynamic_images',
                  $this->imageConf['image_ident'],
                  array($cacheId, $imageFormat),
                  $cacheTime,
                  $this->imageConf['image_modified']
                );
                $this->sendContentHeader($imageFormat, $cacheCreated + $cacheTime - time());
                echo $imageData;
                return TRUE;
              } else {
                return $imageData;
              }
            }
          }
          if ($image = $moduleObj->generateImage($this)) {
            if ($imageData = $this->getImageOutput($image, $imageFormat)) {
              if ($outputImage) {
                $this->sendContentHeader($imageFormat, $cacheTime);
                echo $imageData;
              }
              if ($this->publicMode && $moduleObj->cacheable && $cacheTime > 0) {
                $cache = PapayaCache::getService($this->papaya()->options);
                $cache->write(
                  'dynamic_images',
                  $this->imageConf['image_ident'],
                  array($cacheId, $imageFormat),
                  $imageData,
                  $cacheTime
                );
              }
              if ($outputImage) {
                return TRUE;
              } else {
                return $imageData;
              }
            } else {
              $this->lastError = 'Invalid image output';
            }
          } else {
            $this->lastError = $moduleObj->lastError;
          }
        } else {
          $this->lastError = 'Invalid image attributes';
        }
      } else {
        $this->lastError = 'Invalid image module';
      }
    } else {
      $this->lastError = 'Image data not found';
    }
    return FALSE;
  }

  /**
  * Send content header with content type gif, jpeg or png
  *
  * @access public
  */
  function sendContentHeader($imageFormat, $cacheTime) {
    $contentType = image_type_to_mime_type($imageFormat);
    header('Content-type: '.$contentType);
    if ($this->publicMode && $cacheTime > 0) {
      header('Expires: '.gmdate("D, d M Y H:i:s", time() + (int)$cacheTime)." GMT");
      header(
        sprintf(
          'Cache-Control: max-age=%s, pre-check=%s, no-transform',
          (int)$cacheTime,
          (int)$cacheTime
        )
      );
      header('Pragma:');
    } else {
      header('Expires: '.gmdate("D, d M Y H:i:s", time() - 86400)." GMT");
      header('Cache-Control:	no-store, no-cache, must-revalidate, post-check=0, pre-check=0');
      header('Pragma:	no-cache');
    }
  }

  /**
  * loads an image as gif, jpeg or png
  *
  * @access public
  * @param string $fileName Dateiname der Bilddatei
  * @return resource
  */
  function loadImage($fileName) {
    list(, , $fileType) = @getimagesize($fileName);
    $result = FALSE;
    switch ($fileType) {
    case IMAGETYPE_GIF :
      $result = @imagecreatefromGIF($fileName);
      break;
    case IMAGETYPE_JPEG :
      $result = @imagecreatefromJPEG($fileName);
      break;
    case IMAGETYPE_PNG :
      $result = @imagecreatefromPNG($fileName);
      imagesavealpha($result, TRUE);
      break;
    }
    return $result;
  }

  /**
   * Save image to gif, jpeg or png
   *
   * @param resource $im image resource
   * @param integer $format default PAPAYA_THUMBS_FILETYPE
   * @access public
   * @return boolean
   */
  function getImageOutput($im, $format = NULL) {
    if ($format > 0 && $format <= 3) {
      $fileFormat = (int)$format;
    } elseif (defined('PAPAYA_THUMBS_FILETYPE') && PAPAYA_THUMBS_FILETYPE > 0) {
      $fileFormat = PAPAYA_THUMBS_FILETYPE;
    } else {
      $fileFormat = IMAGETYPE_PNG;
    }
    ob_start();
    switch ($fileFormat) {
    case IMAGETYPE_GIF :
      imageGIF($im);
      break;
    case IMAGETYPE_JPEG :
      imageJPEG($im, NULL, 60);
      break;
    case IMAGETYPE_PNG :
      imagePNG($im);
      break;
    default :
      ob_end_clean();
      $this->lastError = 'Image output format not supported.';
      return FALSE;
    }
    return ob_get_clean();
  }

  /**
  * load a media file
  *
  * @param string $dataStr
  * @access public
  * @return resource Image resource
  */
  function getMediaFileImage($dataStr) {
    $mediaDB = base_mediadb::getInstance();
    $result = NULL;
    if (trim($dataStr) != '' && file_exists($mediaDB->getFileName($dataStr))) {
      $result = $this->loadImage($mediaDB->getFileName($dataStr));
    }
    return $result;
  }

  /**
  * Get color to rgb values
  *
  * @param string $colorStr
  * @access public
  * @return array $result rgb values
  */
  function colorToRGB($colorStr) {
    if (preg_match('~#[a-fA-F\d]{6}~', $colorStr)) {
      $result['red'] = hexdec(substr($colorStr, 1, 2));
      $result['green'] = hexdec(substr($colorStr, 3, 2));
      $result['blue'] = hexdec(substr($colorStr, 5, 2));
    } elseif (preg_match('~#[a-fA-F\d]{3}~', $colorStr)) {
      $result['red'] = hexdec(str_repeat(substr($colorStr, 1, 1), 2));
      $result['green'] = hexdec(str_repeat(substr($colorStr, 2, 1), 2));
      $result['blue'] = hexdec(str_repeat(substr($colorStr, 3, 2), 2));
    } else {
      $result['red'] = 0;
      $result['green'] = 0;
      $result['blue'] = 0;
    }
    return $result;
  }

  /**
  * Validate the given image format return png (3) if invalid
  *
  * @param integer $format
  * @return integer
  */
  protected function validateFormat($format) {
    if (isset($this->_validFormats[$format])) {
      return $format;
    } else {
      $systemFormat = $this->papaya()->options->get('PAPAYA_THUMBS_FILETYPE');
      if (isset($this->_validFormats[$systemFormat])) {
        return $systemFormat;
      } else {
        return 3;
      }
    }
  }

  /**
  * Validate the given image format and return the file extension for it.
  *
  * @param integer $format
  * @return string
  */
  public function getFileExtension($format) {
    $extensions = array(
      IMAGETYPE_GIF => 'gif',
      IMAGETYPE_JPEG => 'jpg',
      IMAGETYPE_PNG => 'png'
    );
    return $extensions[$this->validateFormat($format)];
  }
}

