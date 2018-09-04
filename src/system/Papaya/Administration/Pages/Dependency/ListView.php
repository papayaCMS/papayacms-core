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

namespace Papaya\Administration\Pages\Dependency;

/**
 * List view to show all dependencies for the specified origin page.
 *
 * @package Papaya-Library
 * @subpackage Administration
 */
class ListView extends \Papaya\UI\ListView {
  /**
   * Origin page id, this will be different from the current page id, if the current page id
   * is a dependency of this page id
   *
   * @var integer
   */
  private $_originPageId;
  /**
   * Current page id, this can be the page of an existing page dependency or of the selected page
   *
   * @var integer
   */
  private $_currentPageId;

  /**
   * List of database records
   *
   * @var \Papaya\Database\BaseObject\Records
   */
  private $_dependencies;

  /**
   * List of database records
   *
   * @var \Papaya\Content\Page\References
   */
  private $_references;

  /**
   * A list of the synchronization for the select field.
   *
   * @var Synchronizations
   */
  private $_synchronizations;

  /**
   * A pages list, to fetch page informations
   *
   * @var \Papaya\Content\Pages
   */
  private $_pages = NULL;

  public function __construct(
    $originPageId,
    $currentPageId,
    \Papaya\Content\Page\Dependencies $dependencies,
    \Papaya\Content\Page\References $references,
    Synchronizations $synchronizations
  ) {
    \Papaya\Utility\Constraints::assertInteger($originPageId);
    \Papaya\Utility\Constraints::assertInteger($currentPageId);
    $this->_originPageId = $originPageId;
    $this->_currentPageId = $currentPageId;
    $this->_dependencies = $dependencies;
    $this->_references = $references;
    $this->_synchronizations = $synchronizations;
  }

  /**
   * Prepare listview, set caption, create columns, items and subitems
   */
  private function prepare() {
    $pages = $this->pages();
    $pageTitle = isset($pages[$this->_originPageId])
      ? $pages[$this->_originPageId]['title'] : '[...]';
    $this->caption = new \Papaya\UI\Text\Translated(
      'Dependent pages of page "%s #%d"', array($pageTitle, $this->_originPageId)
    );
    $this->columns[] = new \Papaya\UI\ListView\Column(
      new \Papaya\UI\Text\Translated('Page')
    );
    $this->columns[] = new \Papaya\UI\ListView\Column(
      new \Papaya\UI\Text\Translated('GoTo'),
      \Papaya\UI\Option\Align::CENTER
    );
    $this->columns[] = new \Papaya\UI\ListView\Column(
      new \Papaya\UI\Text\Translated('Synchronization'),
      \Papaya\UI\Option\Align::CENTER
    );
    $this->columns[] = new \Papaya\UI\ListView\Column(
      new \Papaya\UI\Text\Translated('Modified'),
      \Papaya\UI\Option\Align::CENTER
    );
    if (count($this->_dependencies) > 0) {
      $this->items[] = $listitem = new \Papaya\UI\ListView\Item(
        'items-folder',
        new \Papaya\UI\Text\Translated('Dependencies')
      );
      $listitem->subitems[] = new \Papaya\UI\ListView\SubItem\Image(
        'actions-go-superior',
        new \Papaya\UI\Text\Translated('Go to origin page'),
        array('page_id' => $this->_originPageId)
      );
      $listitem->subitems[] = new \Papaya\UI\ListView\SubItem\Text('');
      $listitem->subitems[] = new \Papaya\UI\ListView\SubItem\Text('');
      foreach ($this->_dependencies as $dependency) {
        $this->items[] = $listitem = new \Papaya\UI\ListView\Item(
          'items-page',
          $dependency['title'].' #'.$dependency['id'],
          array('page_id' => $dependency['id'])
        );
        $listitem->indentation = 1;
        if (!empty($dependency['note'])) {
          $listitem->text = \Papaya\Utility\Text::truncate($dependency['note'], 60, TRUE);
        }
        $listitem->selected = $dependency['id'] == $this->_currentPageId;
        $listitem->subitems[] = new \Papaya\UI\ListView\SubItem\Text('');
        $listitem->subitems[] = new \Papaya\UI\ListView\SubItem\Images(
          $this->_synchronizations->getIcons(),
          $dependency['synchronization'],
          \Papaya\UI\ListView\SubItem\Images::VALIDATE_BITMASK
        );
        $listitem->subitems[] = new \Papaya\UI\ListView\SubItem\Date(
          (int)$dependency['modified']
        );
      }
    }
    if (count($this->_references) > 0) {
      $this->items[] = $listitem = new \Papaya\UI\ListView\Item(
        'items-folder',
        new \Papaya\UI\Text\Translated('References')
      );
      $listitem->columnSpan = -1;
      foreach ($this->_references as $reference) {
        $this->items[] = $listitem = new \Papaya\UI\ListView\Item(
          'items-link',
          $reference['title'].' #'.$reference['target_id'],
          array(
            'page_id' => $reference['source_id'],
            'target_id' => $reference['target_id'],
            'cmd' => 'reference_change'
          )
        );
        $listitem->indentation = 1;
        if (!empty($reference['note'])) {
          $listitem->text = \Papaya\Utility\Text::truncate($reference['note'], 60, TRUE);
        }
        $listitem->selected = in_array(
          $this->parameters()->get('target_id'),
          array($reference['source_id'], $reference['target_id'])
        );
        $listitem->subitems[] = new \Papaya\UI\ListView\SubItem\Image(
          'items-page',
          new \Papaya\UI\Text\Translated(
            'Go to page %s #%d', array($reference['title'], $reference['target_id'])
          ),
          array(
            'page_id' => $reference['target_id'],
            'target_id' => $reference['source_id'],
            'cmd' => 'reference_change'
          )
        );
        $listitem->subitems[] = new \Papaya\UI\ListView\SubItem\Text('');
        $listitem->subitems[] = new \Papaya\UI\ListView\SubItem\Date(
          (int)$reference['modified']
        );
      }
    }
  }

  /**
   * Append listview to parent element if it has records.
   *
   * @param \Papaya\XML\Element $parent
   * @return NULL|\Papaya\XML\Element
   */
  public function appendTo(\Papaya\XML\Element $parent) {
    if (count($this->_dependencies) > 0 || count($this->_references) > 0) {
      $this->prepare();
      return parent::appendTo($parent);
    }
    return NULL;
  }

  /**
   * Access to the pages list, to load page informations
   *
   * @param \Papaya\Content\Pages $pages
   * @return \Papaya\Content\Pages
   */
  public function pages(\Papaya\Content\Pages $pages = NULL) {
    if (isset($pages)) {
      $this->_pages = $pages;
    } elseif (is_null($this->_pages)) {
      $this->_pages = new \Papaya\Content\Pages();
      $this->_pages->papaya($this->papaya());
    }
    return $this->_pages;
  }
}