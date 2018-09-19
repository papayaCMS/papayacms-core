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
namespace Papaya\Theme;

/**
 * Combine, cache and output theme files (javascript/css)
 *
 * @package Papaya-Library
 * @subpackage Theme
 */
class Wrapper extends \Papaya\Application\BaseObject {
  /**
   * @var Wrapper\URL
   */
  private $_wrapperURL;

  /**
   * Theme handler object, provides current theme and local path
   *
   * @var \Papaya\Theme\Handler
   */
  private $_handler;

  /**  *
   * @var \Papaya\Theme\Handler
   */
  private $_themeSet;

  /**
   * Theme group object, allows to read files from a group specified in theme.xml.
   *
   * @var Wrapper\Group
   */
  private $_group;

  /**
   * @var \Papaya\Cache\Service
   */
  private $_cacheService;

  /**
   * @var \Papaya\Template\Engine
   */
  private $_templateEngine;

  /**
   * Initialize object using a wrapper url object.
   *
   * If no object if provided, a default object with access to the current request url is created.
   *
   * @param Wrapper\URL $wrapperURL
   */
  public function __construct(Wrapper\URL $wrapperURL = NULL) {
    if (NULL !== $wrapperURL) {
      $this->_wrapperURL = $wrapperURL;
    } else {
      $this->_wrapperURL = new Wrapper\URL();
    }
  }

  /**
   * Get/Set the theme wrapper group object.
   *
   * @param Wrapper\Group $group
   *
   * @return Wrapper\Group
   */
  public function group(Wrapper\Group $group = NULL) {
    if (NULL !== $group) {
      $this->_group = $group;
    } elseif (NULL === $this->_group) {
      $this->_group = new Wrapper\Group(
        $this->handler()->getLocalThemePath().'theme.xml'
      );
    }
    return $this->_group;
  }

  /**
   * Return a response object containing http headers and content.
   *
   * If the current content is in the browser cache it will return a 304 status and an empty
   * content.
   *
   * If the current content is cached on the server it will return this with needed headers for
   * public caching.
   *
   * In other cases the newly generated content will be returned.
   */
  public function getResponse() {
    $application = $this->papaya();
    $mimetype = $this->_wrapperURL->getMimetype();
    $theme = $this->_wrapperURL->getTheme();
    $themeSetId = $this->_wrapperURL->getThemeSet();
    $compress = (
      $application->request->allowCompression() &&
      $application->options->get('PAPAYA_COMPRESS_CACHE_THEMES', TRUE)
    );
    $files = $this->getFiles();
    $cacheId = $this->getCacheIdentifier($themeSetId, $files, $mimetype, $compress);
    if ($application->options->get('PAPAYA_CACHE_THEMES', FALSE)) {
      $cacheTime = $application->options->get('PAPAYA_CACHE_TIME_THEMES', 0);
    } else {
      $cacheTime = 0;
    }
    $response = new \Papaya\Response();
    $data = NULL;
    $lastModified = 0;
    if ($cacheTime > 0) {
      $lastModified = $this->cache()->created('theme', $theme, $cacheId, $cacheTime);
      if ($application->request->validateBrowserCache($cacheId, $lastModified)) {
        $response->setStatus(304);
        $response->setCache('public', $cacheTime, $lastModified);
        $response->headers()->set('Etag', $cacheId);
        return $response;
      }
      $data = $this->cache()->read('theme', $theme, $cacheId, $cacheTime);
    }
    if ($data) {
      $response->setCache('public', $cacheTime, $lastModified);
      $response->headers()->set('Etag', $cacheId);
    } else {
      $data = $this->getCompiledContent($theme, $themeSetId, $files, $compress);
      if ($cacheTime > 0) {
        $this->cache()->write(
          'theme', $theme, $cacheId, $data, $cacheTime
        );
        $response->headers()->set('Etag', $cacheId);
      }
      $response->setCache('public', $cacheTime);
    }
    $response->content(new \Papaya\Response\Content\Text($data));
    if ($compress) {
      $response->headers()->set('X-Papaya-Compress', 'yes');
      $response->headers()->set('Content-Encoding', 'gzip');
    }
    $response->setStatus(200);
    $response->setContentType(empty($mimetype) ? 'text/plain' : $mimetype);
    return $response;
  }

  /**
   * Getter/setter for theme handler object including implicit create
   *
   * @param \Papaya\Theme\Handler $handler
   *
   * @return \Papaya\Theme\Handler
   */
  public function handler(\Papaya\Theme\Handler $handler = NULL) {
    if (isset($handler)) {
      $this->_handler = $handler;
    }
    if (\is_null($this->_handler)) {
      $this->_handler = new \Papaya\Theme\Handler();
      $this->_handler->papaya($this->papaya());
    }
    return $this->_handler;
  }

  /**
   * Getter/setter for theme set database object including a implicit create
   *
   * @param \Papaya\Content\Theme\Skin $themeSet
   *
   * @return \Papaya\Content\Theme\Skin
   */
  public function themeSet(\Papaya\Content\Theme\Skin $themeSet = NULL) {
    if (isset($themeSet)) {
      $this->_themeSet = $themeSet;
    }
    if (\is_null($this->_themeSet)) {
      $this->_themeSet = new \Papaya\Content\Theme\Skin();
      $this->_themeSet->papaya($this->papaya());
    }
    return $this->_themeSet;
  }

  /**
   * Getter/setter for cache service object
   *
   * @param \Papaya\Cache\Service $service
   *
   * @return \Papaya\Cache\Service
   */
  public function cache(\Papaya\Cache\Service $service = NULL) {
    if (isset($service)) {
      $this->_cacheService = $service;
    }
    if (\is_null($this->_cacheService)) {
      /* @noinspection PhpParamsInspection */
      $this->_cacheService = \Papaya\Cache::getService(
        $this->papaya()->options
      );
    }
    return $this->_cacheService;
  }

  /**
   * Compile file contents into a single output
   *
   * @param string $theme
   * @param int $themeSetId
   * @param array $files
   * @param bool $compress use gzip compression
   *
   * @return string
   */
  public function getCompiledContent($theme, $themeSetId, $files, $compress) {
    $localPath = $this->handler()->getLocalThemePath($theme);
    $result = '';
    $fileCount = \count($files);
    foreach ($files as $fileName) {
      $localFile = $localPath.$fileName;
      if (\file_exists($localFile) &&
        \is_file($localFile) &&
        \is_readable($localFile)) {
        if ($fileCount > 1) {
          $result .= "\n/* Adding file: ".$fileName." */\n";
        }
        $result .= \file_get_contents($localFile);
      } else {
        $result .= "\n/* Missing file: ".$fileName." */\n";
      }
    }
    if (($engine = $this->templateEngine()) &&
      $themeSetId > 0 &&
      $this->themeSet()->load($themeSetId)) {
      $engine->setTemplateString($result);
      $engine->values(
        $this->themeSet()->getValuesXML($this->handler()->getDefinition($theme))
      );
      $engine->prepare();
      $engine->run();
      $result = $engine->getResult();
    }
    return ($compress) ? \gzencode($result) : $result;
  }

  /**
   * Getter/Setter for the active template engine.
   *
   * @param \Papaya\Template\Engine $engine
   *
   * @return \Papaya\Template\Engine|null
   */
  public function templateEngine(\Papaya\Template\Engine $engine = NULL) {
    if (isset($engine)) {
      $this->_templateEngine = $engine;
    }
    return $this->_templateEngine;
  }

  /**
   * Take a list of file identifiers check them agains syntax rules and return filenames.
   *
   * The identifier are basically files names, but the can emit the extension. In this case the
   * method will add the extension. It will remove whitespraces around the filename, too.
   *
   * The resulting filename is checked for invalid input. An unique list of valid filenames is
   * returned.
   *
   * The mothod does not check if the file exists.
   *
   * @return array
   */
  public function getFiles() {
    $mimetype = $this->_wrapperURL->getMimetype();
    if ($mimetype) {
      switch ($mimetype) {
        case 'text/javascript' :
          $extension = 'js';
          $allowDirectories = TRUE;
        break;
        case 'text/css' :
        default :
          $extension = 'css';
          $allowDirectories = $this->_wrapperURL->allowDirectories();
          $this->templateEngine(new \Papaya\Template\Engine\Simple());
      }
      if ($group = $this->_wrapperURL->getGroup()) {
        $files = $this->group()->getFiles($group, $extension);
        if (!$allowDirectories) {
          $allowDirectories = $this->group()->allowDirectories($group, $extension);
        }
      } else {
        $files = $this->_wrapperURL->getFiles();
      }
      if ($allowDirectories) {
        $pattern = '(^([a-z\d_-]+/)*[a-z\d._-]+\\.'.\preg_quote($extension).'$)iS';
      } else {
        $pattern = '(^[a-z\d._-]+\\.'.\preg_quote($extension).'$)iS';
      }
      $result = [];
      foreach ($files as $fileIdentifier) {
        $fileName = $this->prepareFileName($fileIdentifier, '.'.$extension);
        if (\preg_match($pattern, $fileName)) {
          $result[] = $fileName;
        }
      }
      return \array_unique($result);
    }
    return [];
  }

  /**
   * Trim whitespaces and add extension if it is not here.
   *
   * @param string $fileIdentifier
   * @param string $extension
   *
   * @return string
   */
  private function prepareFileName($fileIdentifier, $extension) {
    $fileName = \trim($fileIdentifier);
    if (\substr($fileName, 0 - \strlen($extension)) != $extension) {
      $fileName .= $extension;
    }
    return $fileName;
  }

  /**
   * Get an unique cache identifier based on a file list and output compression.
   *
   * @param int $themeSetId
   * @param array $files
   * @param string $mimetype
   * @param bool $compress
   *
   * @return string
   */
  public function getCacheIdentifier($themeSetId, $files, $mimetype, $compress = FALSE) {
    $options = $this->papaya()->options;
    $result = ($themeSetId > 0) ? $themeSetId.'_' : '';
    $result .= \implode(
      '_',
      [
        $options->get('PAPAYA_WEBSITE_REVISION', 'dev'),
        'text/css' == $mimetype ? 'css' : 'js',
        \md5(\serialize($files))
      ]
    );
    $result .= ($compress ? '.gz' : '');
    return $result;
  }
}
