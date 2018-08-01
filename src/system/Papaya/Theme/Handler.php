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
 * Papaya theme handler class
 *
 * @package Papaya-Library
 * @subpackage Theme
 */
class Handler extends \Papaya\Application\BaseObject {

  /**
   * Get url for theme files, is $themeName is empty the current theme is used.
   *
   * @param string $themeName
   * @return string
   */
  public function getUrl($themeName = NULL) {
    $options = $this->papaya()->options;
    $baseUrl = '';
    if (\Papaya\Utility\Server\Protocol::isSecure()) {
      $baseUrl = $options->get('PAPAYA_CDN_THEMES_SECURE', '');
    }
    if (empty($baseUrl)) {
      $baseUrl = $options->get('PAPAYA_CDN_THEMES', '');
    }
    if (empty($baseUrl)) {
      $baseUrl = $this
        ->papaya()
        ->request
        ->getUrl()
        ->getHostUrl();
      $baseUrl .= \Papaya\Utility\File\Path::cleanup(
        $options->get('PAPAYA_PATH_WEB').$options->get('PAPAYA_PATH_THEMES')
      );
    }
    if (empty($themeName)) {
      return $baseUrl.$this->getTheme().'/';
    } else {
      return $baseUrl.$themeName.'/';
    }
  }

  /**
   * Get local path on server to theme directories
   *
   * @return string
   */
  public function getLocalPath() {
    $root = \Papaya\Utility\File\Path::getDocumentRoot(
      $this->papaya()->options
    );
    $path = $this
      ->papaya()
      ->options
      ->get('PAPAYA_PATH_THEMES');
    return \Papaya\Utility\File\Path::cleanup($root.'/'.$path);
  }

  /**
   * Get local path on server to theme files
   *
   * @param string $themeName
   * @return string
   */
  public function getLocalThemePath($themeName = NULL) {
    if (empty($themeName)) {
      $themeName = $this->getTheme();
    }
    return \Papaya\Utility\File\Path::cleanup(
      $this->getLocalPath().$themeName
    );
  }

  /**
   * Load the dynamic value defintion from the theme.xml and return it
   *
   * @param string $theme
   * @return \Papaya\Theme\Definition
   */
  public function getDefinition($theme) {
    $definition = new \Papaya\Theme\Definition();
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
   * Get the currently active theme set id
   *
   * @return string
   */
  public function getThemeSet() {
    $themeSet = 0;
    $isPreview = $this
      ->papaya()
      ->request
      ->getParameter('preview', FALSE, NULL, \Papaya\Request::SOURCE_PATH);
    if ($isPreview) {
      $themeSet = $this
        ->papaya()
        ->session
        ->values
        ->get('PapayaPreviewThemeSet', 0);
    }
    if ($themeSet <= 0) {
      $themeSet = $this
        ->papaya()
        ->options
        ->get('PAPAYA_LAYOUT_THEME_SET', 0);
    }
    return (int)$themeSet;
  }

  /**
   * Set preview theme (saved in session)
   *
   * @param string $themeName
   * @return void
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
   *
   * @return void
   */
  public function removeThemePreview() {
    $this
      ->papaya()
      ->session
      ->values
      ->set('PapayaPreviewTheme', NULL);
  }
}
