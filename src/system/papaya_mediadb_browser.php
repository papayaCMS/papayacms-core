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
* Papaya media db browser class
*
* @package Papaya
* @subpackage Media-Database
*/
class papaya_mediadb_browser extends base_mediadb {

  var $cols = 5;
  var $defaultLimit = 20;

  /**
   * @var array
   */
  public $folders = array();

  /**
   * @var array
   */
  public $alternativeFolderNames = array();
  /**
   * @var \Papaya\Template
   */
  public $layout = NULL;

  /**
   * @var base_thumbnail
   */
  public $thumbnail = NULL;

  /**
   * @var array
   */
  public $files = NULL;

  /**
   * @var array
   */
  public $folderTree = NULL;

  /**
   * @var int
   */
  public $folderId = 1;

  /**
  * php 5 constructor
  */
  function __construct($paramName = 'mdb') {
    parent::__construct();
    $this->paramName = $paramName;
  }

  /**
  * initialize languageselector and session parameters
  */
  function initialize() {
    $this->dataDirectory = $this->papaya()->options->get(\Papaya\Configuration\CMS::PATH_MEDIAFILES);
    $this->thumbnailDirectory = $this->papaya()->options->get(\Papaya\Configuration\CMS::PATH_THUMBFILES);
    $this->sessionParamName = 'PAPAYA_SESS_'.$this->paramName;
    $this->initializeParams();
    $this->sessionParams = $this->getSessionValue($this->sessionParamName);

    $this->initializeSessionParam('folder_id', array('offset', 'search'));
    $this->initializeSessionParam('offset');
    $this->initializeSessionParam('limit');
    $this->initializeSessionParam('viewmode');
    $this->initializeSessionParam('imagesonly');
    $this->initializeSessionParam('filter_mode', array('offset'));
    $this->initializeSessionParam('search', array('filter_mode', 'folder_id'));

    if (isset($this->params['cmd'])) {
      switch($this->params['cmd']) {
      case 'clear_search' :
        $this->params['folder_id'] = 0;
        $this->initializeSessionParam('folder_id', array('offset', 'search'));
        break;
      case 'open_folder':
        if (isset($this->params['open_folder_id']) && $this->params['open_folder_id'] > 0) {
          $this->sessionParams['open_folders'][$this->params['open_folder_id']] = 1;
        }
        break;
      case 'close_folder':
        if (isset($this->params['close_folder_id']) && $this->params['close_folder_id'] > 0 &&
            isset($this->sessionParams['open_folders'][$this->params['close_folder_id']])) {
          unset($this->sessionParams['open_folders'][$this->params['close_folder_id']]);
        }
        break;
      case 'close_panel':
        if (isset($this->params['panel'])) {
          $this->sessionParams['panel_state'][$this->params['panel']] = 'closed';
        }
        break;
      case 'open_panel':
        if (isset($this->params['panel'])) {
          $this->sessionParams['panel_state'][$this->params['panel']] = 'open';
        }
        break;
      }
    }
    if (isset($this->params['folder_id'])) {
      $this->folderId = $this->params['folder_id'];
    } else {
      $this->folderId = 0;
    }
    if (!isset($this->sessionParams['open_folders'][0])) {
      $this->sessionParams['open_folders'][0] = 1;
    }
    if (!isset($this->params['offset'])) {
      $this->params['offset'] = 0;
    }
    if (!isset($this->params['viewmode'])) {
      if (isset($this->params['imagesonly']) && $this->params['imagesonly']) {
        $this->params['viewmode'] = 'thumbs';
      } else {
        $this->params['viewmode'] = 'list';
      }
    }
    if (!isset($this->params['limit'])) {
      $this->params['limit'] = $this->defaultLimit;
    }

    $this->setSessionValue($this->sessionParamName, $this->sessionParams);
  }

  /**
   * generates xml for frameset or frames (thumbs, list, preview, ...)
   */
  function getXML() {
    $this->layout->parameters()->set('PAGE_MODE', 'frame');
    if (!isset($this->params['mode'])) {
      $this->params['mode'] = NULL;
    }
    switch ($this->params['mode']) {
    case 'thumbs':
      $this->getThumbs();
      break;
    case 'imgpreview':
      $parser = new papaya_parser;
      if (preg_match('~src="([^"]+)"~', $parser->parse($this->params['img'], NULL), $img)) {
        header('Location: '.$img[1]);
      }
      exit;
      break;
    case 'preview':
      $this->getFilePreview();
      break;
    case 'list':
      $this->mimeObj = base_mediadb_mimetypes::getInstance();
      $this->getSearchPanel();
      $this->getFolderPanel();
      break;
    default:
      $this->layout->parameters()->set('PAGE_MODE', 'frameset');
      $this->getFrameset();
      break;
    }
  }

  /**
  * initialize instance of base_thumbnail ($this->thumbnail)
  */
  function initializeThumbObj() {
    if (!isset($this->thumbnail) || !is_object($this->thumbnail)) {
      $this->thumbnail = new base_thumbnail;
    }
  }

  /**
   * generate thumbnail or listview
   */
  function getThumbs() {
    $result = '';

    $folderId = (isset($this->params['folder_id']))
      ? $this->params['folder_id']
      : 0;

    $this->layout->addScript(
      '<script type="text/javascript" src="script/imgbrowser.js"></script>'
    );
    if (isset($this->params['search'])) {
      $searchParams = $this->params['search'];
      if (isset($this->params['imagesonly']) && $this->params['imagesonly']) {
        $searchParams['ext'] = array('png', 'jpg', 'jpeg', 'gif', 'svg');
      }
      $this->files = $this->findFiles(
        $searchParams,
        $this->params['limit'],
        $this->params['offset']
      );
    } elseif (isset($this->params['imagesonly']) && $this->params['imagesonly']) {
      $this->files = $this->findFiles(
        array(
          'folders' => array($folderId),
          'ext' => array('png', 'jpg', 'jpeg', 'gif', 'svg')
        ),
        $this->params['limit'],
        $this->params['offset']
      );
    } else {
      $this->files = $this->getFiles(
        $folderId, $this->params['limit'], $this->params['offset']
      );
    }
    $imageAbsoluteCount = $this->absCount;
    if (!empty($this->files)) {
      $fileData = $this->getFilesById(
        array_keys($this->files), $this->papaya()->administrationLanguage->id
      );
      if (!empty($fileData)) {
        foreach ($fileData as $fileId => $data) {
          $this->files[$fileId] = $data;
        }
      }
      if (isset($this->params['file']) && isset($fileData[$this->params['file']])) {
        $result .= $this->getFileDescription($fileData[$this->params['file']]);
      }
    }
    $viewMode = 'list';
    if (isset($this->params['viewmode'])) {
      switch ($this->params['viewmode']) {
      case 'tile' :
      case 'list' :
      case 'thumbs' :
        $viewMode = $this->params['viewmode'];
        break;
      }
    }
    $fileCounts = sprintf(
      '%d-%d/%d'.LF,
      $this->params['offset'] + 1,
      $this->params['offset'] + count($this->files),
      $imageAbsoluteCount
    );
    $result .= sprintf(
      '<listview mode="%s" title="%s [%s]">'.LF,
      $viewMode,
      papaya_strings::escapeHTMLChars($this->_gt('Files')),
      papaya_strings::escapeHTMLChars($fileCounts)
    );

    $result .= '<buttons>';
    // back button
    if ($imageAbsoluteCount > $this->params['limit']) {
      $result .= '<left>'.LF;
      $result .= $this->getJavascriptPagingButtons(
        $this->params['offset'], $this->params['limit'], $imageAbsoluteCount
      );
      $result .= '</left>'.LF;
    }

    $result .= '<right>'.LF;
    $limits = array(10, 20, 50, 100);
    foreach ($limits as $limit) {
      $result .= sprintf(
        '<button href="%s" title="%d" %s/>'.LF,
        papaya_strings::escapeHTMLChars(
          sprintf(
            "javascript:parent.changeFrames(%s,0,0,1);",
            \Papaya\Utility\Text\Javascript::quote(
              $this->getLinkParams(
                array(
                  'offset' => 0,
                  'limit' => $limit,
                )
              )
            )
          )
        ),
        papaya_strings::escapeHTMLChars($limit),
        $limit == $this->params['limit'] ? ' down="down"' : ''
      );
    }
    $result .= sprintf(
      '<button href="%s" hint="%s" glyph="%s" %s/>'.LF,
      papaya_strings::escapeHTMLChars(
        sprintf(
          "javascript:parent.changeFrames(%s,0,0,1);",
          \Papaya\Utility\Text\Javascript::quote(
            $this->getLinkParams(
              array(
                'offset' => $this->params['offset'],
                'viewmode' => 'list',
              )
            )
          )
        )
      ),
      papaya_strings::escapeHTMLChars($this->_gt('List')),
      papaya_strings::escapeHTMLChars($this->papaya()->images['categories-view-list']),
      $viewMode == 'list' ? ' down="down"' : ''
    );
    $result .= sprintf(
      '<button href="%s" hint="%s" glyph="%s" %s/>'.LF,
      papaya_strings::escapeHTMLChars(
        sprintf(
          "javascript:parent.changeFrames(%s,0,0,1);",
          \Papaya\Utility\Text\Javascript::quote(
            $this->getLinkParams(
              array(
                'offset' => $this->params['offset'],
                'viewmode' => 'tile',
              )
            )
          )
        )
      ),
      papaya_strings::escapeHTMLChars($this->_gt('Tiles')),
      papaya_strings::escapeHTMLChars($this->papaya()->images['categories-view-tiles']),
      $viewMode == 'tile' ? ' down="down"' : ''
    );
    $result .= sprintf(
      '<button href="%s" hint="%s" glyph="%s" %s/>'.LF,
      papaya_strings::escapeHTMLChars(
        sprintf(
          "javascript:parent.changeFrames(%s,0,0,1);",
          \Papaya\Utility\Text\Javascript::quote(
            $this->getLinkParams(
              array(
                'offset' => $this->params['offset'],
                'viewmode' => 'thumbs',
              )
            )
          )
        )
      ),
      papaya_strings::escapeHTMLChars($this->_gt('Thumbnails')),
      papaya_strings::escapeHTMLChars($this->papaya()->images['categories-view-icons']),
      $viewMode == 'thumbs' ? ' down="down"' : ''
    );
    $result .= '</right>'.LF;
    $result .= '</buttons>';

    if (isset($this->files) && is_array($this->files)) {
      $this->initializeMimeObject();
      $result .= '<items>';
      if ($this->params['viewmode'] == 'list') {
        $result .= $this->getFileListXML();
      } else {
        $result .= $this->getThumbsXML($viewMode == 'thumbs');
      }
      $result .= '</items>';
    }
    $result .= '</listview>';
    $this->layout->add($result);
  }

  /**
  * generate link params instead of complete link for use with JScript
  *
  * @param array $params array of params (key => value)
  * @return string $result query string
  */
  function getLinkParams($params) {
    $result = $this->encodeQueryString($params, $this->paramName);
    $result = str_replace('?', '&', $result);
    return $result;
  }

  /**
   * generate xml for thumbview
   *
   * this method generates a thumbnail view on the files in the folder
   *
   * @param bool $large
   * @return string $result thumbs table XML
   */
  function getThumbsXML($large = FALSE) {
    $this->initializeThumbObj();
    if (isset($this->params['allnew']) && $this->params['allnew']) {
      $this->thumbnail->createAll = TRUE;
    } else {
      $this->thumbnail->createAll = FALSE;
    }
    $result = '';
    foreach ($this->files as $fileId => $file) {
      if (isset($this->params['file']) && $this->params['file'] == $fileId) {
        $selected = ' selected="selected"';
      } else {
        $selected = '';
      }
      $href = sprintf(
        "javascript:parent.changeFrames('%s',0,1,1);",
        $this->getLinkParams(
          array('file' => $fileId, 'offset' => $this->params['offset'])
        )
      );
      $href .= sprintf(
        'selectFile(%s,'.
        ' {id:%s, name:%s, title:%s, description:%s, size:%d, type:%d, width:%d, height:%d});',
        \Papaya\Utility\Text\Javascript::quote($fileId),
        \Papaya\Utility\Text\Javascript::quote($fileId),
        \Papaya\Utility\Text\Javascript::quote($file['file_name']),
        \Papaya\Utility\Text\Javascript::quote($file['file_title']),
        \Papaya\Utility\Text\Javascript::quote(
          \Papaya\Utility\Text\HTML::stripTags($file['file_description'])
        ),
        $file['file_size'],
        $this->mimeToInteger($file['mimetype']),
        empty($file['width']) ? 0 : (int)$file['width'],
        empty($file['height']) ? 0 : (int)$file['height']
      );
      if ($file['mimetype_icon'] != '') {
        $icon = $this->mimeObj->getMimeTypeIcon($file['mimetype_icon']);
      } else {
        $icon = $this->mimeObj->getMimeTypeIcon($this->defaultTypeIcon);
      }
      if (in_array($file['mimetype'], $this->imageMimeTypes)) {
        $thumbnail = new base_thumbnail;
        $thumbFileName = $thumbnail->getThumbnail(
          $file['file_id'],
          $file['current_version_id'],
          ($large) ? 100 :  48,
          ($large) ? 80 :  48
        );
        if ($thumbFileName) {
          $icon = $this->getWebMediaLink(
            $thumbFileName, 'thumb', $file['file_name']
          );
        }
      } elseif ($file['mimetype'] === 'image/svg+xml') {
        $icon = $this->getWebMediaLink(
          $file['file_id'], 'media', $file['file_name']
        );
      }
      $result .= sprintf(
        '<listitem href="%s" title="%s" subtitle="%s" hint="%s" image="%s"%s/>',
        papaya_strings::escapeHTMLChars($href),
        papaya_strings::escapeHTMLChars($file['file_name']),
        date('Y-m-d H:i', $file['file_date']),
        papaya_strings::escapeHTMLChars($file['file_title']),
        $icon,
        $selected
      );
    }
    return $result;
  }

  /**
   * generate file listing xml
   *
   * this methods generates a filemanager like listview on the files in the folder
   *
   * @return string $result file listview XML
   */
  function getFileListXML() {
    $result = '';
    foreach ($this->files as $fileId => $file) {
      if (isset($this->params['file']) && $this->params['file'] == $fileId) {
        $selected = ' selected="selected"';
      } else {
        $selected = '';
      }
      $href = sprintf(
        "javascript:parent.changeFrames('%s',0,1,1);",
        $this->getLinkParams(
          array('file' => $fileId, 'offset' => $this->params['offset'])
        )
      );
      $href .= sprintf(
        'selectFile(%s,'.
        ' {id:%s, name:%s, title:%s, description:%s, size:%d, type:%d, width:%d, height:%d});',
        \Papaya\Utility\Text\Javascript::quote($fileId),
        \Papaya\Utility\Text\Javascript::quote($fileId),
        \Papaya\Utility\Text\Javascript::quote($file['file_name']),
        \Papaya\Utility\Text\Javascript::quote($file['file_title']),
        \Papaya\Utility\Text\Javascript::quote(
          \Papaya\Utility\Text\HTML::stripTags($file['file_description'])
        ),
        $file['file_size'],
        $this->mimeToInteger($file['mimetype']),
        empty($file['width']) ? 0 : (int)$file['width'],
        empty($file['height']) ? 0 : (int)$file['height']
      );
      if ($file['mimetype_icon'] != '') {
        $mimeIcon = $file['mimetype_icon'];
      } else {
        $mimeIcon = $this->defaultTypeIcon;
      }
      $result .= sprintf(
        '<listitem image="icon.mimetypes.%s?size=16" id="file%s" title="%s" href="%s" hint="%s" %s>'.LF,
        papaya_strings::escapeHTMLChars(preg_replace('(\.(gif|png|svg)$)', '', $mimeIcon)),
        papaya_strings::escapeHTMLChars($fileId),
        papaya_strings::escapeHTMLChars($file['file_name']),
        papaya_strings::escapeHTMLChars($href),
        papaya_strings::escapeHTMLChars($file['file_title']),
        $selected
      );
      $result .= sprintf(
        '<subitem>%s</subitem>'.LF,
        date('Y-m-d H:i', $file['file_date'])
      );
      $result .= sprintf(
        '<subitem>%s</subitem>'.LF,
        papaya_strings::escapeHTMLChars($file['mimetype'])
      );
      $result .= '</listitem>'.LF;
    }
    return $result;
  }

  /**
   * generate file preview
   */
  function getFilePreview() {
    $result = '';
    if (isset($this->params['file']) && $this->params['file'] != '') {
      $file = $this->getFile($this->params['file']);
    } elseif (isset($this->params['mid']) && $this->params['mid'] != '') {
      $file = $this->getFile($this->params['mid']);
    }
    if (isset($file) && is_array($file) && count($file) > 0) {
      $imageType = $this->mimeToInteger($file['mimetype']);
      $css = sprintf(
        'position:absolute; left:0px; top:0px; width:%dpx; height:%dpx; border: none;',
        (int)$file['width'],
        (int)$file['height']
      );
      // file is an image
      if (($imageType >= 1 && $imageType <= 4) || $imageType == 13 || $file['mimetype'] === 'image/svg+xml') {
        $tempFileName = $file['file_id'].'v'.$file['current_version_id'];
        if (($imageType >= 1 && $imageType <= 3)  || $file['mimetype'] === 'image/svg+xml') {
          $result .= sprintf(
            '<img style="%s" src="%s" id="preview" onclick="resizeImage();"'.
            ' alt="" width="%d" height="%d"/>',
            papaya_strings::escapeHTMLChars($css),
            papaya_strings::escapeHTMLChars($this->getWebMediaLink($tempFileName, 'thumb')),
            (int)$file['width'],
            (int)$file['height']
          );
          $this->layout->addScript(
            '<script type="text/javascript" src="script/imgbrowser.js"></script>
            <script type="text/javascript">
            <![CDATA[
            var actWidth = 0;
            var actHeight = 0;
            var actLeft = 0;
            var actTop = 0;
            var imgwidth = '.((int)$file['width']).';
            var imgheight = '.((int)$file['height']).';
            resizeImage();
            ]]>
            </script>'
          );
        } elseif ($imageType == IMAGETYPE_SWF || $imageType == IMAGETYPE_SWC) {
          // file is a flash movie
          // embed only for standard compilant browser, the xsl will add ie compatibility
          $result .= sprintf(
            '<object type="application/x-shockwave-flash" '.
            'data="%s" width="100%%" height="300">'.
            '<param name="play" value="false" />'.
            '<param name="loop" value="FALSE" />'.
            '<param name="quality" value="high" />'.
            papaya_strings::escapeHTMLChars($this->_gt('Flash not found.')).
            '</object>',
            papaya_strings::escapeHTMLChars($this->getWebMediaLink($tempFileName))
          );
        }
      } elseif ($this->getFileExtension($file['file_name']) == 'flv' ||
                $file['mimetype_ext'] == 'flv') {
        $result .= $this->getFlvViewer(
          $file['file_id'],
          180,
          150,
          $this->_gt('Flash not found.'),
          NULL,
          array(
            'displayheight' => !empty($this->filePreviewHeight)
              ? $this->filePreviewHeight : '100%'
          )
        );
      } else {
        // file cannot be displayed, showing some file information instead
        $result .= $this->getFileInfos($file);
      }
    } else {
      $this->addMsg(MSG_INFO, $this->_gt('No file selected.'));
    }
    $this->layout->add($result);
  }

  /**
   * generate listview with file information
   *
   * @param array $file
   * @return string $result file information XML
   */
  function getFileInfos($file) {
    $result = '<listview>';
    $result .= '<items>';
    $result .= sprintf(
      '<listitem title="%s"><subitem>%s</subitem></listitem>',
      papaya_strings::escapeHTMLChars($this->_gt('Filename')),
      papaya_strings::escapeHTMLChars($file['file_name'])
    );
    $result .= sprintf(
      '<listitem title="%s"><subitem>%s</subitem></listitem>'.LF,
      papaya_strings::escapeHTMLChars($this->_gt('Filesize')),
      papaya_strings::escapeHTMLChars($this->formatFileSize($file['file_size']))
    );
    $result .= sprintf(
      '<listitem title="%s"><subitem>%s</subitem></listitem>'.LF,
      papaya_strings::escapeHTMLChars($this->_gt('File date')),
      date('Y-m-d H:i:s', $file['file_date'])
    );
    $result .= sprintf(
      '<listitem title="%s"><subitem>%s</subitem></listitem>'.LF,
      papaya_strings::escapeHTMLChars($this->_gt('File type')),
      papaya_strings::escapeHTMLChars($file['mimetype'])
    );
    if (isset($file['width']) && $file['width'] > 0) {
      $result .= sprintf(
        '<listitem title="%s"><subitem>%s Pixel</subitem></listitem>'.LF,
        papaya_strings::escapeHTMLChars($this->_gt('Image width')),
        number_format($file['width'], 0, '.', ' ')
      );
    }
    if (isset($file['height']) && $file['height'] > 0) {
      $result .= sprintf(
        '<listitem title="%s"><subitem>%s Pixel</subitem></listitem>'.LF,
        papaya_strings::escapeHTMLChars($this->_gt('Image height')),
        number_format($file['height'], 0, '.', ' ')
      );
    }
    $result .= '</items>';
    $result .= '</listview>';
    return $result;
  }

  /**
   * Get XML listview containing file title and description
   * @param array $file
   * @return string
   */
  function getFileDescription($file) {
    $result = sprintf(
      '<listview title="%s">',
      papaya_strings::escapeHTMLChars($file['file_name'])
    );
    $result .= '<items>';
    $result .= sprintf(
      '<listitem title="%s"><subitem>%s</subitem></listitem>',
      papaya_strings::escapeHTMLChars($this->_gt('Title')),
      papaya_strings::escapeHTMLChars($file['file_title'])
    );
    $result .= sprintf(
      '<listitem title="%s"><subitem>%s</subitem></listitem>',
      papaya_strings::escapeHTMLChars($this->_gt('Source')),
      papaya_strings::escapeHTMLChars($file['file_source'])
    );
    $result .= sprintf(
      '<listitem title="%s"><subitem>%s</subitem></listitem>',
      papaya_strings::escapeHTMLChars($this->_gt('Description')),
      papaya_strings::escapeHTMLChars(
        preg_replace(
          '([\r\n ]+)',
          ' ',
          \Papaya\Utility\Text\HTML::stripTags($file['file_description'])
        )
      )
    );
    $result .= '</items>';
    $result .= '</listview>';
    return $result;
  }

  /**
   * generate search panel
   *
   * @see papaya_mediadb::getSearchPanel()
   */
  function getSearchPanel() {
    $result = '';
    if (isset($this->sessionParams['panel_state']['search'])
        && $this->sessionParams['panel_state']['search'] == 'open') {
      $resize = sprintf(
        ' minimize="%s"',
        papaya_strings::escapeHTMLChars(
          $this->getLink(
            array(
              'mode' => $this->params['mode'],
              'cmd' => 'close_panel',
              'panel' => 'search'
            )
          )
        )
      );
      $result .= sprintf(
        '<dialog action="javascript:parent.searchFiles(document.getElementById(\'%1$s\'));"'.
        ' method="post" id="%1$s" title="%2$s" %3$s>'.LF,
        'papayaMediaSearchForm',
        papaya_strings::escapeHTMLChars($this->_gt('Search')),
        $resize
      );
      $result .= sprintf(
        '<input type="hidden" name="%s[filter_mode]" value="search" />'.LF,
        papaya_strings::escapeHTMLChars($this->paramName)
      );
      $result .= '<lines class="dialogXSmall">'.LF;
      $result .= sprintf(
        '<line caption="%s" hint="%s">'.LF,
        papaya_strings::escapeHTMLChars($this->_gt('Text')),
        papaya_strings::escapeHTMLChars($this->_gt('Id, Filename, Source, Keywords'))
      );
      $result .= sprintf(
        '<input type="text" name="%s[search][q]" value="%s" class="dialogInput dialogScale"/>',
        papaya_strings::escapeHTMLChars($this->paramName),
        empty($this->params['search']['q'])
          ? '' : papaya_strings::escapeHTMLChars($this->params['search']['q'])
      );
      $result .= '</line>'.LF;

      $result .= sprintf(
        '<line caption="%s">'.LF,
        papaya_strings::escapeHTMLChars($this->_gt('Filetype'))
      );
      $result .= sprintf(
        '<select name="%s[search][mimegroup]" class="dialogSelect dialogScale">'.LF,
        papaya_strings::escapeHTMLChars($this->paramName)
      );
      $mimegroups = $this->mimeObj->getMimeGroups(
        $this->papaya()->administrationLanguage->id, FALSE
      );
      $result .= sprintf(
        '<option value="0">%s</option>'.LF,
        papaya_strings::escapeHTMLChars($this->_gt('All'))
      );
      foreach ($mimegroups as $mimegroupId => $mimegroup) {
        if (isset($this->params['search']['mimegroup']) &&
            $this->params['search']['mimegroup'] == $mimegroupId) {
          $selected = ' selected="selected"';
        } else {
          $selected = '';
        }
        $result .= sprintf(
          '<option value="%s" %s>%s</option>'.LF,
          papaya_strings::escapeHTMLChars($mimegroupId),
          $selected,
          papaya_strings::escapeHTMLChars($mimegroup['mimegroup_title'])
        );
      }
      $result .= '</select>'.LF;
      $result .= '</line>'.LF;

      $result .= sprintf(
        '<line caption="%s &gt;">'.LF,
        papaya_strings::escapeHTMLChars($this->_gt('Date'))
      );
      $result .= sprintf(
        '<input type="text" name="%s[search][younger]" value="%s"'.
        ' class="dialogInputDate dialogScale"/>',
        papaya_strings::escapeHTMLChars($this->paramName),
        empty($this->params['search']['younger'])
          ? '' : papaya_strings::escapeHTMLChars($this->params['search']['younger'])
      );
      $result .= '</line>'.LF;
      $result .= sprintf(
        '<line caption="%s &lt;">'.LF,
        papaya_strings::escapeHTMLChars($this->_gt('Date'))
      );
      $result .= sprintf(
        '<input type="text" name="%s[search][older]" value="%s"'.
        ' class="dialogInputDate dialogScale"/>',
        papaya_strings::escapeHTMLChars($this->paramName),
        empty($this->params['search']['older'])
          ? '' : papaya_strings::escapeHTMLChars($this->params['search']['older'])
      );
      $result .= '</line>'.LF;
      $result .= sprintf(
        '<line caption="%s">'.LF,
        papaya_strings::escapeHTMLChars($this->_gt('Owner'))
      );
      $result .= sprintf(
        '<input type="text" name="%s[search][owner]" value="%s" class="dialogInput dialogScale"/>',
        papaya_strings::escapeHTMLChars($this->paramName),
        empty($this->params['search']['owner'])
          ? '' : papaya_strings::escapeHTMLChars($this->params['search']['owner'])
      );
      $result .= '</line>'.LF;
      $result .= '</lines>'.LF;
      $result .= sprintf(
        '<dlgbutton align="left" value="" caption="%s" type="button"'.
        ' onclick="parent.changeFrames(\'&amp;%s[cmd]=clear_search\');" />',
        papaya_strings::escapeHTMLChars($this->_gt('Clear')),
        papaya_strings::escapeHTMLChars($this->paramName)
      );
      $result .= sprintf(
        '<dlgbutton value="%s"/>'.LF,
        papaya_strings::escapeHTMLChars($this->_gt('Find'))
      );
      $result .= '</dialog>'.LF;
    } else {
      $resize = sprintf(
        ' maximize="%s"',
        papaya_strings::escapeHTMLChars(
          $this->getLink(
            array(
              'mode' => $this->params['mode'],
              'cmd' => 'open_panel',
              'panel' => 'search'
            )
          )
        )
      );
      $result .= sprintf(
        '<listview title="%s" %s />'.LF,
        papaya_strings::escapeHTMLChars($this->_gt('Search')),
        $resize
      );
    }
    $this->layout->add($result);
  }

  /**
   * generate folder panel
   *
   * @see papaya_mediadb::getFolderPanel()
   */
  function getFolderPanel() {
    $result = '';
    $result .= sprintf(
      '<listview title="%s">'.LF,
      papaya_strings::escapeHTMLChars($this->_gt('Folders'))
    );
    $result .= '<items>'.LF;
    if (!isset($this->params['folder_id']) || $this->params['folder_id'] == 0) {
      $selected = ' selected="selected"';
    } else {
      $selected = '';
    }
    $href = sprintf(
      "javascript:parent.changeFrames('%s',1,0,1)",
      $this->getLinkParams(array('folder_id' => 0, 'filter_mode' => 'folder'))
    );
    $nodeImage = ($selected == '') ?
      $this->papaya()->images['items-folder'] : $this->papaya()->images['status-folder-open'];
    $result .= sprintf(
      '<listitem image="%s" title="%s" href="%s" %s>'.LF,
      papaya_strings::escapeHTMLChars($nodeImage),
      papaya_strings::escapeHTMLChars($this->_gt('Desktop')),
      papaya_strings::escapeHTMLChars($href),
      $selected
    );
    $result .= '</listitem>'.LF;
    $result .= $this->getFoldersListXML();
    $result .= '</items>'.LF;
    $result .= '</listview>'.LF;
    $this->layout->add($result);
  }

  /**
  * load folders for folder tree
  */
  function loadFolders() {
    $this->folders = $this->getFolders($this->papaya()->administrationLanguage->id);
    $this->countSubFolders($this->folders);
    foreach ($this->folders as $folderId => $folder) {
      $this->folderTree[$folder['parent_id']][$folderId] = $folderId;
      if (!isset($folder['folder_name'])) {
        $foldersWithoutName[] = $folderId;
      }
    }
    $defaultLanguage = $this->papaya()->options->get(\Papaya\Configuration\CMS::CONTENT_LANGUAGE);
    if ($defaultLanguage != $this->papaya()->administrationLanguage->id &&
        isset($foldersWithoutName) && is_array($foldersWithoutName) &&
        count($foldersWithoutName) > 0) {
      $this->alternativeFolderNames = $this->getFolders(
        $defaultLanguage,
        $foldersWithoutName
      );
    }
  }

  /**
   * generate the folders list xml
   *
   * @uses papaya_mediadb_browser::getFoldersSubTreeXML()
   * @see papaya_mediadb::getFoldersSubTreeXML()
   * @return string $result folder listview XML
   */
  function getFoldersListXML() {
    $result = '';
    $this->loadFolders();
    if (isset($this->folders) && is_array($this->folders) && count($this->folders) > 0) {
      $result .= $this->getFoldersSubTreeXML(0, 1);
    }
    return $result;
  }

  /**
   * generate a subtree of the folders list xml
   *
   * @access private
   * @uses papaya_mediadb_browser::getFolderEntryXML()
   * @see papaya_mediadb::getFoldersSubTreeXML()
   * @param int $parentId
   * @param int $indent
   * @return string $result subtree XML
   */
  function getFoldersSubTreeXML($parentId, $indent) {
    $result = '';
    if (isset($this->folderTree[$parentId]) &&
        is_array($this->folderTree[$parentId]) &&
        count($this->folderTree[$parentId]) > 0 &&
        (isset($this->sessionParams['open_folders'][$parentId]) || $parentId == 0)) {
      foreach ($this->folderTree[$parentId] as $folderId) {
        $result .= $this->getFolderEntryXML($folderId, $indent);
      }
    }
    return $result;
  }

  /**
   * generate a single foldertree entry
   *
   * @access private
   * @see papaya_mediadb::getFolderEntryXML()
   * @param int $folderId
   * @param int$indent
   * @return string $result listitem XML
   */
  function getFolderEntryXML($folderId, $indent) {
    $result = '';
    if (isset($this->folders[$folderId]) && is_array($this->folders[$folderId])) {
      $folder = $this->folders[$folderId];
      if (isset($folder['COUNT']) && $folder['COUNT'] > 0) {
        if (isset($this->sessionParams['open_folders'][$folderId])) {
          $node = 'open';
          $nodeHref = sprintf(
            ' nhref="%s"',
            papaya_strings::escapeHTMLChars(
              $this->getLink(
                array('mode' => 'list', 'cmd' => 'close_folder', 'close_folder_id' => $folderId)
              )
            )
          );
        } else {
          $node = 'close';
          $nodeHref = sprintf(
            ' nhref="%s"',
            papaya_strings::escapeHTMLChars(
              $this->getLink(
                array('mode' => 'list', 'cmd' => 'open_folder', 'open_folder_id' => $folderId)
              )
            )
          );
        }
      } else {
        $node = 'empty';
        $nodeHref = '';
      }
      if (isset($this->params['folder_id']) && $this->params['folder_id'] == $folderId) {
        $selected = ' selected="selected"';
      } else {
        $selected = '';
      }
      if (isset($folder['folder_name'])) {
        $folderName = $folder['folder_name'];
      } elseif (isset($this->alternativeFolderNames[$folderId])) {
        $folderName = '['.$this->alternativeFolderNames[$folderId]['folder_name'].']';
      } else {
        $folderName = $this->_gt('New Folder');
      }

      $href = sprintf(
        "javascript:parent.changeFrames('%s',1,0,1)",
        $this->getLinkParams(array('folder_id' => $folderId, 'filter_mode' => 'folder'))
      );
      $nodeImage = ($selected == '')?
      $this->papaya()->images['items-folder']:
      $this->papaya()->images['status-folder-open'];
      $result .= sprintf(
        '<listitem node="%s" %s image="%s" indent="%d" title="%s" href="%s" %s>'.LF,
        papaya_strings::escapeHTMLChars($node),
        $nodeHref,
        papaya_strings::escapeHTMLChars($nodeImage),
        (int)$indent,
        papaya_strings::escapeHTMLChars($folderName),
        papaya_strings::escapeHTMLChars($href),
        $selected
      );
      $result .= '</listitem>'.LF;
      $result .= $this->getFoldersSubTreeXML($folderId, $indent + 1);
    }
    return $result;
  }

  /**
   * generate the image browser frameset
   */
  function getFrameset() {
    $result = '<script type="text/javascript" src="script/imgbrowser.js"></script>
<script type="text/javascript">
<![CDATA[
var linkList = "'.$this->getLink(array('mode' => 'list')).'";
var linkPreview = "'.$this->getLink(array('mode' => 'preview')).'";
var linkThumbs = "'.$this->getLink(array('mode' => 'thumbs')).'";

]]>
</script>
';
    $result .= '<frameset cols="300,*" frameborder="yes" framespacing="5" border="5">'.LF;
    $result .= '<frameset rows="*,200" frameborder="yes" framespacing="5" border="5">'.LF;
    $result .= sprintf(
      '<frame src="%s" scrolling="auto" name="flist" />'.LF,
      papaya_strings::escapeHTMLChars($this->getLink(array('mode' => 'list')))
    );
    $result .= sprintf(
      '<frame src="%s" scrolling="no" name="fpreview" />'.LF,
      papaya_strings::escapeHTMLChars($this->getLink(array('mode' => 'preview')))
    );
    $result .= '</frameset>'.LF;
    $result .= sprintf(
      '<frame src="%s" scrolling="auto" name="fthumbs" />'.LF,
      papaya_strings::escapeHTMLChars($this->getLink(array('mode' => 'thumbs')))
    );
    $result .= '</frameset>'.LF;
    $this->layout->add($result);
  }

  /**
  * Get javascript based paging navigation
  * @param integer $offset
  * @param integer $step
  * @param integer $max
  * @param integer $groupCount
  * @return string
  */
  function getJavascriptPagingButtons($offset, $step, $max, $groupCount = 9) {
    $result = '';
    $baseParams = array(
      'limit' => $step
    );
    $paramName = 'offset';
    $step = ($step > 0) ? $step : 10;
    $pageCount = ceil($max / $step);
    $currentPage = ceil($offset / $step);
    if ($currentPage > 0) {
      $i = ($currentPage - 1) * $step;
      $params = $baseParams;
      $params[$paramName] = $i;
      $result .= sprintf(
        '<button hint="%s" glyph="%s" href="%s"/>'.LF,
        papaya_strings::escapeHTMLChars($this->_gt('Previous page')),
        papaya_strings::escapeHTMLChars($this->papaya()->images['actions-go-previous']),
        papaya_strings::escapeHTMLChars(
          $this->getJavascriptButtonHref($params)
        )
      );
    } else {
      $result .= sprintf(
        '<button glyph="%s" />'.LF,
        papaya_strings::escapeHTMLChars($this->papaya()->images['status-go-previous-disabled'])
      );
    }
    if ($pageCount > $groupCount) {
      $plusMinus = floor($groupCount / 2);
      $pageMin = ceil(($offset - ($step * ($plusMinus))) / $step);
      $pageMax = ceil(($offset + ($step * ($plusMinus))) / $step);
      if ($pageMin < 0) {
        $pageMin = 0;
      }
      if ($pageMin == 0) {
        $pageMax = $groupCount;
      } elseif ($pageMax >= $pageCount) {
        $pageMax = $pageCount;
        $pageMin = $pageCount - $groupCount;
      }
      for ($x = $pageMin; $x < $pageMax; $x++) {
        $i = $x * $step;
        $down = ($i == $offset)? ' down="down"' : '';
        $params = $baseParams;
        $params[$paramName] = $i;
        $result .= sprintf(
          '<button title="%s" href="%s"%s/>'.LF,
          papaya_strings::escapeHTMLChars($x + 1),
          papaya_strings::escapeHTMLChars(
            $this->getJavascriptButtonHref($params)
          ),
          $down
        );
      }
    } else {
      for ($i = 0, $x = 1; $i < $max; $i += $step, $x++) {
        $down = ($i == $offset)? ' down="down"' : '';
        $params = $baseParams;
        $params[$paramName] = $i;
        $result .= sprintf(
          '<button title="%s" href="%s"%s/>'.LF,
          papaya_strings::escapeHTMLChars($x),
          papaya_strings::escapeHTMLChars(
            $this->getJavascriptButtonHref($params)
          ),
          $down
        );
      }
    }
    if ($currentPage < $pageCount - 1) {
      $i = ($currentPage + 1) * $step;
      $params = $baseParams;
      $params[$paramName] = $i;
      $result .= sprintf(
        '<button hint="%s" glyph="%s" href="%s"/>'.LF,
        papaya_strings::escapeHTMLChars($this->_gt('Next page')),
        papaya_strings::escapeHTMLChars($this->papaya()->images['actions-go-next']),
        papaya_strings::escapeHTMLChars(
          $this->getJavascriptButtonHref($params)
        )
      );
    } else {
      $result .= sprintf(
        '<button glyph="%s" />'.LF,
        papaya_strings::escapeHTMLChars($this->papaya()->images['status-go-next-disabled'])
      );
    }
    return $result;
  }

  /**
  * Get javascript link to change frames in image browser
  * @param array $params
  * @return string
  */
  function getJavascriptButtonHref($params = array()) {
    return sprintf(
      "javascript:parent.changeFrames(%s,0,0,1);",
      \Papaya\Utility\Text\Javascript::quote(
        $this->getLinkParams($params)
      )
    );
  }
}

