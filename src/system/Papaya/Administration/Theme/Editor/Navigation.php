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

namespace Papaya\Administration\Theme\Editor;

use Papaya\Content;
use Papaya\Iterator;
use Papaya\Theme;
use Papaya\UI;
use Papaya\XML;

/**
 * Navigation part of the theme skins editor (dynamic values for a theme)
 *
 * @package Papaya-Library
 * @subpackage Administration
 */
class Navigation extends \Papaya\Administration\Page\Part {
  /**
   * @var UI\ListView
   */
  private $_listview;

  /**
   * Append navigation to parent xml element
   *
   * @param XML\Element $parent
   */
  public function appendTo(XML\Element $parent) {
    $parent->append($this->listview());
    if ('' !== ($themeName = $this->parameters()->get('theme', ''))) {
      $skinId = $this->parameters()->get('skin_id', 0);
      $this->toolbar()->elements[] = $button = new UI\Toolbar\Button();
      $button->caption = new UI\Text\Translated('Add skin');
      $button->image = 'actions-generic-add';
      $button->reference()->setParameters(
        [
          'cmd' => 'skin_edit',
          'theme' => $themeName,
          'skin_id' => 0
        ],
        $this->parameterGroup()
      );
      if (0 < $skinId) {
        $this->toolbar()->elements[] = $button = new UI\Toolbar\Button();
        $button->caption = new UI\Text\Translated('Delete skin');
        $button->image = 'actions-generic-delete';
        $button->reference()->setParameters(
          [
            'cmd' => 'skin_delete',
            'theme' => $themeName,
            'skin_id' => $skinId
          ],
          $this->parameterGroup()
        );
      }
      $this->toolbar()->elements[] = $button = new UI\Toolbar\Button();
      $button->caption = new UI\Text\Translated('Import');
      $button->image = 'actions-upload';
      $button->reference()->setParameters(
        [
          'cmd' => 'skin_import',
          'theme' => $themeName,
          'skin_id' => $skinId
        ],
        $this->parameterGroup()
      );
      if (0 < $skinId) {
        $this->toolbar()->elements[] = $button = new UI\Toolbar\Button();
        $button->caption = new UI\Text\Translated('Export');
        $button->image = 'actions-download';
        $button->reference()->setParameters(
          [
            'cmd' => 'skin_export',
            'theme' => $themeName,
            'skin_id' => $skinId
          ],
          $this->parameterGroup()
        );
      }
    }
  }

  /**
   * Getter/Setter for the theme navigation listview
   *
   * It displays the list of Themes, the Sets of the selected theme and the pages of the
   * selected skin.
   *
   * @param UI\ListView $listview
   * @return UI\ListView
   */
  public function listview(UI\ListView $listview = NULL) {
    if (NULL !== $listview) {
      $this->_listview = $listview;
    } elseif (NULL === $this->_listview) {
      $this->_listview = new UI\ListView();
      $this->_listview->caption = new UI\Text\Translated('Themes');
      $this->_listview->builder(
        $builder = new UI\ListView\Items\Builder(
          new \RecursiveIteratorIterator(
            $this->createThemeList(), \RecursiveIteratorIterator::SELF_FIRST
          )
        )
      );
      $builder->callbacks()->onCreateItem = function($builder, $items, $element) {
        $this->callbackCreateItem($builder, $items, $element);
      };
      $builder->callbacks()->onCreateItem->context = $builder;
      $this->_listview->parameterGroup($this->parameterGroup());
      $this->_listview->parameters($this->parameters());
    }
    return $this->_listview;
  }

  /**
   * Get the Theme list for the listview. The result is an RecursiveIterator, the
   * skins of the selected theme are attached as children to the theme element
   *
   * If a skin is selected, the value pages from the theme.xml are attached to the skin
   *
   * @return \RecursiveIterator
   */
  private function createThemeList() {
    $themes = new Theme\Collection();
    $themes->papaya($this->papaya());
    $themeIterator = new Iterator\Tree\Items(
      $themes, Iterator\Tree\Items::ATTACH_TO_VALUES
    );
    $selectedTheme = $this->parameters()->get('theme', '');
    if (!empty($selectedTheme)) {
      $skins = new Content\Theme\Skins();
      $skins->activateLazyLoad(['theme' => $selectedTheme]);
      $skinIterator = new Iterator\Tree\Items($skins);
      $selectedSet = $this->parameters()->get('skin_id', 0);
      if ($selectedSet > 0) {
        $skinIterator->attachItemIterator(
          $selectedSet,
          new Iterator\Generator(
            function() use ($themes, $selectedTheme) {
              return $themes->getDefinition($selectedTheme);
            }
          )
        );
      }
      $themeIterator->attachItemIterator($selectedTheme, $skinIterator);
    }
    return $themeIterator;
  }

  /**
   * Callback to create the items, depending on the depth here are the theme and skin elements
   *
   * @param UI\ListView\Items\Builder $builder
   * @param UI\ListView\Items $items
   * @param mixed $element
   * @return null|UI\ListView\Item
   */
  public function callbackCreateItem(UI\ListView\Items\Builder $builder, UI\ListView\Items $items, $element) {
    /* @noinspection PhpUndefinedMethodInspection */
    switch ($builder->getDataSource()->getDepth()) {
      case 0 :
        $items[] = $item = $this->createThemeItem($element);
        return $item;
      case 1 :
        $items[] = $item = $this->createSetItem($element);
        return $item;
      case 2 :
        $items[] = $item = $this->createPageItem($element);
        return $item;
    }
  }

  /**
   * Create the listitem for a theme
   *
   * @param string $element
   * @return UI\ListView\Item
   */
  private function createThemeItem($element) {
    $item = new UI\ListView\Item('items-theme', (string)$element);
    $item->papaya($this->papaya());
    $item->reference->setParameters(
      [
        'cmd' => 'theme_show',
        'theme' => $element
      ],
      $this->parameterGroup()
    );
    $item->selected = (
      !$this->parameters()->get('skin_id', 0) &&
      $this->parameters()->get('theme', '') === $element
    );
    return $item;
  }

  /**
   * Create the listitem for a skin
   *
   * @param array $element
   * @return UI\ListView\Item
   */
  private function createSetItem($element) {
    $item = new UI\ListView\Item('items-folder', (string)$element['title']);
    $item->papaya($this->papaya());
    $item->indentation = 1;
    $item->reference->setParameters(
      [
        'cmd' => 'skin_edit',
        'theme' => $element['theme'],
        'skin_id' => $element['id']
      ],
      $this->parameterGroup()
    );
    $item->selected =
      ('' == $this->parameters()->get('page_identifier', '')) &&
      $this->parameters()->get('skin_id', 0) == $element['id'];
    return $item;
  }

  /**
   * Create the listitem for a theme values page
   *
   * @param \Papaya\Content\Structure\Page $element
   * @return UI\ListView\Item
   */
  private function createPageItem(\Papaya\Content\Structure\Page $element) {
    $item = new UI\ListView\Item('items-folder', (string)$element->title);
    $item->papaya($this->papaya());
    $item->indentation = 2;
    $item->reference->setParameters(
      [
        'cmd' => 'values_edit',
        'theme' => $this->parameters()->get('theme', ''),
        'skin_id' => $this->parameters()->get('skin_id', 0),
        'page_identifier' => $element->getIdentifier()
      ],
      $this->parameterGroup()
    );
    $item->selected = $this->parameters()->get('page_identifier', '') == $element->getIdentifier();
    return $item;
  }
}
