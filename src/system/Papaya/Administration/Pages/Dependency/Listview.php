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

/**
* Listview to show all dependencies for the specified origin page.
*
* @package Papaya-Library
* @subpackage Administration
*/
class PapayaAdministrationPagesDependencyListview extends PapayaUiListview {
  /**
  * Origin page id, this will be different from the current page id, if the current page id
  * is a dependency of this page id
  *
  * @var integer
  */
  private $_originPageId = 0;
  /**
  * Current page id, this can be the page of an existing page dependency or of the selected page
  *
  * @var integer
  */
  private $_currentPageId = 0;

  /**
  * List of database records
  *
  * @var PapayaDatabaseObjectList
  */
  private $_dependencies = 0;

  /**
  * List of database records
  *
  * @var PapayaContentPageReferences
  */
  private $_references = 0;

  /**
  * A list of the synchronization for the select field.
  *
  * @var PapayaAdministrationPagesDependencySynchronizations
  */
  private $_synchronizations = NULL;

  /**
  * A pages list, to fetch page informations
  *
  * @var PapayaContentPages
  */
  private $_pages = NULL;

  public function __construct(
    $originPageId,
    $currentPageId,
    PapayaContentPageDependencies $dependencies,
    PapayaContentPageReferences $references,
    PapayaAdministrationPagesDependencySynchronizations $synchronizations
  ) {
    PapayaUtilConstraints::assertInteger($originPageId);
    PapayaUtilConstraints::assertInteger($currentPageId);
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
    $this->caption = new \PapayaUiStringTranslated(
      'Dependent pages of page "%s #%d"', array($pageTitle, $this->_originPageId)
    );
    $this->columns[] = new \PapayaUiListviewColumn(
      new \PapayaUiStringTranslated('Page')
    );
    $this->columns[] = new \PapayaUiListviewColumn(
      new \PapayaUiStringTranslated('GoTo'),
      PapayaUiOptionAlign::CENTER
    );
    $this->columns[] = new \PapayaUiListviewColumn(
      new \PapayaUiStringTranslated('Synchronization'),
      PapayaUiOptionAlign::CENTER
    );
    $this->columns[] = new \PapayaUiListviewColumn(
      new \PapayaUiStringTranslated('Modified'),
      PapayaUiOptionAlign::CENTER
    );
    if (count($this->_dependencies) > 0) {
      $this->items[] = $listitem = new \PapayaUiListviewItem(
        'items-folder',
        new \PapayaUiStringTranslated('Dependencies')
      );
      $listitem->subitems[] = new \PapayaUiListviewSubitemImage(
        'actions-go-superior',
        new \PapayaUiStringTranslated('Go to origin page'),
        array('page_id' => $this->_originPageId)
      );
      $listitem->subitems[] = new \PapayaUiListviewSubitemText('');
      $listitem->subitems[] = new \PapayaUiListviewSubitemText('');
      foreach ($this->_dependencies as $dependency) {
        $this->items[] = $listitem = new \PapayaUiListviewItem(
          'items-page',
          $dependency['title'].' #'.$dependency['id'],
          array('page_id' => $dependency['id'])
        );
        $listitem->indentation = 1;
        if (!empty($dependency['note'])) {
          $listitem->text = PapayaUtilString::truncate($dependency['note'], 60, TRUE);
        }
        $listitem->selected = $dependency['id'] == $this->_currentPageId;
        $listitem->subitems[] = new \PapayaUiListviewSubitemText('');
        $listitem->subitems[] = new \PapayaUiListviewSubitemImageList(
          $this->_synchronizations->getIcons(),
          $dependency['synchronization'],
          PapayaUiListviewSubitemImageList::VALIDATE_BITMASK
        );
        $listitem->subitems[] = new \PapayaUiListviewSubitemDate(
          (int)$dependency['modified']
        );
      }
    }
    if (count($this->_references) > 0) {
      $this->items[] = $listitem = new \PapayaUiListviewItem(
        'items-folder',
        new \PapayaUiStringTranslated('References')
      );
      $listitem->columnSpan = -1;
      foreach ($this->_references as $reference) {
        $this->items[] = $listitem = new \PapayaUiListviewItem(
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
          $listitem->text = PapayaUtilString::truncate($reference['note'], 60, TRUE);
        }
        $listitem->selected = in_array(
          $this->parameters()->get('target_id'),
          array($reference['source_id'], $reference['target_id'])
        );
        $listitem->subitems[] = new \PapayaUiListviewSubitemImage(
          'items-page',
          new \PapayaUiStringTranslated(
            'Go to page %s #%d', array($reference['title'], $reference['target_id'])
          ),
          array(
            'page_id' => $reference['target_id'],
            'target_id' => $reference['source_id'],
            'cmd' => 'reference_change'
          )
        );
        $listitem->subitems[] = new \PapayaUiListviewSubitemText('');
        $listitem->subitems[] = new \PapayaUiListviewSubitemDate(
          (int)$reference['modified']
        );
      }
    }
  }

  /**
  * Append listview to parent element if it has records.
  *
  * @param PapayaXmlElement $parent
  * @return NULL|PapayaXmlElement
  */
  public function appendTo(PapayaXmlElement $parent) {
    if (count($this->_dependencies) > 0 || count($this->_references) > 0) {
      $this->prepare();
      return parent::appendTo($parent);
    }
    return NULL;
  }

  /**
  * Access to the pages list, to load page informations
  *
  * @param PapayaContentPages $pages
  * @return PapayaContentPages
  */
  public function pages(PapayaContentPages $pages = NULL) {
    if (isset($pages)) {
      $this->_pages = $pages;
    } elseif (is_null($this->_pages)) {
      $this->_pages = new \PapayaContentPages();
      $this->_pages->papaya($this->papaya());
    }
    return $this->_pages;
  }
}
