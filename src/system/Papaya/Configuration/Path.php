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
namespace Papaya\Configuration;

use Papaya\Utility;

/**
 * Object representation a system path (depending on the configuration)
 *
 * @package Papaya-Library
 * @subpackage Configuration
 */
class Path extends \Papaya\Application\BaseObject {
  const PATH_THEMES = 'theme';

  const PATH_THEME_CURRENT = 'current_theme';

  const PATH_INSTALLATION = 'page';

  const PATH_ADMINISTRATION = 'admin';

  const PATH_UPLOAD = 'upload';

  /**
   * @var string
   */
  private $_basePath;

  /**
   * @var string
   */
  private $_path;

  /**
   * @var \Papaya\Theme\Handler
   */
  private $_themeHandler;

  /**
   * Create a system path object (that depends on configuration)
   *
   * @param string $identifier
   * @param string $path
   */
  public function __construct($identifier, $path) {
    $this->_basePath = (string)$identifier;
    $this->_path = (string)$path;
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
        $result = Utility\File\Path::getDocumentRoot().
          $this->papaya()->options->get(\Papaya\Configuration\CMS::PATH_WEB, '/').
          $this->_path;
      break;
      case self::PATH_ADMINISTRATION :
        $result = Utility\File\Path::getDocumentRoot().
          $this->papaya()->options->get(\Papaya\Configuration\CMS::PATH_WEB, '/').
          $this->papaya()->options->get(\Papaya\Configuration\CMS::PATH_ADMIN, '/').
          $this->_path;
      break;
      case self::PATH_UPLOAD :
        $result = $this->papaya()->options->get(\Papaya\Configuration\CMS::PATH_DATA, '/').
          $this->_path;
      break;
      default :
        $result = $this->_basePath.'/'.$this->_path;
      break;
    }
    return Utility\File\Path::cleanup($result);
  }

  /**
   *Getter/Setter for a theme handler subobject.
   *
   * @param \Papaya\Theme\Handler $handler
   *
   * @return \Papaya\Theme\Handler
   */
  public function themeHandler(\Papaya\Theme\Handler $handler = NULL) {
    if (NULL !== $handler) {
      $this->_themeHandler = $handler;
    } elseif (NULL === $this->_themeHandler) {
      $this->_themeHandler = new \Papaya\Theme\Handler();
      $this->_themeHandler->papaya($this->papaya());
    }
    return $this->_themeHandler;
  }

  /**
   * validate if something is an identifier for a system path
   *
   * @param string $identifier
   *
   * @return bool
   */
  public static function isIdentifier($identifier) {
    return \in_array(
      $identifier,
      [
        self::PATH_THEMES,
        self::PATH_THEME_CURRENT,
        self::PATH_INSTALLATION,
        self::PATH_ADMINISTRATION,
        self::PATH_UPLOAD
      ],
      TRUE
    );
  }
}
