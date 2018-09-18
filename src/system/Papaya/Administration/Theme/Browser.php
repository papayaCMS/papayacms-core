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

use Papaya\Theme;
use Papaya\UI;
use Papaya\XML;

class Browser
  extends UI\Control {
  private $_optionName = 'PAPAYA_LAYOUT_THEME';

  /**
   * @var \Traversable
   */
  private $_themes;

  /**
   * @var Theme\Handler
   */
  private $_themeHandler;

  /**
   * @var UI\Dialog
   */
  private $_dialog;

  public function appendTo(XML\Element $parent) {
    $parent->append($this->dialog());
  }

  /**
   * @param UI\Dialog $dialog
   * @return UI\Dialog
   */
  public function dialog(UI\Dialog $dialog = NULL) {
    if (NULL !== $dialog) {
      $this->_dialog = $dialog;
    } elseif (NULL === $this->_dialog) {
      $this->_dialog = $dialog = new UI\Dialog();
      $dialog->caption = new UI\Text\Translated('Themes (%s)', [$this->_optionName]);
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
      $dialog->fields[] = new UI\Dialog\Field\Collector(
        $this->_optionName, $this->papaya()->options->get($this->_optionName, '')
      );
      $dialog->fields[] = new UI\Dialog\Field\ListView(
        $listview = new UI\ListView()
      );
      $listview->mode = UI\ListView::MODE_TILES;
      $listview->builder($builder = new UI\ListView\Items\Builder($this->themes()));
      $builder->callbacks()->onCreateItem = function(
        /* @noinspection PhpUnusedParameterInspection */
        $context, UI\ListView\Items $items, Theme\Definition $theme
      ) use ($dialog) {
        $items[] = $item = new UI\ListView\Item\Radio(
          $theme->thumbnails['medium'], $theme->title, $dialog, $this->_optionName, $theme->name
        );
        $item->text = $theme->templatePath;
      };
      $dialog->buttons[] = new UI\Dialog\Button\Submit(new UI\Text\Translated('Save'));
    }
    return $this->_dialog;
  }

  /**
   * @param \Traversable|null $themes
   * @return \Traversable
   */
  public function themes(\Traversable $themes = NULL) {
    if (NULL !== $themes) {
      $this->_themes = $themes;
    } elseif (NULL === $this->_themes) {
      $this->_themes = new \Papaya\Iterator\Caching(
        new \Papaya\Iterator\Filter\Callback(
          new \Papaya\Iterator\Callback(
            new \DirectoryIterator(
              \Papaya\Utility\File\Path::cleanup($this->themeHandler()->getLocalPath())
            ),
            function(\DirectoryIterator $fileInfo) {
              if (
                $fileInfo->isDir() &&
                !$fileInfo->isDot() &&
                \file_exists($fileInfo->getRealPath().'/theme.xml')
              ) {
                return $this->themeHandler()->getDefinition($fileInfo->getBasename());
              }
              return FALSE;
            }
          ),
          function($theme) {
            return $theme instanceof Theme\Definition;
          }
        )
      );
    }
    return $this->_themes;
  }

  /**
   * @param Theme\Handler|null $themeHandler
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

  /**
   * @return Theme\Definition
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
