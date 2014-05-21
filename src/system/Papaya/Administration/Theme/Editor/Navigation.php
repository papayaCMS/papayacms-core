<?php
/**
* Navigation part of the theme sets editor (dynamic values for a theme)
*
* @copyright 2011 by papaya Software GmbH - All rights reserved.
* @link http://www.papaya-cms.com/
* @license http://www.gnu.org/licenses/old-licenses/gpl-2.0.html GNU General Public License, version 2
*
* You can redistribute and/or modify this script under the terms of the GNU General Public
* License (GPL) version 2, provided that the copyright and license notes, including these
* lines, remain unmodified. papaya is distributed in the hope that it will be useful, but
* WITHOUT ANY WARRANTY; without even the implied warranty of MERCHANTABILITY or FITNESS
* FOR A PARTICULAR PURPOSE.
*
* @package Papaya-Library
* @subpackage Administration
* @version $Id: Navigation.php 39429 2014-02-27 20:14:26Z weinert $
*/

/**
* Navigation part of the theme sets editor (dynamic values for a theme)
*
* @package Papaya-Library
* @subpackage Administration
*/
class PapayaAdministrationThemeEditorNavigation extends PapayaAdministrationPagePart {

  /**
   * @var PapayaUiListview
   */
  private $_listview = NULL;

  /**
  * Append navigation to parent xml element
  *
  * @param PapayaXmlElement $parent
  */
  public function appendTo(PapayaXmlElement $parent) {
    $parent->append($this->listview());
    if ('' != ($themeName = $this->parameters()->get('theme', ''))) {
      $setId = $this->parameters()->get('set_id', 0);
      $this->toolbar()->elements[] = $button = new PapayaUiToolbarButton();
      $button->caption = new PapayaUiStringTranslated('Add set');
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
        $this->toolbar()->elements[] = $button = new PapayaUiToolbarButton();
        $button->caption = new PapayaUiStringTranslated('Delete set');
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
      $this->toolbar()->elements[] = $button = new PapayaUiToolbarButton();
      $button->caption = new PapayaUiStringTranslated('Import');
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
        $this->toolbar()->elements[] = $button = new PapayaUiToolbarButton();
        $button->caption = new PapayaUiStringTranslated('Export');
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
   * @param PapayaUiListview $listview
   * @return PapayaUiListview
   */
  public function listview(PapayaUiListview $listview = NULL) {
    if (isset($listview)) {
      $this->_listview = $listview;
    } elseif (NULL === $this->_listview) {
      $this->_listview = new PapayaUiListview();
      $this->_listview->caption = new PapayaUiStringTranslated('Themes');
      $this->_listview->builder(
        $builder = new PapayaUiListviewItemsBuilder(
          new RecursiveIteratorIterator(
            $this->createThemeList(), RecursiveIteratorIterator::SELF_FIRST
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
   * @return RecursiveIterator
   */
  private function createThemeList() {
    $themes = new PapayaThemeList();
    $themes->papaya($this->papaya());
    $themeIterator = new PapayaIteratorTreeItems(
      $themes, PapayaIteratorTreeItems::ATTACH_TO_VALUES
    );
    $selectedTheme = $this->parameters()->get('theme', '');
    if (!empty($selectedTheme)) {
      $sets = new PapayaContentThemeSets();
      $sets->activateLazyLoad(array('theme' => $selectedTheme));
      $setIterator = new PapayaIteratorTreeItems($sets);
      $selectedSet = $this->parameters()->get('set_id', 0);
      if ($selectedSet > 0) {
        $setIterator->attachItemIterator(
          $selectedSet,
          new PapayaIteratorGenerator(
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
   * @param PapayaUiListviewItemsBuilder $builder
   * @param PapayaUiListviewItems $items
   * @param mixed $element
   * @param mixed $index
   * @return null|PapayaUiListviewItem
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
   * @return PapayaUiListviewItem
   */
  private function createThemeItem($element) {
    $item = new PapayaUiListviewItem('items-theme', (string)$element);
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
   * @return PapayaUiListviewItem
   */
  private function createSetItem($element) {
    $item = new PapayaUiListviewItem('items-folder', (string)$element['title']);
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
   * @param PapayaContentStructurePage $element
   * @return PapayaUiListviewItem
   */
  private function createPageItem(PapayaContentStructurePage $element) {
    $item = new PapayaUiListviewItem('items-folder', (string)$element->title);
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