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

use Papaya\Application;
use Papaya\Utility;

/**
 * Papaya theme handler class
 *
 * @package Papaya-Library
 * @subpackage Theme
 */
class Handler implements Application\Access {
  use Application\Access\Aggregation;

  /**
   * Get url for theme files, is $themeName is empty the current theme is used.
   *
   * @param string|null $themeName
   * @param string|null $fileName
   * @return string
   */
  public function getURL($themeName = NULL, $fileName = NULL) {
    $options = $this->papaya()->options;
    $baseURL = '';
    if (Utility\Server\Protocol::isSecure()) {
      $baseURL = $options->get('PAPAYA_CDN_THEMES_SECURE', '');
    }
    if (empty($baseURL)) {
      $baseURL = $options->get('PAPAYA_CDN_THEMES', '');
    }
    if (empty($baseURL)) {
      $baseURL = $this
        ->papaya()
        ->request
        ->getURL()
        ->getHostURL();
      $baseURL .= Utility\File\Path::cleanup(
        $options->get('PAPAYA_PATH_WEB').$options->get('PAPAYA_PATH_THEMES')
      );
    }
    if ('' === \trim($themeName)) {
      $url = $baseURL.$this->getTheme().'/';
    } else {
      $url = $baseURL.$themeName.'/';
    }
    if (NULL !== $fileName) {
      return '' !== $this->getLocalThemeFile($themeName, $fileName) ? $url.$fileName : '';
    }
    return $url;
  }

  /**
   * Get local path on server to theme directories
   *
   * @return string
   */
  public function getLocalPath() {
    $root = Utility\File\Path::getDocumentRoot(
      $this->papaya()->options
    );
    $path = $this
      ->papaya()
      ->options
      ->get('PAPAYA_PATH_THEMES');
    return Utility\File\Path::cleanup($root.'/'.$path);
  }

  /**
   * Get local path on server to theme files
   *
   * @param string $themeName
   *
   * @return string
   */
  public function getLocalThemePath($themeName = NULL) {
    if ('' === \trim($themeName)) {
      $themeName = $this->getTheme();
    }
    return Utility\File\Path::cleanup(
      $this->getLocalPath().$themeName
    );
  }

  /**
   * Get local path on server to theme files
   *
   * @param string $fileName
   * @param string $themeName
   *
   * @return string
   */
  public function getLocalThemeFile($fileName, $themeName = NULL) {
    if ('' === \trim($fileName)) {
      return FALSE;
    }
    if ('' === \trim($themeName)) {
      $themeName = $this->getTheme();
    }
    $fileName = Utility\File\Path::cleanup(
      $this->getLocalPath().$themeName.'/'.$fileName
    );
    if (file_exists($fileName) && is_file($fileName)) {
      return $fileName;
    }
    return '';
  }

  /**
   * Load the dynamic value defintion from the theme.xml and return it
   *
   * @param string $theme
   *
   * @return Definition
   */
  public function getDefinition($theme) {
    $definition = new Definition();
    $definition->load(
      $this->getLocalThemePath($theme).'/theme.xml'
    );
    return $definition;
  }

  /**
   * Get the currently active theme name
   *
   * @return string
   */
  public function getTheme() {
    $theme = '';
    $isPreview = $this
      ->papaya()
      ->request
      ->getParameter('preview', FALSE, NULL, \Papaya\Request::SOURCE_PATH);
    if ($isPreview) {
      $theme = $this
        ->papaya()
        ->session
        ->values
        ->get('PapayaPreviewTheme');
    }
    if (empty($theme)) {
      $theme = $this
        ->papaya()
        ->options
        ->get('PAPAYA_LAYOUT_THEME');
    }
    return $theme;
  }

  /**
   * Get the currently active theme skin id
   *
   * @return string
   */
  public function getThemeSkin() {
    $themeSkin = 0;
    $isPreview = $this->papaya()->request->isPreview;
    if ($isPreview) {
      $themeSkin = $this
        ->papaya()
        ->session
        ->values
        ->get('PapayaPreviewThemeSkin');
    }
    if ($themeSkin <= 0) {
      $themeSkin = $this
        ->papaya()
        ->options
        ->get('PAPAYA_LAYOUT_THEME_SET', 0);
    }
    return (int)$themeSkin;
  }

  /**
   * @deprecated
   * @return string
   */
  public function getThemeSet() {
    return $this->getThemeSkin();
  }

  /**
   * Set preview theme (saved in session)
   *
   * @param string $themeName
   */
  public function setThemePreview($themeName) {
    $this
      ->papaya()
      ->session
      ->values
      ->set('PapayaPreviewTheme', $themeName);
  }

  /**
   * Remove preview theme (saved in session)
   */
  public function removeThemePreview() {
    $this
      ->papaya()
      ->session
      ->values
      ->set('PapayaPreviewTheme', NULL);
  }
}
