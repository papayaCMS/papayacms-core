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

namespace Papaya\UI\Reference;

/**
 * Papaya Interface Thumbnail Reference (Hyperlink Reference)
 *
 * @package Papaya-Library
 * @subpackage UI
 */
class Thumbnail extends \Papaya\UI\Reference {
  /**
   * Page identification data
   *
   * @var array
   */
  protected $_pageData = [
    'title' => 'index',
    'media_id' => NULL,
    'version' => 0,
    'thumbnail_mode' => 'max',
    'thumbnail_size' => '',
    'thumbnail_params' => '',
    'extension' => '',
    'preview' => FALSE
  ];

  /**
   * Static create function to allow fluent calls.
   *
   * @param \Papaya\URL $url
   * @return \Papaya\UI\Reference
   */
  public static function create(\Papaya\URL $url = NULL) {
    return new self($url);
  }

  /**
   * @see \Papaya\UI\Reference::get()
   * @param bool $forPublic
   * @return null|string
   */
  public function get($forPublic = FALSE) {
    if (!empty($this->_pageData['media_id'])) {
      $result = $this->url()->getHostURL().$this->_basePath;
      $result .= $this->_pageData['title'];
      if (!empty($this->_pageData['thumbnail_size'])) {
        $result .= '.thumb';
      } else {
        $result .= '.media';
      }
      if ((!$forPublic) && $this->_pageData['preview']) {
        $result .= '.preview';
      }
      $result .= '.'.$this->_pageData['media_id'];
      if ($this->_pageData['version'] > 0) {
        $result .= 'v'.$this->_pageData['version'];
      }
      if (!empty($this->_pageData['thumbnail_size'])) {
        $result .= '_'.$this->_pageData['thumbnail_mode'];
        $result .= '_'.$this->_pageData['thumbnail_size'];
        if (!empty($this->_pageData['thumbnail_params'])) {
          $result .= '_'.$this->_pageData['thumbnail_params'];
        }
      }
      if (!empty($this->_pageData['extension'])) {
        $result .= '.'.$this->_pageData['extension'];
      }
      return $result;
    }
    return;
  }

  /**
   * @see \Papaya\UI\Reference::load()
   * @param \Papaya\Request $request
   * @return \Papaya\UI\Reference|$this
   */
  public function load(\Papaya\Request $request) {
    parent::load($request);
    $this->setPreview(
      $request->getParameter('preview', FALSE, NULL, \Papaya\Request::SOURCE_PATH)
    );
    return $this;
  }

  /**
   * Set media id
   *
   * @param string $mediaId
   * @return self
   */
  public function setMediaId($mediaId) {
    $this->prepare();
    if (!empty($mediaId) && \preg_match('(^[a-fA-F\d]{32}$)D', $mediaId)) {
      $this->_pageData['media_id'] = \strtolower($mediaId);
    }
    return $this;
  }

  /**
   * Set media version
   *
   * @param int $version
   * @return self
   */
  public function setMediaVersion($version) {
    $this->prepare();
    if ($version > 0) {
      $this->_pageData['version'] = (int)$version;
    }
    return $this;
  }

  /**
   * Set file title (normalized string)
   *
   * @param string $title
   * @return self
   */
  public function setTitle($title) {
    $this->prepare();
    if (\preg_match('(^[a-zA-Z\d_-]+$)D', $title)) {
      $this->_pageData['title'] = (string)$title;
    }
    return $this;
  }

  /**
   * Set thumbnail resize mode
   *
   * @param string $mode
   * @return self
   */
  public function setThumbnailMode($mode) {
    $this->prepare();
    if (\preg_match('(^[a-zA-Z]+$)D', $mode)) {
      $this->_pageData['thumbnail_mode'] = (string)$mode;
    }
    return $this;
  }

  /**
   * Set thumbnail size
   *
   * @param string $size
   * @return self
   */
  public function setThumbnailSize($size) {
    $this->prepare();
    if (\preg_match('(^\d+x\d+$)D', $size)) {
      $this->_pageData['thumbnail_size'] = (string)$size;
    }
    return $this;
  }

  /**
   * Set thumbnail params
   *
   * @param array $params
   * @return self
   */
  public function setThumbnailParameters($params) {
    $this->prepare();
    if (\is_array($params)) {
      $this->_pageData['thumbnail_params'] = \md5(\serialize($params));
    } elseif (\preg_match('(^[a-fA-F\d]{32}$)D', $params)) {
      $this->_pageData['thumbnail_params'] = (string)$params;
    } else {
      $this->_pageData['thumbnail_params'] = \md5($params);
    }
    return $this;
  }

  /**
   * Set extension (normalized string)
   *
   * @param string $extension
   * @return self
   */
  public function setExtension($extension) {
    $this->prepare();
    if (\preg_match('(^[a-zA-Z\d_]+$)D', $extension)) {
      $this->_pageData['extension'] = \strtolower($extension);
    }
    return $this;
  }

  /**
   * Set media data from "uri" [id]v[version].[extension]
   *
   * @param string $mediaUri
   * @return self
   */
  public function setMediaUri($mediaUri) {
    $this->prepare();
    $pattern = '(^
      (?P<media_id>[a-fA-F\d]{32})
      (?:v(?P<version>\d+))?
      (?:
        _(?P<thumbnail_mode>[a-zA-Z]+) # thumbnail mode
        _(?P<thumbnail_size>\d+x\d+) # thumbnail size
      )?
      (?:_(?P<thumbnail_params>[A-Fa-f\d]{32}))? # thumbnail parameters
      (?:\.(?P<thumbnail_format>[a-zA-Z\d]+))?
    $)Dix';
    if (\preg_match($pattern, $mediaUri, $matches)) {
      $this->setMediaId($matches['media_id']);
      if (!empty($matches['version']) && $matches['version'] > 0) {
        $this->setMediaVersion($matches['version']);
      }
      if (!empty($matches['thumbnail_mode'])) {
        $this->setThumbnailMode($matches['thumbnail_mode']);
        $this->setThumbnailSize($matches['thumbnail_size']);
        if (!empty($matches['thumbnail_params'])) {
          $this->setThumbnailParameters($matches['thumbnail_params']);
        }
      }
      if (!empty($matches['thumbnail_format'])) {
        $this->setExtension($matches['thumbnail_format']);
      }
    }
    return $this;
  }

  /**
   * Set preview mode
   *
   * @param bool $isPreview
   * @return \Papaya\UI\Reference\Page
   */
  public function setPreview($isPreview) {
    $this->prepare();
    $this->_pageData['preview'] = (bool)$isPreview;
    return $this;
  }
}
