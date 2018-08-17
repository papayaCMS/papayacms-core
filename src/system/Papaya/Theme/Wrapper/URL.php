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

namespace Papaya\Theme\Wrapper;
/**
 * Extract theme wrapper data from an url object
 *
 * The path of a theme url ends with "/theme/file". The theme name has to be present in the
 * url just before the file. The file indicates the mimetype of the files to compile together.
 *
 * The query string parameter 'files' is a comma separated list of file identifiers
 * (file names with or without the extension). For JavaScript files subdirectories are always allowed.
 * For css the parameter 'rec' (recursive) can be used to allow subdirectories.
 *
 * If you allow subdirectories for css, be aware of the relative urls inside the css file. The
 * browser will interpret them different from direct access.
 *
 * @package Papaya-Library
 * @subpackage Theme
 */
class URL {

  /**
   * @var \Papaya\URL
   */
  private $_requestURL = NULL;

  private $_mimetypeIdentification = array(
    'text/javascript' => '((/[^/]+)/js(\\.php)?$)',
    'text/css' => '((/[^/]+)/css(\\.php)?$)',
    'image/*' => '((/[^/]+)/image(\\.php)?$)',
  );

  /**
   * @var \Papaya\Request\Parameters
   */
  private $_parameters;

  /**
   * Initialize using an url object.
   *
   * @param \Papaya\URL $url
   */
  public function __construct(\Papaya\URL $url = NULL) {
    if (isset($url)) {
      $this->_requestURL = $url;
    } else {
      $this->_requestURL = new \Papaya\URL\Current();
    }
  }

  /**
   * Get mimetype from url path
   *
   * @return string|NULL
   */
  public function getMimetype() {
    $path = $this->_requestURL->getPath();
    foreach ($this->_mimetypeIdentification as $type => $pattern) {
      if (preg_match($pattern, $path)) {
        return $type;
      }
    }
    return FALSE;
  }

  /**
   * Getter/Setter for url parameters.
   *
   * If the $_parameters property is not set it will be initialized using the query string of the
   * $_requestURL property.
   *
   * @param \Papaya\Request\Parameters $parameters
   * @return \Papaya\Request\Parameters
   */
  public function parameters(\Papaya\Request\Parameters $parameters = NULL) {
    if (NULL !== $parameters) {
      $this->_parameters = $parameters;
    } elseif (NULL === $parameters) {
      $query = new \Papaya\Request\Parameters\QueryString();
      $query->setString($this->_requestURL->getQuery());
      $this->_parameters = $query->values();
    }
    return $this->_parameters;
  }

  /**
   * Get the group name that provides the file list
   *
   * The groups are specified in the theme.xml
   *
   * @return string
   */
  public function getGroup() {
    return $this->parameters()->get('group');
  }

  /**
   * Get the theme set id that provides the dynamic values
   *
   * @return string
   */
  public function getThemeSet() {
    return $this->parameters()->get('set', 0);
  }

  /**
   * Get the files list from the query string parameter.
   *
   * The parameter is a comma separated string.
   *
   * @return array
   */
  public function getFiles() {
    return explode(',', $this->parameters()->get('files'));
  }

  /**
   * Get theme from url path
   *
   * The theme is the last directory before the wrapper script itself.
   *
   * @return string
   */
  public function getTheme() {
    $path = strrchr(dirname($this->_requestURL->getPath()), '/');
    return substr($path, 1);
  }

  /**
   * Return if subdirectores are allowed.
   *
   * @return boolean
   */
  public function allowDirectories() {
    return 'yes' === $this->parameters()->get('rec', 'no');
  }

}

