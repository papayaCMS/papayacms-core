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
  extends \Papaya\Ui\Control {

  private $_optionName = 'PAPAYA_LAYOUT_THEME';

  /**
   * @var \Traversable
   */
  private $_themes;
  /**
   * @var \Papaya\Theme\Handler
   */
  private $_themeHandler;
  /**
   * @var \Papaya\Ui\Dialog
   */
  private $_dialog;

  public function appendTo(\Papaya\Xml\Element $parent) {
    $parent->append($this->dialog());
  }

  /**
   * @param \Papaya\Ui\Dialog $dialog
   * @return \Papaya\Ui\Dialog
   */
  public function dialog(\Papaya\Ui\Dialog $dialog = NULL) {
    if (isset($dialog)) {
      $this->_dialog = $dialog;
    } elseif (NULL === $this->_dialog) {
      $this->_dialog = $dialog = new \Papaya\Ui\Dialog();
      $dialog->caption = new \Papaya\Ui\Text\Translated('Themes (%s)', [$this->_optionName]);
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
      $dialog->fields[] = new \Papaya\Ui\Dialog\Field\Collector(
        $this->_optionName, $this->papaya()->options->get($this->_optionName, '')
      );
      $dialog->fields[] = new \Papaya\Ui\Dialog\Field\Listview(
        $listview = new \Papaya\Ui\Listview()
      );
      $listview->mode = \Papaya\Ui\Listview::MODE_TILES;
      $listview->builder($builder = new \Papaya\Ui\Listview\Items\Builder($this->themes()));
      $builder->callbacks()->onCreateItem = function (
        $context, \Papaya\Ui\Listview\Items $items, \Papaya\Theme\Definition $theme
      ) use ($dialog) {
        $items[] = $item = new \Papaya\Ui\Listview\Item\Radio(
          $theme->thumbnails['medium'], $theme->title, $dialog, $this->_optionName, $theme->name
        );
        $item->text = $theme->templatePath;
      };
      $dialog->buttons[] = new \Papaya\Ui\Dialog\Button\Submit(new \Papaya\Ui\Text\Translated('Save'));
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
      $this->_themes = new \Papaya\Iterator\Caching(
        new \Papaya\Iterator\Filter\Callback(
          new \Papaya\Iterator\Callback(
            new \DirectoryIterator(
              \Papaya\Utility\File\Path::cleanup($this->themeHandler()->getLocalPath())
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
            return $theme instanceof \Papaya\Theme\Definition;
          }
        )
      );
    }
    return $this->_themes;
  }

  /**
   * @param \Papaya\Theme\Handler|NULL $themeHandler
   * @return \Papaya\Theme\Handler
   */
  public function themeHandler(\Papaya\Theme\Handler $themeHandler = NULL) {
    if (isset($themeHandler)) {
      $this->_themeHandler = $themeHandler;
    } elseif (NULL === $this->_themeHandler) {
      $this->_themeHandler = new \Papaya\Theme\Handler();
      $this->_themeHandler->papaya($this->papaya());
    }
    return $this->_themeHandler;
  }

  /**
   * @return \Papaya\Theme\Definition
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
