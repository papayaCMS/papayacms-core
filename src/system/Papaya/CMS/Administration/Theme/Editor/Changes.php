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
namespace Papaya\CMS\Administration\Theme\Editor;

use Papaya\CMS\Content;
use Papaya\CMS\Theme;
use Papaya\UI;

/**
 * Main part of the theme skins editor (dynamic values for a theme)
 *
 * @package Papaya-Library
 * @subpackage Administration
 */
class Changes extends \Papaya\CMS\Administration\Page\Part {
  /**
   * @var Content\Theme\Skin
   */
  private $_themeSet;

  /**
   * @var Theme\Handler
   */
  private $_themeHandler;

  /**
   * Commands, actual actions
   *
   * @param string $name
   * @param string $default
   *
   * @return UI\Control\Command\Controller
   */
  protected function _createCommands($name = 'cmd', $default = 'skin_edit') {
    $commands = new UI\Control\Command\Controller('cmd');
    $commands->owner($this);
    $commands['skin_edit'] = new Changes\Skin\Change($this->themeSet());
    $commands['skin_delete'] = new Changes\Skin\Remove($this->themeSet());
    $commands['skin_import'] = new Changes\Skin\Import($this->themeSet(), $this->themeHandler());
    $commands['skin_export'] = new Changes\Skin\Export($this->themeSet(), $this->themeHandler());
    $commands['values_edit'] = new Changes\Dialog($this->themeSet());
    return $commands;
  }

  /**
   * The theme skin the the database record wrapper object.
   *
   * @param Content\Theme\Skin $themeSet
   *
   * @return Content\Theme\Skin
   */
  public function themeSet(Content\Theme\Skin $themeSet = NULL) {
    if (NULL !== $themeSet) {
      $this->_themeSet = $themeSet;
    } elseif (NULL === $this->_themeSet) {
      $this->_themeSet = new Content\Theme\Skin();
    }
    return $this->_themeSet;
  }

  /**
   * The theme handler is an helper object to get general information about the
   * themes of the current installation
   *
   * @param Theme\Handler $themeHandler
   *
   * @return Theme\Handler
   */
  public function themeHandler(Theme\Handler $themeHandler = NULL) {
    if (NULL !== $themeHandler) {
      $this->_themeHandler = $themeHandler;
    } elseif (NULL === $this->_themeHandler) {
      $this->_themeHandler = new Theme\Handler();
      $this->_themeHandler->papaya($this->papaya());
    }
    return $this->_themeHandler;
  }
}
