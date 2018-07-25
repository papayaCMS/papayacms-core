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

namespace Papaya\Administration\Theme;
use PapayaThemeHandler;
use PapayaUiDialog;
use Traversable;

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

class Browser
  extends \PapayaUiControl {

  private $_optionName = 'PAPAYA_LAYOUT_THEME';

  /**
   * @var Traversable
   */
  private $_themes;
  /**
   * @var \PapayaThemeHandler
   */
  private $_themeHandler;
  /**
   * @var \PapayaUiDialog
   */
  private $_dialog;

  public function appendTo(\PapayaXmlElement $parent) {
    $parent->append($this->dialog());
  }

  /**
   * @param \PapayaUiDialog $dialog
   * @return \PapayaUiDialog
   */
  public function dialog(\PapayaUiDialog $dialog = NULL) {
    if (isset($dialog)) {
      $this->_dialog = $dialog;
    } elseif (NULL === $this->_dialog) {
      $this->_dialog = $dialog = new \PapayaUiDialog();
      $dialog->caption = new \PapayaUiStringTranslated('Themes (%s)', [$this->_optionName]);
      $dialog->papaya($this->papaya());
      $dialog->parameterGroup('opt');
      $dialog->data()->merge(
        [
          $this->_optionName => $this->papaya()->options[$this->_optionName]
        ]
      );
      $dialog->hiddenFields()->merge(
        [
          'cmd' => 'edit',
          'id' => $this->_optionName,
          'save' => 1
        ]
      );
      $dialog->fields[] = new \PapayaUiDialogFieldCollector(
        $this->_optionName, $this->papaya()->options->get($this->_optionName, '')
      );
      $dialog->fields[] = new \PapayaUiDialogFieldListview(
        $listview = new \PapayaUiListview()
      );
      $listview->mode = \PapayaUiListview::MODE_TILES;
      $listview->builder($builder = new \PapayaUiListviewItemsBuilder($this->themes()));
      $builder->callbacks()->onCreateItem = function (
        $context, \PapayaUiListviewItems $items, \PapayaThemeDefinition $theme
      ) use ($dialog) {
        $items[] = $item = new \PapayaUiListviewItemRadio(
          $theme->thumbnails['medium'], $theme->title, $dialog, $this->_optionName, $theme->name
        );
        $item->text = $theme->templatePath;
      };
      $dialog->buttons[] = new \PapayaUiDialogButtonSubmit(new \PapayaUiStringTranslated('Save'));
    }
    return $this->_dialog;
  }

  /**
   * @param \Traversable|NULL $themes
   * @return \Traversable
   */
  public function themes(\Traversable $themes = NULL) {
    if (isset($themes)) {
      $this->_themes = $themes;
    } elseif (NULL === $this->_themes) {
      $this->_themes = new \PapayaIteratorCaching(
        new \PapayaIteratorFilterCallback(
          new \PapayaIteratorCallback(
            new \DirectoryIterator(
              \PapayaUtilFilePath::cleanup($this->themeHandler()->getLocalPath())
            ),
            function (\DirectoryIterator $fileInfo) {
              if ($fileInfo->isDir() && !$fileInfo->isDot()) {
                if (file_exists($fileInfo->getRealPath().'/theme.xml')) {
                  return $this->themeHandler()->getDefinition($fileInfo->getBasename());
                }
              }
              return FALSE;
            }
          ),
          function ($theme) {
            return $theme instanceof \PapayaThemeDefinition;
          }
        )
      );
    }
    return $this->_themes;
  }

  /**
   * @param \PapayaThemeHandler|NULL $themeHandler
   * @return \PapayaThemeHandler
   */
  public function themeHandler(\PapayaThemeHandler $themeHandler = NULL) {
    if (isset($themeHandler)) {
      $this->_themeHandler = $themeHandler;
    } elseif (NULL === $this->_themeHandler) {
      $this->_themeHandler = new \PapayaThemeHandler();
      $this->_themeHandler->papaya($this->papaya());
    }
    return $this->_themeHandler;
  }

  /**
   * @return \PapayaThemeDefinition
   */
  public function getCurrent() {
    return $this->themeHandler()->getDefinition($this->dialog()->data->get($this->_optionName));
  }

  /**
   * @param string $themeName
   */
  public function setCurrent($themeName) {
    $this->dialog()->data()->set($this->_optionName, (string)$themeName);
  }

}
