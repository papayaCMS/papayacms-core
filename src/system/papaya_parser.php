<?php
/**
* Parser for special tags like papaya:media, papaya:link and papaya:popup
*
* @copyright 2002-2009 by papaya Software GmbH - All rights reserved.
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
* @version $Id: papaya_parser.php 39855 2014-06-05 13:57:21Z weinert $
*/

/**
* Parser for special tags like papaya:media, papaya:link and papaya:popup
*
* @package Papaya
* @subpackage Core
*/
class papaya_parser extends base_db {

  const ELEMENTS_SPAN = 0;
  const ELEMENTS_FIGURE = 1;
  const ELEMENTS_FIGURE_MANDATORY = 2;

  /**
  * Table media files
  * @var string $tableMediaFiles
  */
  var $tableMediaFiles = PAPAYA_DB_TBL_MEDIADB_FILES;
  /**
  * Table media files translations
  * @var string $tableMediaFilesTrans
  */
  var $tableMediaFilesTrans = PAPAYA_DB_TBL_MEDIADB_FILES_TRANS;

  /**
  * Table media database  mimetypes
  * @var string $tableMimeTypes
  */
  var $tableMimeTypes = PAPAYA_DB_TBL_MEDIADB_MIMETYPES;

  /**
  * Table topic translations
  * @var string $tableTopicsTrans
  */
  var $tableTopicsTrans = PAPAYA_DB_TBL_TOPICS_TRANS;

  /**
  * Table topics
  * @var string $tableTopics
  */
  var $tableTopics = PAPAYA_DB_TBL_TOPICS;

  /**
  * Table link types
  * @var string $tableLinkTypes
  */
  var $tableLinkTypes = PAPAYA_DB_TBL_LINKTYPES;

  /**
  * Dynamic Image Configurations Table
  * @var string $tableImageConfs
  */
  var $tableImageConfs = PAPAYA_DB_TBL_IMAGES;

  /**
  * Modules/Plugins
  * @var string $tableModules
  */
  var $tableModules = PAPAYA_DB_TBL_MODULES;

  /**
  * papaya tag pattern
  * @var string $papayaTagPattern
  */
  var $papayaTagPattern = '(<(?:papaya|ndim):([a-z]\w+)\s*([^>]*)(?:/>|(?:>(?:</\\1:\\2>))))i';

  /**
  * papaya parameters pattern
  * @var string $papayaParamsPattern
  */
  var $papayaParamsPattern =
    '((?:^|\s)([a-z]\w+)(?:=)(?:(?:\'([^\']+)\')|(?:"([^"]*)")|([^\s,]+)))iu';

  /**
  * Tags
  * @var array $tags
  */
  var $tags;

  /**
  * Data
  * @var string $data
  */
  var $data;

  /**
  * Linked Files
  * @var array $files
  */
  var $files = NULL;

  /**
  * Linked page topics
  * @var array $topics
  */
  var $topics = NULL;

  /**
  * decoded link type data for topics
  * @var array $topicLinkTypes
  */
  var $topicLinkTypes = NULL;

  /**
  * Linked Dynamic Images (Attributes)
  * @var array $dynImages
  */
  var $dynImages = NULL;

  /**
  * Linked Addons
  * @var array $addOns
  */
  var $addOns = NULL;
  /**
  * Addon tag data
  * @var array $addOnTags
  */
  var $addOnTags = NULL;

  /**
  * output mode for page links
  * @var string
  * @access private
  */
  var $_linkOutputMode;

  /**
  * storage service instance
  * @var PapayaMediaStorageService
  */
  var $storageService = NULL;

  /**
  * if set to true links should be absolute
  */
  var $linkModeAbsolute = FALSE;

  /**
   * @var int
   */
  private $lngId;

  /**
   * @var base_mediadb
   */
  private $mediaDB;

  /**
  * Parse function
  *
  * @param string $data
  * @param integer $lngId language id
  * @access public
  * @return string
  */
  function parse($data, $lngId) {
    PapayaUtilConstraints::assertString($data);
    $this->data = $data;
    if (defined('PAPAYA_ADMIN_PAGE') && PAPAYA_ADMIN_PAGE) {
      $this->lngId = $this->papaya()->administrationLanguage->id;
    } else {
      $this->lngId = $lngId;
    }
    if (preg_match_all($this->papayaTagPattern, $data, $matches, PREG_SET_ORDER)) {
      foreach ($matches as $match) {
        $tag = $match[0];
        if (!isset($this->tags[$tag])) {
          $this->tags[$tag] = $this->parseTag($match[1], $match[2]);
        }
      }
      if (isset($this->tags) && is_array($this->tags)) {
        $this->initializeAddOns($lngId);
        $this->getTopicInfos();
        $this->getMediaInfos();
        $this->getImageConfInfos();
        foreach ($this->tags as $tagString => $tag) {
          $str = $this->createTag($tag);
          if ($str) {
            $this->tags[$tagString]['parsed'] = $str;
          } else {
            $this->tags[$tagString]['parsed'] =
              '<!-- '.str_replace(array('<!--', '-->'), '', $tag['str']).' -->';
          }
        }
      }
    } else {
      unset($this->tags);
    }
    $this->replaceTags();
    return $this->data;
  }

  /**
  * Get additional data from parser addons
  *
  * @access public
  * @return string
  */
  function getParsedData() {
    $result = '';
    if (isset($this->addOns) &&
        is_array($this->addOns) && count($this->addOns) > 0) {
      foreach ($this->addOns as $addOn) {
        if (is_object($addOn) && method_exists($addOn, 'getParsedData')) {
          $result .= $addOn->getParsedData();
        }
      }
    }
    return $result;
  }

  /**
  * Replace tags
  *
  * @access public
  */
  function replaceTags() {
    if (isset($this->tags) && is_array($this->tags)) {
      foreach ($this->tags as $tag => $parsed) {
        $this->data = str_replace($tag, $parsed['parsed'], $this->data);
      }
    }
  }

  /**
  * Parse tag
  *
  * @param string $tagName
  * @param string $tag
  * @access public
  * @return array
  */
  function parseTag($tagName, $tag) {
    $result = FALSE;
    if (preg_match_all($this->papayaParamsPattern, $tag, $matches, PREG_SET_ORDER)) {
      $params = array();
      foreach ($matches as $match) {
        $paramName = $match[1];
        $paramValue =
          (empty($match[2]) ? '' : $match[2]).
          (empty($match[3]) ? '' : $match[3]).
          (empty($match[4]) ? '' : $match[4]);
        switch ($paramName) {
        case 'src':
          $paramValue = substr(strtolower($paramValue), 0, 32);
          $this->files[$paramValue] = FALSE;
          break;
        case 'topic':
          $this->topics[(int)$paramValue] = FALSE;
          break;
        case 'image':
          $this->dynImages[strtolower($paramValue)] = FALSE;
          break;
        case 'addon':
          $this->addOns[strtolower($paramValue)] = FALSE;
          break;
        case 'href':
          if (PapayaFilterFactory::isInteger($paramValue, TRUE)) {
            $this->topics[(int)$paramValue] = FALSE;
          }
          break;
        }
        $params[$paramName] =
          papaya_strings::unicodeEntitiesToUTF8($paramValue);
      }
      $result = array('name' => $tagName, 'params' => $params, 'str' => $tag);
      if (isset($params['addon'])) {
        $this->addOnTags[$params['addon']][] = $result;
      }
    }
    return $result;
  }

  /**
  * Load addon data and initialize addons
  *
  * @param integer $languageId optional, default NULL
  * @access public
  */
  function initializeAddOns($languageId = NULL) {
    if (isset($this->addOns) && is_array($this->addOns)) {
      $filter = ' WHERE '.$this->databaseGetSQLCondition('module_guid', array_keys($this->addOns));
      $sql = "SELECT m.module_guid, m.module_path, m.module_file, m.module_class
                FROM %s m
                $filter";
      $addOns = array();
      if ($res = $this->databaseQueryFmt($sql, array($this->tableModules))) {
        while ($data = $res->fetchRow(DB_FETCHMODE_ASSOC)) {
          $addOns[$data['module_guid']] = $data;
        }
      }
      foreach ($addOns as $data) {
        $moduleObj = $this->papaya()->plugins->get($data['module_guid']);
        if (isset($moduleObj) && is_object($moduleObj)) {
          if (method_exists($moduleObj, 'setLanguageId')) {
            $moduleObj->setLanguageId($languageId);
          }
          $this->addOns[$data['module_guid']] = $moduleObj;
          if (isset($this->addOnTags[$data['module_guid']])) {
            $moduleObj->initialize($this);
            $moduleObj->compileData($this->addOnTags[$data['module_guid']]);
          }
        }
      }
    }
  }

  /**
  * Fetch informaiton to given topic id out of database
  *
  * @access public
  */
  function getTopicInfos() {
    if (isset($this->topics) && is_array($this->topics)) {
      $this->papaya()->pageReferences->preload($this->lngId, array_keys($this->topics));
      $filter = $this->databaseGetSQLCondition('tt.topic_id', array_keys($this->topics));
      $sql = "SELECT tt.topic_id, tt.topic_title, lt.linktype_id,
                     lt.linktype_is_popup, lt.linktype_name,
                     lt.linktype_target, lt.linktype_popup_config
                FROM %s tt, %s t, %s lt
               WHERE $filter
                 AND tt.lng_id = %d
                 AND t.topic_id = tt.topic_id
                 AND lt.linktype_id = t.linktype_id";
      $params = array(
        $this->tableTopicsTrans,
        $this->tableTopics,
        $this->tableLinkTypes,
        $this->lngId
      );
      if ($res = $this->databaseQueryFmt($sql, $params)) {
        while ($row = $res->fetchRow(DB_FETCHMODE_ASSOC)) {
          $this->topics[$row['topic_id']] = $row;
          if (!isset($this->topicLinkTypes[$row['linktype_id']]) && $row['linktype_is_popup']) {
            $this->topicLinkTypes[$row['linktype_id']] = array();
            $xmlTree = PapayaXmlDocument::createFromXml($row['linktype_popup_config']);
            if (isset($xmlTree) && isset($xmlTree->documentElement) &&
                $xmlTree->documentElement->hasChildNodes()) {
              for ($idx = 0; $idx < $xmlTree->documentElement->childNodes->length; $idx++) {
                $xmlNode = $xmlTree->documentElement->childNodes->item($idx);
                if ($xmlNode instanceof DOMElement &&
                    $xmlNode->hasAttribute('name')) {
                  $name = $xmlNode->getAttribute('name');
                  $value = $xmlNode->nodeValue;
                  $this->topicLinkTypes[$row['linktype_id']][$name] = $value;
                }
              }
            }
          }
        }
      }
    }
  }

  /**
  * Fetch information to given media object out of database
  *
  * @access public
  */
  function getMediaInfos() {
    if (isset($this->files) && is_array($this->files)) {
      $fileIds = array_keys($this->files);
      $this->mediaDB = base_mediadb::getInstance();
      $files = $this->mediaDB->getFilesById($fileIds, $this->lngId);
      foreach ($files as $fileId => $file) {
        $this->files[$fileId] = array(
          'file_id' => $fileId,
          'version_id' => $file['current_version_id'],
          'width' => $file['width'],
          'height' => $file['height'],
          'type' => $this->mediaDB->mimeToInteger($file['mimetype']),
          'size' => $file['file_size'],
          'file' => $file['file_name'],
          'title' => $file['file_title'],
          'description' => $file['file_description'],
          'source' => $file['file_source'],
          'source_url' => $file['file_source_url'],
          'extension' => $file['mimetype_ext'],
          'mimetype' => $file['mimetype'],
        );
      }
    }
  }

  /**
  * create storage
  * @return PapayaMediaStorageService
  */
  function getStorageService() {
    if (!isset($this->storageService)) {
      $configuration = $this->papaya()->options;
      $this->storageService = PapayaMediaStorage::getService(
        $configuration->get('PAPAYA_MEDIA_STORAGE_SERVICE', ''),
        $configuration
      );
    }
    return $this->storageService;
  }

  /**
  * Get image config infos
  *
  * @access public
  */
  function getImageConfInfos() {
    if (isset($this->dynImages) &&
        is_array($this->dynImages) && count($this->dynImages) > 0) {
      $moduleData = array();
      $filter = str_replace(
        '%',
        '%%',
        $this->databaseGetSQLCondition(
          'i.image_ident',
          array_keys($this->dynImages)
        )
      );
      $sql = "SELECT i.image_ident, i.image_format,
                     m.module_guid, m.module_path, m.module_file,
                     m.module_class, m.module_title
                FROM %s i, %s m
               WHERE m.module_guid = i.module_guid
                 AND $filter";
      $params = array($this->tableImageConfs, $this->tableModules);
      if ($res = $this->databaseQueryFmt($sql, $params)) {
        $imageData = array();
        $moduleGuids = array();
        while ($row = $res->fetchRow(DB_FETCHMODE_ASSOC)) {
          $imageData[$row['image_ident']] = $row;
          $moduleGuids[$row['module_guid']] = TRUE;
        }
        foreach ($imageData as $data) {
          $moduleObj = $this->papaya()->plugins->get($data['module_guid']);
          if (isset($moduleObj) && is_object($moduleObj)) {
            $data['ATTRS'] = $moduleObj->attributeFields;
            $moduleData[$data['module_guid']] = $moduleObj->attributeFields;
            $this->dynImages[$data['image_ident']] = $data;
          }
        }
      }
    }
  }

  /**
  * Function creates links by given tag
  *
  * @param array $tag data of papaya-tag for example
  *  <papaya:media.../ <papaya:link... or <papaya:popup...>
  * @access public
  * @return mixed Link or FALSE if unknown tag is given to function
  */
  function createTag($tag) {
    switch($tag['name']) {
    case 'media':
      return $this->createMediaTag($tag['params']);
    case 'file':
      return $this->createFileTag($tag['params']);
    case 'image':
      return $this->createDynImageTag($tag['params']);
    case 'link':
      return $this->createLink($tag['params']);
    case 'popup':
      return $this->createPopUpLink($tag['params']);
    case 'addon':
      if (!empty($tag['params']['addon'])) {
        $guid = (string)$tag['params']['addon'];
        if (isset($this->addOns[$guid]) &&
            is_object($this->addOns[$guid]) &&
            method_exists($this->addOns[$guid], 'createTag')) {
          if ($tagStr = $this->addOns[$guid]->createTag($tag)) {
            return $tagStr;
          }
        }
      }
      break;
    }
    return FALSE;
  }

  /**
  * Create media tag
  *
  * @param array $params Parameters
  * @access public
  * @return mixed Link or FALSE
  *
  */
  function createMediaTag($params) {
    $data = empty($this->files[$params['src']]) ? '' : $this->files[$params['src']];
    if (isset($params) && is_array($params) && isset($data) && is_array($data)) {
      if (isset($params['download']) && $params['download'] == 'yes') {
        return $this->createDownloadTag($params, $data);
      }
      switch($data['type']) {
      case 1:
      case 2:
      case 3:
        return $this->createImageTag($params, $data);
      case 4:
      case 13:
        return $this->createFlashTag($params, $data);
      default:
        $ext = strtolower(strrchr($data['file'], '.'));
        switch ($ext) {
        case '.flv':
          return $this->createFLVTag($params, $data);
        case '.pdf':
          return $this->createPDFTag($params, $data);
        }
        return $this->createDownloadTag($params, $data);
      }
    }
    return FALSE;
  }


  /**
  * Creates a download link for the given file.
  *
  * @param $params array Parameters containing all information of the file
  * @access public
  * @return mixed link or FALSE
  */
  function createFileTag($params) {
    $data = empty($this->files[$params['src']]) ? '' : $this->files[$params['src']];
    if (isset($params) && is_array($params) && isset($data) && is_array($data)) {
      return $this->createDownloadTag($params, $data);
    }
    return FALSE;
  }

  /**
  * Create dynamic image tag
  *
  * @param array $params parameters
  * @access public
  * @return mixed string image tag or boolean FALSE
  */
  function createDynImageTag($params) {
    $paramName = 'img';
    if (isset($this->dynImages[$params['image']]) &&
        ($data = $this->dynImages[$params['image']]) &&
        isset($params) && is_array($params) && isset($data) &&
        isset($data['ATTRS']) && is_array($data['ATTRS'])) {
      $paramStr = '';
      foreach ($data['ATTRS'] as $attrName => $attrDef) {
        if (isset($params[$attrName])) {
          $paramStr .= '&'.$paramName.'['.urlencode($attrName).']='.urlencode($params[$attrName]);
        } else {
          $paramStr .= '&'.$paramName.'['.urlencode($attrName).']='.urlencode($attrDef[6]);
        }
      }
      $src = $this->escapeForFileName($params['image']).'.image.'.
        $this->getDynamicFileExtension($data['image_format']);
      $borderData = $this->calcBorders($params, $data);
      $style = '';
      if (isset($borderData['top']) && $borderData['top'] > 0) {
        $style .= ' margin-top: '.(int)$borderData['top'].'px;';
      }
      if (isset($borderData['right']) && $borderData['right'] > 0) {
        $style .= ' margin-right: '.(int)$borderData['right'].'px;';
      }
      if (isset($borderData['bottom']) && $borderData['bottom'] > 0) {
        $style .= ' margin-bottom: '.(int)$borderData['bottom'].'px;';
      }
      if (isset($borderData['left']) && $borderData['left'] > 0) {
        $style .= ' margin-left: '.(int)$borderData['left'].'px;';
      }
      if (isset($params['align'])) {
        switch ($params['align'])  {
        case 'none':
          break;
        case 'left':
          $style .= ' float: left;';
          break;
        case 'right':
          $style .= ' float: right;';
          break;
        case 'center':
          $style = ' display: block;  text-align: center;'.
            ' margin-left: auto;  margin-right: auto;';
          if (isset($style['top']) && $style['top'] > 0) {
            $style .= ' margin-top: '.(int)$borderData['top'].'px;';
          }
          if (isset($style['bottom']) && $style['bottom'] > 0) {
            $style .= ' margin-bottom: '.(int)$borderData['bottom'].'px;';
          }
          break;
        }
      }
      if (!empty($params['alt'])) {
        $alt = $params['alt'];
      } else {
        $alt = '';
      }
      if (empty($style)) {
        return sprintf(
          '<img src="%s?%s" alt="%s" class="%s" />',
          papaya_strings::escapeHTMLChars($src),
          papaya_strings::escapeHTMLChars(substr($paramStr, 1)),
          papaya_strings::escapeHTMLChars($alt),
          papaya_strings::escapeHTMLChars(
            $this->getClassAttributeString(
              $params, 'PAPAYA_MEDIA_CSSCLASS_DYNIMAGE', 'papayaDynamicImage'
            )
          )
        );
      } else {
        return sprintf(
          '<img src="%s?%s" alt="%s" class="%s" style="%s" />',
          papaya_strings::escapeHTMLChars($src),
          papaya_strings::escapeHTMLChars(substr($paramStr, 1)),
          papaya_strings::escapeHTMLChars($alt),
          papaya_strings::escapeHTMLChars(
            $this->getClassAttributeString(
              $params, 'PAPAYA_MEDIA_CSSCLASS_DYNIMAGE', 'papayaDynamicImage'
            )
          ),
          substr($style, 1)
        );
      }
    }
    return FALSE;
  }

  /**
  * Get file extension depending on given format
  *
  * @param integer $format
  * @return string
  */
  private function getDynamicFileExtension($format) {
    $extensions = array(
      IMAGETYPE_GIF => 'gif',
      IMAGETYPE_JPEG => 'jpg',
      IMAGETYPE_PNG => 'png'
    );
    if (isset($extensions[$format])) {
      return $extensions[$format];
    } else {
      $systemFormat = $this->papaya()->options->get('PAPAYA_THUMBS_FILETYPE');
      if (isset($extensions[$systemFormat])) {
        return $extensions[$systemFormat];
      } else {
        return 'png';
      }
    }
  }

  /**
  * Create image tag
  *
  * @param array $params Link parameters from the papaya tag
  * @param array $data corresponding data to this image from the database
  * @access public
  * @return string
  */
  function createImageTag($params, $data) {
    if (isset($params['version_id']) && $params['version_id'] > 0) {
      $data['version_id'] = $params['version_id'];
    }
    if (empty($data['width'])) {
      $orgWidth = 0;
    } else {
      $orgWidth = (int)$data['width'];
    }
    if (empty($data['height'])) {
      $orgHeight = 0;
    } else {
      $orgHeight = (int)$data['height'];
    }
    if (empty($params['width'])) {
      $params['width'] = 0;
    }
    if (empty($params['height'])) {
      $params['height'] = 0;
    }
    if (($params['width'] > 0 || $params['height'] > 0) &&
        $orgWidth > 0 && $orgHeight > 0) {
      switch ($this->papaya()->options['PAPAYA_THUMBS_FILETYPE']) {
      case 1 :
        $mimetype = 'image/gif';
        break;
      case 2 :
        $mimetype = 'image/jpeg';
        break;
      case 3 :
        $mimetype = 'image/png';
        break;
      default:
        $mimetype = '';
        break;
      }
      $data = $this->createThumbnail($params, $data);
      $imageData = array(
        'src' => $this->getWebMediaLink($data['filename'], 'thumb', $data['title']),
        'width' => (int)$data['width'],
        'height' => (int)$data['height'],
        'storage_group' => $data['storage_group'],
        'storage_id' => $data['storage_id'],
        'mimetype' => $mimetype,
      );
    } else {
      $imageData = array(
        'src' => $this->getWebMediaLink(
          $data['file_id'].'v'.$data['version_id'],
          'media',
          $data['title'],
          $data['extension']
        ),
        'width' => $orgWidth,
        'height' => $orgHeight,
        'storage_group' => 'files',
        'storage_id' => $data['file_id'].'v'.$data['version_id'],
        'mimetype' => $data['mimetype'],
      );
    }
    $storage = $this->getStorageService();
    if (
      $storage->isPublic(
        $imageData['storage_group'], $imageData['storage_id'], $imageData['mimetype']
      )
    ) {
      $imageData['src'] = $storage->getUrl(
        $imageData['storage_group'],
        $imageData['storage_id'],
        $imageData['mimetype']
      );
    }
    if (
      isset($params['topic']) &&
      isset($this->topics[$params['topic']]) &&
      is_array($this->topics[$params['topic']])
    ) {
      $topicData = $this->topics[$params['topic']];
      $hrefData = array(
        'href' => $this->getWebLink((int)$params['topic']),
        'target' => ((isset($params['target'])) ? $params['target'] : '_self'),
        'title' => $topicData['topic_title']
      );
      if (isset($topicData['linktype_is_popup']) &&
          isset($this->topicLinkTypes[$topicData['linktype_id']])) {
        $cfgData = $this->topicLinkTypes[$topicData['linktype_id']];
        $hrefData['data-popup'] = self::getDataPopupAttribute(
          $cfgData['width'],
          $cfgData['height'],
          $cfgData['scrollbars'],
          $cfgData['resizable'],
          $cfgData['toolbar'],
          $cfgData['top'],
          $cfgData['left'],
          $cfgData['menubar'],
          $cfgData['location'],
          $cfgData['status']
        );
      }
    } elseif (isset($params['href']) && PapayaFilterFactory::isInteger($params['href'], TRUE) &&
        isset($this->topics[$params['href']]) &&
              is_array($this->topics[$params['href']])) {
      $hrefData = array(
        'href' => $this->getWebLink((int)$params['href']),
        'target' => isset($params['target']) ? $params['target'] : '_self',
        'title' => isset($params['title']) ? $params['title'] : ''
      );
    } elseif (isset($params['href']) &&
              PapayaFilterFactory::isUrl($params['href'], TRUE) && $this->isSessionInUri()) {
      $hrefData = array(
        'href' => $this->getWebLink(0, '', 'page', array('exit' => $params['href'])),
        'target' => isset($params['target']) ? $params['target'] : '_self',
        'title' => isset($params['title']) ? $params['title'] : ''
      );
    } elseif (isset($params['href']) && 0 === strpos($params['href'], 'mediaimage:')) {
      $params['linkedImage'] = substr($params["href"], 11);
      $linkedImageData = $this->files[$params['linkedImage']];
      $hrefData = array(
        'href' => papaya_strings::escapeHTMLChars(
          $this->getWebMediaLink(
            substr($params['href'], 11),
            'media',
            $data['title'],
            $data['extension']
          )
        ),
        'target' => isset($params['target']) ? $params['target'] : '_self',
        'title' => isset($params['title']) ? $params['title'] : ''
      );
      $hrefData['data-popup'] = self::getDataPopupAttribute(
        $linkedImageData['width'],
        $linkedImageData['height'],
        FALSE,
        FALSE,
        FALSE
      );
    } elseif (isset($params['href']) && 0 === strpos($params['href'], 'mediafile:')) {
        $hrefData = array(
        'href' => papaya_strings::escapeHTMLChars(
          $this->getWebMediaLink(
            substr($params['href'], 10),
            'download',
            $data['title'],
            $data['extension']
          )
        ),
        'target' => isset($params['target']) ? $params['target'] : '_self',
        'title' => isset($params['title']) ? $params['title'] : ''
      );
    } elseif (isset($params['href']) && trim($params['href']) != '') {
      $hrefData = array(
        'href' => $params['href'],
        'target' => isset($params['target']) ? $params['target'] : '_self',
        'title' => isset($params['title']) ? $params['title'] : ''
      );
    } else {
      $hrefData = FALSE;
    }
    if (!empty($params['alt'])) {
      $altText = $params['alt'];
    } elseif (!empty($data['description']) &&
              defined('PAPAYA_MEDIA_ALTTEXT_MODE') &&
              PAPAYA_MEDIA_ALTTEXT_MODE) {
      $altText = preg_replace(
        '([\r\n ]+)',
        ' ',
        PapayaUtilStringHtml::stripTags($data['description'])
      );
    } else {
      $altText = $hrefData['title'];
    }
    $borderData = $this->calcBorders($params, $data);
    $style = 'width: '.(int)$data['width'].'px;';
    if (isset($borderData['top']) && $borderData['top'] > 0) {
      $style .= ' margin-top: '.(int)$borderData['top'].'px;';
    }
    if (isset($borderData['right']) && $borderData['right'] > 0) {
      $style .= ' margin-right: '.(int)$borderData['right'].'px;';
    }
    if (isset($borderData['bottom']) && $borderData['bottom'] > 0) {
      $style .= ' margin-bottom: '.(int)$borderData['bottom'].'px;';
    }
    if (isset($borderData['left']) && $borderData['left'] > 0) {
      $style .= ' margin-left: '.(int)$borderData['left'].'px;';
    }
    if (isset($params['align'])) {
      switch ($params['align'])  {
      case 'none':
        break;
      case 'left':
        $style .= ' float: left;';
        break;
      case 'right':
        $style .= ' float: right;';
        break;
      case 'center':
        $style = ' display: block;  text-align: center;'.
          ' margin-left: auto;  margin-right: auto;';
        if (isset($style['top']) && $style['top'] > 0) {
          $style .= ' margin-top: '.(int)$borderData['top'].'px;';
        }
        if (isset($style['bottom']) && $style['bottom'] > 0) {
          $style .= ' margin-bottom: '.(int)$borderData['bottom'].'px;';
        }
        break;
      }
    }
    $subtitleHtml = $this->getImageSubtitle($params, $data);
    if ($subtitleHtml) {
      $result = sprintf(
        '<img src="%s" class="%s" style="width: %dpx; height: %dpx;" alt="%s" />',
        papaya_strings::escapeHTMLChars($imageData['src']),
        papaya_strings::escapeHTMLChars(
          $this->getClassAttributeString(
            $params, 'PAPAYA_MEDIA_CSSCLASS_IMAGE', 'papayaImage'
          )
        ),
        (int)$imageData['width'],
        (int)$imageData['height'],
        papaya_strings::escapeHTMLChars($altText)
      );
    } else {
      $result = sprintf(
        '<img src="%s" class="%s" style="%s height: %dpx;" alt="%s" />',
        papaya_strings::escapeHTMLChars($imageData['src']),
        papaya_strings::escapeHTMLChars(
          $this->getClassAttributeString(
            $params, 'PAPAYA_MEDIA_CSSCLASS_IMAGE', 'papayaImage'
          )
        ),
        papaya_strings::escapeHTMLChars($style),
        (int)$imageData['height'],
        papaya_strings::escapeHTMLChars($altText)
      );
    }
    if ($hrefData && isset($hrefData['data-popup'])) {
      $result = sprintf(
        '<a href="%s" target="%s" data-popup="%s" title="%s">%s</a>',
        papaya_strings::escapeHTMLChars($hrefData['href']),
        papaya_strings::escapeHTMLChars($hrefData['target']),
        papaya_strings::escapeHTMLChars($hrefData['data-popup']),
        papaya_strings::escapeHTMLChars($hrefData['title']),
        $result
      );
    } elseif ($hrefData) {
      $result = sprintf(
        '<a href="%s" target="%s" title="%s">%s</a>',
        papaya_strings::escapeHTMLChars($hrefData['href']),
        papaya_strings::escapeHTMLChars($hrefData['target']),
        papaya_strings::escapeHTMLChars($hrefData['title']),
        $result
      );
    } elseif (isset($params['popup']) && trim($params['popup']) != '' &&
              $orgWidth > 0 && $orgHeight > 0) {
      $result = sprintf(
        '<a href="%s" target="%s" data-popup="%s">%s</a>',
        papaya_strings::escapeHTMLChars(
          $this->getWebMediaLink(
            $params['src'], 'popup', $data['title'], $data['extension']
          )
        ),
        papaya_strings::escapeHTMLChars($params['popup']),
        papaya_strings::escapeHTMLChars(
          self::getDataPopupAttribute($orgWidth, $orgHeight, FALSE, FALSE, FALSE)
        ),
        $result
      );
    }
    $containerMode = $this->papaya()->options->get('PAPAYA_MEDIA_ELEMENTS_IMAGE', 0);
    if ($subtitleHtml) {
      $subtitleHtml = '<!--googleoff: all-->'.$subtitleHtml.' <!--googleon: all-->';
      switch ($containerMode) {
      case self::ELEMENTS_FIGURE :
      case self::ELEMENTS_FIGURE_MANDATORY :
        $pattern =
          '<figure class="%s" style="%s">%s<figcaption class="%s">%s</figcaption></figure>';
        break;
      case self::ELEMENTS_SPAN :
      default :
        $pattern =
          '<span class="%s" style="%s">%s<span class="%s">%s</span></span>';
        break;
      }
    } elseif ($containerMode == self::ELEMENTS_FIGURE_MANDATORY) {
      $pattern = '<figure class="%s" style="%s">%s</figure>';
    } else {
      $pattern = FALSE;
    }
    if ($pattern) {
      $result = sprintf(
        $pattern,
        papaya_strings::escapeHTMLChars(
          $this->getClassAttributeString(
            $params, 'PAPAYA_MEDIA_CSSCLASS_IMAGE', 'papayaImage'
          )
        ),
        $style,
        $result,
        papaya_strings::escapeHTMLChars(
          $this->getClassAttributeString(
            $params, 'PAPAYA_MEDIA_CSSCLASS_SUBTITLE', 'papayaSubTitle'
          )
        ),
        $subtitleHtml
      );
    }
    return $result;
  }

  /**
  * Compile the image subtitle html from parameters and image data
  *
  * @param array $params
  * @param array $data
  * @return string
  */
  function getImageSubTitle($params, $data) {
    $subTitleMode = defined('PAPAYA_MEDIA_CUTLINE_MODE') ? PAPAYA_MEDIA_CUTLINE_MODE : 0;
    switch ($subTitleMode) {
    case 2 :
    case 1 :
      if (!empty($params['subtitle'])) {
        $title = $params['subtitle'];
      } elseif (!empty($data['title'])) {
        $title = $data['title'];
      } else {
        $title = '';
      }
      if (!empty($params['source'])) {
        $source = $params['source'];
      } elseif (!empty($data['source'])) {
        $source = $data['source'];
      } else {
        $source = '';
      }
      if (!empty($params['source_url'])) {
        $sourceUrl = $params['source_url'];
      } elseif (!empty($data['source_url'])) {
        $sourceUrl = $data['source_url'];
      } else {
        $sourceUrl = '';
      }
      $subTitle = papaya_strings::escapeHTMLChars($title);
      if (!empty($source) &&
          (!preg_match('(\\(.*\\)\s*$)', $title) || $subTitleMode == 1)) {
        if (empty($data['source_url'])) {
          $subTitle .= ' ('.papaya_strings::escapeHTMLChars($source).')';
        } else {
          $subTitle .= sprintf(
            ' (<a href="%s" class="%s" target="%s">%s</a>)',
            papaya_strings::escapeHTMLChars($sourceUrl),
            papaya_strings::escapeHTMLChars(
              defined('PAPAYA_MEDIA_CUTLINE_LINK_CLASS')
                ? PAPAYA_MEDIA_CUTLINE_LINK_CLASS : 'source'
            ),
            papaya_strings::escapeHTMLChars(
              defined('PAPAYA_MEDIA_CUTLINE_LINK_TARGET')
                ? PAPAYA_MEDIA_CUTLINE_LINK_TARGET : '_self'
            ),
            papaya_strings::escapeHTMLChars($source)
          );
        }
      }
      break;
    case 0 :
    default :
      $subTitle = empty($params['subtitle'])
        ? '' : papaya_strings::escapeHTMLChars($params['subtitle']);
      break;
    }
    return $subTitle;
  }

  /**
  * Calculate borders
  *
  * @param array $params Link parameters
  * @access public
  * @return array ('left' => ..., 'right' => ..., 'top' => ..., 'bottom' => ..., 'subtitle' => ...)
  */
  function calcBorders($params) {
    if (isset($params['align']) && $params['align'] == 'left') {
      $left = 0;
      $right = 4;
    } elseif (isset($params['align']) && $params['align'] == 'right') {
      $left = 4;
      $right = 0;
    } else {
      $left = 0;
      $right = 0;
    }
    $left = (isset($params['lspace'])) ? $params['lspace'] : $left;
    $right = (isset($params['rspace'])) ? $params['rspace'] : $right;
    $top = (isset($params['tspace'])) ? $params['tspace'] : 0;
    $bottom = (isset($params['bspace'])) ? $params['bspace'] : 0;
    return array(
      'left' => $left,
      'right' => $right,
      'top' => $top,
      'bottom' => $bottom
    );
  }

  /**
  * Create a thumbnail in given size
  *
  * @param array $params parameters
  * @param array $data Parser given data like hight width of object
  * @access public
  * @return array Tag in XHTML which can be used in all HTML-files
  */
  function createThumbnail($params, $data) {
    $result = $data;
    $result['filename'] = $params['src'];
    if (isset($params['version_id']) && $params['version_id'] > 0) {
      $data['version_id'] = $params['version_id'];
    }
    $result['filename'] .= 'v'.$data['version_id'];
    $result['storage_group'] = 'files';
    $result['storage_id'] = $result['filename'];
    if ($data['extension'] != '') {
      $result['filename'] .= '.'.$data['extension'];
    }
    $tWidth = (isset($params['width']) && $params['width'] > 0) ?
      (int)$params['width'] : (int)$data['width'];
    $tHeight = (isset($params['height']) && $params['height'] > 0) ?
      (int)$params['height'] : (int)$data['height'];

    if (
        isset($params['resize']) && $params['resize'] == 'abs' &&
        (
          !isset($params['width']) || $params['width'] <= 0 ||
          !isset($params['height']) || $params['height'] <= 0
        )
      ) {
      $thumbMode = 'max';
    } elseif (isset($params['resize']) &&
              in_array($params['resize'], array('abs', 'max', 'min', 'mincrop'))) {
      $thumbMode = $params['resize'];
    } else {
      $thumbMode = 'max';
    }

    if (($tWidth != $data['width']) || ($tHeight != $data['height'])) {
      $thumbnail = new base_thumbnail;
      $thumbParams = array();
      if (!empty($params['cropwidth'])) {
        $thumbParams['cropwidth'] = $params['cropwidth'];
      }
      if (!empty($params['cropheight'])) {
        $thumbParams['cropheight'] = $params['cropheight'];
      }
      if (!empty($params['xoff'])) {
        $thumbParams['x_offset'] = $params['xoff'];
      }
      if (!empty($params['yoff'])) {
        $thumbParams['y_offset'] = $params['yoff'];
      }
      if (isset($data['file_id']) && $data['file_id'] != '') {
        $thumbFile = $thumbnail->getThumbnail(
          $data['file_id'],
          $data['version_id'],
          $tWidth,
          $tHeight,
          $thumbMode,
          $thumbParams
        );
        list($tWidth, $tHeight) = $thumbnail->lastThumbSize;
        $result['filename'] = $thumbFile;
        $result['storage_group'] = 'thumbs';
        $result['storage_id'] = $thumbFile;
      }
      $result['width'] = $tWidth;
      $result['height'] = $tHeight;
    }
    return $result;
  }

  /**
  * Create tag for flash object
  *
  * @param array $params Used parameters
  * @param array $data Parser given data like hight width of object
  * @access public
  * @return string Tag in XHTML which can be used in all HTML-files
  */
  function createFlashTag($params, $data) {
    $width = (isset($params['width']) ? $params['width'] : $data['width']);
    $height = (isset($params['height']) ? $params['height'] : $data['height']);
    $play = (isset($params['play']) ? $params['play'] : 'true');
    $loop = (isset($params['loop']) ? $params['loop'] : 'true');
    $align = (isset($params['align']) ? ' align="'.$params['align'].'"' : '');
    $movie = $this->getWebMediaLink(
      $params['src'], 'media', $data['title'], $data['extension']
    );
    $bgColor = (isset($params['bgcolor']) ? $params['bgcolor'] : '');
    $quality = (isset($params['quality']) ? $params['quality'] : '');

    $flvPlayer = new papaya_swfobject();
    $flvPlayer->setSWFParam('play', $play);
    $flvPlayer->setSWFParam('loop', $loop);
    $flvPlayer->setSWFParam('quality', $quality);
    $flvPlayer->setSWFParam('bgcolor', $bgColor);
    if (!empty($data['version_id'])) {
      $flvPlayer->setPlayerRevision($data['version_id']);
    }
    return $flvPlayer->getXHTML($movie, $width, $height, $align);
  }

  /**
  * Create tag for flash object
  *
  * @param array $params Used parameters
  * @param array $data Parser given data like hight width of object
  * @access public
  * @return string Tag in XHTML which can be used in all HTML-files
  */
  function createFLVTag($params, $data) {
    $width = (isset($params['width']) ? $params['width'] : $data['width']);
    $height = (isset($params['height']) ? $params['height'] : $data['height']);
    $movie = $this->getWebMediaLink(
      $params['src'], 'media', $data['title'], $data['extension']
    );
    $swf = new papaya_swfobject();
    if (substr($movie, -4) != '.flv') {
      $movie .= '.flv';
    }
    $swf->setFlashVars(
      array(
        'file' => '../../../'.$movie
      )
    );
    $swf->setSWFParam('bgcolor', '#000000');
    return $swf->getXHTML(
      PAPAYA_PATH_WEB.'papaya-themes/'.PAPAYA_LAYOUT_THEME.'/papaya/flvplayer.swf',
      $width,
      $height
    );
  }

  /**
  * Create tag for pdf-object
  *
  * @param array $params Used parameters
  * @param array $data Parser given data like hight width of object
  * @access public
  * @return string Tag in XHTML which can be used in all HTML-files
  */
  function createPDFTag($params, $data) {
    $width = (isset($params['width']) ? $params['width'] : '100%');
    $height = (isset($params['height']) ? $params['height'] : '400');
    $align = (isset($params['align']) ? $params['align'] : 'left');
    $file = $this->getWebMediaLink(
      $params['src'], 'media', $data['title'], $data['extension']
    );
    $tag = sprintf(
      '<object classid="clsid:CA8A9780-280D-11CF-A24D-444553540000" width="%1$s"'.
      ' height="%2$s" align="%3$s">
        <param name="src" value="%4$s" />
        <!-- <iframe src="%4$s" width="%1$s" height="%2$s" align="%3$s">
          <noframes> //-->
          <embed src="%4$s" width="%1$s" height="%2$s" align="%3$s" href="%4$s">
            <noembed>
              %5$s<br />
              <a href="http://www.adobe.de/products/acrobat/readstep2.html"'.
              ' target="_blank">www.adobe.de/products/acrobat/readstep2.html</a>
            </noembed>
          </embed>
          <!-- </noframes>
        </iframe> //-->
      </object>',
      papaya_strings::escapeHTMLChars($width),
      papaya_strings::escapeHTMLChars($height),
      papaya_strings::escapeHTMLChars($align),
      papaya_strings::escapeHTMLChars($file),
      $params['text']
    );
    return $tag;
  }

  /**
  * Create download link tag for example parameter "download=yes" is given.
  *
  * @param array $params Used parameters
  * @param array $data Parser given data like hight width of object
  * @access public
  * @return string Tag in XHTML which can be used in all HTML-files
  */
  function createDownloadTag($params, $data) {
    if ($data['size'] > 1048576) {
      $size = number_format(($data['size'] / 1048576), 2, ',', '.').' MB';
    } elseif ($data['size'] > 1024) {
      $size = number_format(($data['size'] / 1024), 0, ',', '.').' kB';
    } else {
      $size = number_format($data['size'], 0, ',', '.').' Bytes';
    }
    $result = sprintf(
      '<a href="%s" title="%s" %s>%s</a>',
      papaya_strings::escapeHTMLChars(
        $this->getWebMediaLink(
          $params['src'],
          'download',
          $data['title'],
          $data['extension']
        )
      ),
      papaya_strings::escapeHTMLChars(
        isset($params['hint']) ? $params['hint'] : ''
      ),
      isset($params['target'])
        ? 'target="'.papaya_strings::escapeHTMLChars($params['target']).'"' : '',
      papaya_strings::escapeHTMLChars(
        isset($params['text']) ? $params['text'] : $data['title'].' ('.$size.')'
      )
    );
    return $result;
  }

  /**
   * Create Link
   *
   * @param array $params Used parameters
   * @access public
   * @return string Tag in XHTML which can be used in all HTML-files
   */
  function createLink($params) {
    $additionalAttributes = '';
    if (isset($params) && is_array($params)) {
      if (!empty($params['title'])) {
        $additionalAttributes .= sprintf(
          ' title="%s"',
          papaya_strings::escapeHTMLChars($params['title'])
        );
      }
      if (!empty($params['target'])) {
        $additionalAttributes .= sprintf(
          ' target="%s"',
          papaya_strings::escapeHTMLChars($params['target'])
        );
      }
    }
    if (isset($params['topic']) && $params['topic'] > 0) {
      $data = $this->topics[$params['topic']];
      if (isset($params) && is_array($params)) {
        if (isset($params['alttext'])) {
          if (isset($data) && is_array($data)) {
            switch ($params['alttext']) {
            case 'on':
              $caption = (isset($params['text'])) ?
                $params['text'] : $data['topic_title'];
              break;
            default:
              $caption = $data['topic_title'];
            }
            $result = sprintf(
              '<a href="%s" class="%s"%s>%s</a>',
              papaya_strings::escapeHTMLChars(
                $this->getWebLink(
                  (int)$params['topic'],
                  NULL,
                  $this->_linkOutputMode
                )
              ),
              papaya_strings::escapeHTMLChars(
                $this->getClassAttributeString(
                  $params, 'PAPAYA_MEDIA_CSSCLASS_LINK', 'papayaLink'
                )
              ),
              $additionalAttributes,
              papaya_strings::escapeHTMLChars($caption)
            );
          } else {
            switch ($params['alttext']) {
            case 'off':
              $caption = '';
              break;
            default :
              $caption = (isset($params['text'])) ? $params['text'] : '';
            }
            $result = $caption;
          }
        } else {
          if (isset($data) && is_array($data)) {
            if (!isset($params['altmode'])) {
              $params['altmode'] = '';
            }
            switch ($params['altmode']) {
            case 'yes':
              $caption = (isset($params['text'])) ?
                $params['text'] : $data['topic_title'];
              break;
            case 'no':
              $caption = $data['topic_title'];
              break;
            default:
              $caption = $data['topic_title'];
            }
            $result = sprintf(
              '<a href="%s" class="%s"%s>%s</a>',
              papaya_strings::escapeHTMLChars(
                $this->getWebLink(
                  (int)$params['topic'],
                  NULL,
                  $this->_linkOutputMode
                )
              ),
              papaya_strings::escapeHTMLChars(
                $this->getClassAttributeString(
                  $params, 'PAPAYA_MEDIA_CSSCLASS_LINK', 'papayaLink'
                )
              ),
              $additionalAttributes,
              papaya_strings::escapeHTMLChars($caption)
            );
          } else {
            switch ($params['altmode']) {
            case 'yes':
              $caption = (isset($params['text'])) ? $params['text'] : '';
              break;
            case 'no':
              $caption = '';
              break;
            default :
              $caption = (isset($params['text'])) ? $params['text'] : '';
            }
            $result = $caption;
          }
        }
        return $result;
      }
    } elseif (isset($params['href']) && trim($params['href']) != '') {
      if (preg_match('~^mailto:~i', $params['href'])) {
        return sprintf(
          '<a href="%s" class="%s"%s>%s</a>',
          papaya_strings::escapeHTMLChars($params['href']),
          papaya_strings::escapeHTMLChars(
            $this->getClassAttributeString(
              $params, 'PAPAYA_MEDIA_CSSCLASS_MAILTO', 'papayaMailToLink'
            )
          ),
          $additionalAttributes,
          papaya_strings::escapeHTMLChars(
            isset($params['text']) ? $params['text'] : $params['href']
          )
        );
      } else {
        if (isset($params['class']) && $params['class'] == 'papayaReferenceLink') {
          return sprintf(
            '<a href="%s" class="%s"%s>%s</a>',
            papaya_strings::escapeHTMLChars($params['href']),
            papaya_strings::escapeHTMLChars(
              $this->getClassAttributeString(
                $params, 'PAPAYA_MEDIA_CSSCLASS_LINK', 'papayaReferenceLink'
              )
            ),
            $additionalAttributes,
            papaya_strings::escapeHTMLChars(
              isset($params['text']) ? $params['text'] : $params['href']
            )
          );
        }
        if (preg_match('~^\d+$~', $params['href'])) {
          $link = $this->getWebLink($params['href'], NULL, $this->_linkOutputMode);
        } elseif (preg_match('(^\w+://)', $params['href']) && $this->isSessionInUri()) {
          $link = $this->getWebLink(
            NULL, NULL, NULL, array('exit' => $params['href'])
          );
        } else {
          $link = $params['href'];
        }
        if (isset($params['nofollow']) && $params['nofollow'] == 'yes') {
          $additionalAttributes .= ' rel="nofollow"';
        }
        return sprintf(
          '<a href="%s" class="%s"%s>%s</a>',
          papaya_strings::escapeHTMLChars($link),
          papaya_strings::escapeHTMLChars(
            $this->getClassAttributeString($params, 'PAPAYA_MEDIA_CSSCLASS_LINK', 'papayaLink')
          ),
          $additionalAttributes,
          papaya_strings::escapeHTMLChars(
            isset($params['text']) ? $params['text'] : $params['href']
          )
        );
      }
    }
    return '';
  }

  /**
  * Checks if the session id in the path or query string. In this case an exit-link is needed.
  *
  * @return TRUE
  */
  function isSessionInUri() {
    return $this->papaya()->session->id()->existsIn(
      PapayaSessionId::SOURCE_PARAMETER
    );
  }

  /**
  * Get class name for generated tag from parameters, options or use default
  * @param array $params
  * @param string $optionName
  * @param string $defaultClass
  * @return string
  */
  function getClassAttributeString($params, $optionName, $defaultClass) {
    if (!empty($params['class'])) {
      return $params['class'];
    } elseif (defined($optionName) && constant($optionName) != '') {
      return constant($optionName);
    } else {
      return $defaultClass;
    }
  }

  /**
  * Create Link for popup window
  *
  * @param array $params Used parameters
  * @access public
  * @return string Tag in XHTML which can be used in all HTML-files
  */
  function createPopUpLink($params) {
    $href = NULL;
    if (isset($params['topic']) &&
        isset($this->topics[$params['topic']]) &&
        is_array($this->topics[$params['topic']])) {
      $data = $this->topics[$params['topic']];
      $href = $this->getWebLink(
        (int)$data['topic_id'],
        '',
        'page',
        NULL,
        NULL,
        $data['topic_title']
      );
    } else {
      $href = $this->getAbsoluteURL($params['href']);
    }
    if (isset($href)) {
      $width = (isset($params['width']) && $params['width'] > 50) ? (int)$params['width'] : 300;
      $height = (isset($params['height']) && $params['height'] > 50) ? (int)$params['height'] : 400;
      $name = (isset($params['name']) && trim($params['name']) != '')
        ? (int)$params['name'] : 'papaya_popup';
      $scrollbars = (isset($params['scrollbars']) && $params['scrollbars'] == 'yes') ? 'yes' : 'no';
      $resizeable = (isset($params['resizeable']) && $params['resizeable'] == 'yes') ? 'yes' : 'no';
      $toolbar = (isset($params['toolbar']) && $params['toolbar'] == 'yes') ? 'yes' : 'no';
      if (isset($data) && is_array($data)) {
        switch ($params['altmode']) {
        case 'yes':
          $caption = (isset($params['text']))
            ? $params['text']
            : $data['topic_title'];
          break;
        case 'no':
          $caption = $data['topic_title'];
          break;
        default:
          $caption = $data['topic_title'];
        }
      } else {
        $caption = (isset($params['text'])) ? $params['text'] : $href;
      }
      if (isset($params['menubar']) || isset($params['location'])
          || isset($params['status']) || isset($params['top'])
          || isset($params['left'])) {
        $top = (isset($params['top']) && $params['top']) ? $params['top'] : '';
        $left = (isset($params['left']) && $params['left']) ? $params['left'] : '';
        $menubar = (isset($params['menubar']) && $params['menubar']) ? 'yes' : 'no';
        $location = (isset($params['location']) && $params['location']) ? 'yes' : 'no';
        $status = (isset($params['status']) && $params['status']) ? 'yes' : 'no';
        return sprintf(
          '<a href="%s" target="%s" data-popup="%s">%s</a>',
          papaya_strings::escapeHTMLChars($href),
          papaya_strings::escapeHTMLChars($name),
          papaya_strings::escapeHTMLChars(
            self::getDataPopupAttribute(
              $width,
              $height,
              $scrollbars,
              $resizeable,
              $toolbar,
              $top,
              $left,
              $menubar,
              $location,
              $status
            )
          ),
          papaya_strings::escapeHTMLChars($caption)
        );
      } else {
        return sprintf(
          '<a href="%s" target="%s" data-popup="%s">%s</a>',
          papaya_strings::escapeHTMLChars($href),
          papaya_strings::escapeHTMLChars($name),
          papaya_strings::escapeHTMLChars(
            self::getDataPopupAttribute(
              $width,
              $height,
              $scrollbars,
              $resizeable,
              $toolbar
            )
          ),
          papaya_strings::escapeHTMLChars($caption)
        );
      }
    } else {
      switch ($params['altmode']) {
      case 'yes':
        $caption = (isset($params['text'])) ? $params['text'] : '';
        break;
      case 'no':
        $caption = '';
        break;
      default:
        $caption = (isset($params['text'])) ? $params['text'] : '';
      }
      return $caption;
    }
  }

  /**
   * Create data-popup attribute data. This is an json encoded array. A jQuery plugin looks for
   * the data attribute an initializes it.
   *
   * @param string $width
   * @param string $height
   * @param string $scrollbars
   * @param string $resizable
   * @param string $toolbar
   * @param string|NULL $top
   * @param string|NULL $left
   * @param boolean $menubar
   * @param boolean $location
   * @param boolean $status
   * @return string
   */
  public static function getDataPopupAttribute(
    $width,
    $height,
    $scrollbars,
    $resizable,
    $toolbar,
    $top = NULL,
    $left = NULL,
    $menubar = FALSE,
    $location = FALSE,
    $status = FALSE
  ) {
    $disabledStates = array(FALSE, '', 'no');
    $result = array(
      'width' => $width,
      'height' => $height
    );
    if (!empty($top)) {
      $result['top'] = $top;
    }
    if (!empty($left)) {
      $result['left'] = $left;
    }
    $result['scollBars'] = $scrollbars;
    $result['resizable'] = (int)in_array($resizable, $disabledStates);
    $result['toolBar'] = (int)in_array($toolbar, $disabledStates);
    $result['menuBar'] = (int)in_array($menubar, $disabledStates);
    $result['locationBar'] = (int)in_array($location, $disabledStates);
    $result['statusBar'] = (int)in_array($status, $disabledStates);
    return json_encode($result);
  }

  /**
  * Check if media file exists
  *
  * @param integer $fileId
  * @access public
  * @return boolean
  */
  function mediaFileExists($fileId) {
    return (isset($this->files[$fileId]) && is_array($this->files[$fileId]));
  }

  /**
  * Set link output mode for page links
  * @param string|NULL $outputMode
  * @return void
  */
  function setLinkOutputMode($outputMode) {
    $this->_linkOutputMode = $outputMode;
  }

  /**
   * Overload getWebLink to check a object property, if it is TRUE generate absolute links.
   *
   * @param mixed $pageId optional, page id, default value NULL
   * @param integer $lng optional, language id, default value NULL
   * @param string $mode optional, default value 'page'
   * @param mixed $params optional, default value NULL
   * @param mixed $paramName optional, default value NULL
   * @param string $text optional, default value empty string
   * @param integer $categId optional, default value NULL
   * @return string
   * @access public
   * return string
   */
  function getWebLink(
    $pageId = NULL,
    $lng = NULL,
    $mode = NULL,
    $params = NULL,
    $paramName = NULL,
    $text = '',
    $categId = NULL
  ) {
    $link = parent::getWebLink(
      $pageId, $lng, $mode, $params, $paramName, $text, $categId
    );
    if ($this->linkModeAbsolute) {
      $link = $this->getAbsoluteURL($link, NULL, FALSE);
    }
    return $link;
  }
}