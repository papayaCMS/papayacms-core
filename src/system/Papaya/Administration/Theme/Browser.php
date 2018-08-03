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
  extends \Papaya\UI\Control {

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
   * @var \Papaya\UI\Dialog
   */
  private $_dialog;

  public function appendTo(\Papaya\Xml\Element $parent) {
    $parent->append($this->dialog());
  }

  /**
   * @param \Papaya\UI\Dialog $dialog
   * @return \Papaya\UI\Dialog
   */
  public function dialog(\Papaya\UI\Dialog $dialog = NULL) {
    if (isset($dialog)) {
      $this->_dialog = $dialog;
    } elseif (NULL === $this->_dialog) {
      $this->_dialog = $dialog = new \Papaya\UI\Dialog();
      $dialog->caption = new \Papaya\UI\Text\Translated('Themes (%s)', [$this->_optionName]);
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
      $dialog->fields[] = new \Papaya\UI\Dialog\Field\Collector(
        $this->_optionName, $this->papaya()->options->get($this->_optionName, '')
      );
      $dialog->fields[] = new \Papaya\UI\Dialog\Field\Listview(
        $listview = new \Papaya\UI\Listview()
      );
      $listview->mode = \Papaya\UI\Listview::MODE_TILES;
      $listview->builder($builder = new \Papaya\UI\Listview\Items\Builder($this->themes()));
      $builder->callbacks()->onCreateItem = function (
        $context, \Papaya\UI\Listview\Items $items, \Papaya\Theme\Definition $theme
      ) use ($dialog) {
        $items[] = $item = new \Papaya\UI\Listview\Item\Radio(
          $theme->thumbnails['medium'], $theme->title, $dialog, $this->_optionName, $theme->name
        );
        $item->text = $theme->templatePath;
      };
      $dialog->buttons[] = new \Papaya\UI\Dialog\Button\Submit(new \Papaya\UI\Text\Translated('Save'));
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
