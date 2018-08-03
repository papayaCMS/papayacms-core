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
use Papaya\UI\Listview;

/**
 * Navigation part of the theme sets editor (dynamic values for a theme)
 *
 * @package Papaya-Library
 * @subpackage Administration
 */
class Navigation extends \Papaya\Administration\Page\Part {

  /**
   * @var \Papaya\UI\Listview
   */
  private $_listview = NULL;

  /**
   * Append navigation to parent xml element
   *
   * @param \Papaya\XML\Element $parent
   */
  public function appendTo(\Papaya\XML\Element $parent) {
    $parent->append($this->listview());
    if ('' != ($themeName = $this->parameters()->get('theme', ''))) {
      $setId = $this->parameters()->get('set_id', 0);
      $this->toolbar()->elements[] = $button = new \Papaya\UI\Toolbar\Button();
      $button->caption = new \Papaya\UI\Text\Translated('Add set');
      $button->image = 'actions-generic-add';
      $button->reference()->setParameters(
        array(
          'cmd' => 'set_edit',
          'theme' => $themeName,
          'set_id' => 0
        ),
        $this->parameterGroup()
      );
      if (0 < $setId) {
        $this->toolbar()->elements[] = $button = new \Papaya\UI\Toolbar\Button();
        $button->caption = new \Papaya\UI\Text\Translated('Delete set');
        $button->image = 'actions-generic-delete';
        $button->reference()->setParameters(
          array(
            'cmd' => 'set_delete',
            'theme' => $themeName,
            'set_id' => $setId
          ),
          $this->parameterGroup()
        );
      }
      $this->toolbar()->elements[] = $button = new \Papaya\UI\Toolbar\Button();
      $button->caption = new \Papaya\UI\Text\Translated('Import');
      $button->image = 'actions-upload';
      $button->reference()->setParameters(
        array(
          'cmd' => 'set_import',
          'theme' => $themeName,
          'set_id' => $setId
        ),
        $this->parameterGroup()
      );
      if (0 < $setId) {
        $this->toolbar()->elements[] = $button = new \Papaya\UI\Toolbar\Button();
        $button->caption = new \Papaya\UI\Text\Translated('Export');
        $button->image = 'actions-download';
        $button->reference()->setParameters(
          array(
            'cmd' => 'set_export',
            'theme' => $themeName,
            'set_id' => $setId
          ),
          $this->parameterGroup()
        );
      }
    }
  }

  /**
   * Getter/Setter for the theme navigation listview
   *
   * It displays the list of Themes, the Sets of the selected theme and the pages of the
   * selected set.
   *
   * @param \Papaya\UI\Listview $listview
   * @return \Papaya\UI\Listview
   */
  public function listview(\Papaya\UI\Listview $listview = NULL) {
    if (isset($listview)) {
      $this->_listview = $listview;
    } elseif (NULL === $this->_listview) {
      $this->_listview = new \Papaya\UI\Listview();
      $this->_listview->caption = new \Papaya\UI\Text\Translated('Themes');
      $this->_listview->builder(
        $builder = new \Papaya\UI\Listview\Items\Builder(
          new \RecursiveIteratorIterator(
            $this->createThemeList(), \RecursiveIteratorIterator::SELF_FIRST
          )
        )
      );
      $this->_listview->builder()->callbacks()->onCreateItem = array($this, 'callbackCreateItem');
      $this->_listview->builder()->callbacks()->onCreateItem->context = $builder;
      $this->_listview->parameterGroup($this->parameterGroup());
      $this->_listview->parameters($this->parameters());
    }
    return $this->_listview;
  }

  /**
   * Get the Theme list for the listview. The result is an RecursiveIterator, the
   * sets of the selected theme are attached as children to the theme element
   *
   * If a set is selected, the value pages from the theme.xml are attached to the set
   *
   * @return \RecursiveIterator
   */
  private function createThemeList() {
    $themes = new \Papaya\Theme\Collection();
    $themes->papaya($this->papaya());
    $themeIterator = new \Papaya\Iterator\Tree\Items(
      $themes, \Papaya\Iterator\Tree\Items::ATTACH_TO_VALUES
    );
    $selectedTheme = $this->parameters()->get('theme', '');
    if (!empty($selectedTheme)) {
      $sets = new \Papaya\Content\Theme\Sets();
      $sets->activateLazyLoad(array('theme' => $selectedTheme));
      $setIterator = new \Papaya\Iterator\Tree\Items($sets);
      $selectedSet = $this->parameters()->get('set_id', 0);
      if ($selectedSet > 0) {
        $setIterator->attachItemIterator(
          $selectedSet,
          new \Papaya\Iterator\Generator(
            array($themes, 'getDefinition'),
            array($selectedTheme)
          )
        );
      }
      $themeIterator->attachItemIterator($selectedTheme, $setIterator);
    }
    return $themeIterator;
  }

  /**
   * Callback to create the items, depending on the depth here are the theme and set elements
   *
   * @param \Papaya\UI\Listview\Items\Builder $builder
   * @param \Papaya\UI\Listview\Items $items
   * @param mixed $element
   * @param mixed $index
   * @return null|\Papaya\UI\Listview\Item
   */
  public function callbackCreateItem($builder, $items, $element, $index) {
    /** @noinspection PhpUndefinedMethodInspection */
    switch ($builder->getDataSource()->getDepth()) {
      case 0 :
        $items[] = $item = $this->createThemeItem($element, $index);
        return $item;
      case 1 :
        $items[] = $item = $this->createSetItem($element, $index);
        return $item;
      case 2 :
        $items[] = $item = $this->createPageItem($element, $index);
        return $item;
    }
    return NULL;
  }

  /**
   * Create the listitem for a theme
   *
   * @param string $element
   * @return \Papaya\UI\Listview\Item
   */
  private function createThemeItem($element) {
    $item = new \Papaya\UI\Listview\Item('items-theme', (string)$element);
    $item->papaya($this->papaya());
    $item->reference->setParameters(
      array(
        'cmd' => 'theme_show',
        'theme' => $element
      ),
      $this->parameterGroup()
    );
    $item->selected = (
      !$this->parameters()->get('set_id', 0) &&
      $this->parameters()->get('theme', '') == $element
    );
    return $item;
  }

  /**
   * Create the listitem for a set
   *
   * @param array $element
   * @return \Papaya\UI\Listview\Item
   */
  private function createSetItem($element) {
    $item = new \Papaya\UI\Listview\Item('items-folder', (string)$element['title']);
    $item->papaya($this->papaya());
    $item->indentation = 1;
    $item->reference->setParameters(
      array(
        'cmd' => 'set_edit',
        'theme' => $element['theme'],
        'set_id' => $element['id']
      ),
      $this->parameterGroup()
    );
    $item->selected =
      ($this->parameters()->get('page_identifier', '') == '') &&
      $this->parameters()->get('set_id', 0) == $element['id'];
    return $item;
  }

  /**
   * Create the listitem for a theme values page
   *
   * @param \Papaya\Content\Structure\Page $element
   * @return \Papaya\UI\Listview\Item
   */
  private function createPageItem(\Papaya\Content\Structure\Page $element) {
    $item = new \Papaya\UI\Listview\Item('items-folder', (string)$element->title);
    $item->papaya($this->papaya());
    $item->indentation = 2;
    $item->reference->setParameters(
      array(
        'cmd' => 'values_edit',
        'theme' => $this->parameters()->get('theme', ''),
        'set_id' => $this->parameters()->get('set_id', 0),
        'page_identifier' => $element->getIdentifier()
      ),
      $this->parameterGroup()
    );
    $item->selected = $this->parameters()->get('page_identifier', '') == $element->getIdentifier();
    return $item;
  }
}
