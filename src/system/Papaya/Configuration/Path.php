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
* Object represenatin a system path (depending on the configuration)
*
* @package Papaya-Library
* @subpackage Configuration
*/
class PapayaConfigurationPath extends PapayaObject {

  const PATH_THEMES = 'theme';
  const PATH_THEME_CURRENT = 'current_theme';
  const PATH_INSTALLATION = 'page';
  const PATH_ADMINISTRATION = 'admin';
  const PATH_UPLOAD = 'upload';

  /**
   * @var string
   */
  private $_basePath = '';

  /**
   * @var string
   */
  private $_path = '';

  /**
   * @var PapayaThemeHandler
   */
  private $_themeHandler = NULL;

  /**
   * Create a system path object (that depends on configuration)
   *
   * @param string $identifier
   * @param string $path
   */
  public function __construct($identifier, $path) {
    $this->_basePath = $identifier;
    $this->_path = $path;
  }

  /**
   * Allow to cast the system path object to a string
   *
   * @return string
   */
  public function __toString() {
    return $this->get();
  }

  /**
   * Get the system path defined by the identifer and the subdirectory as
   * a string
   *
   * @return string
   */
  public function get() {
    switch ($this->_basePath) {
    case self::PATH_THEMES :
      $result = $this->themeHandler()->getLocalPath().$this->_path;
      break;
    case self::PATH_THEME_CURRENT :
      $result = $this->themeHandler()->getLocalThemePath().$this->_path;
      break;
    case self::PATH_INSTALLATION :
      $result = \PapayaUtilFilePath::getDocumentRoot().
        $this->papaya()->options->get('PAPAYA_PATH_WEB', '/').
        $this->_path;
      break;
    case self::PATH_ADMINISTRATION :
      $result = \PapayaUtilFilePath::getDocumentRoot().
        $this->papaya()->options->get('PAPAYA_PATH_WEB', '/').
        $this->papaya()->options->get('PAPAYA_PATH_ADMIN', '/').
        $this->_path;
      break;
    case self::PATH_UPLOAD :
      $result = $this->papaya()->options->get('PAPAYA_PATH_DATA', '/').
        $this->_path;
      break;
    default :
      $result = $this->_basePath.'/'.$this->_path;
      break;
    }
    return \PapayaUtilFilePath::cleanup($result);
  }

  /**
   *Getter/Setter for a theme handler subobject.
   *
   * @param \PapayaThemeHandler $handler
   * @return \PapayaThemeHandler
   */
  public function themeHandler(\PapayaThemeHandler $handler = NULL) {
    if (isset($handler)) {
      $this->_themeHandler = $handler;
    } elseif (NULL == $this->_themeHandler) {
      $this->_themeHandler = new \PapayaThemeHandler();
      $this->_themeHandler->papaya($this->papaya());
    }
    return $this->_themeHandler;
  }

  /**
   * validate if somthing is an identifer for a system path
   *
   * @param string $identifier
   * @return boolean
   */
  public static function isIdentifier($identifier) {
    return in_array(
      $identifier,
      array(
        self::PATH_THEMES,
        self::PATH_THEME_CURRENT,
        self::PATH_INSTALLATION,
        self::PATH_ADMINISTRATION,
        self::PATH_UPLOAD
      )
    );
  }
}
