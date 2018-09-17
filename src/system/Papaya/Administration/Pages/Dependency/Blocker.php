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

use \Papaya\Content;
use \Papaya\UI;
use \Papaya\XML;

/**
 * Check if the current page is a dependency and block edit page if it is set to sync.
 *
 * Show a information dialog if the synchronisation for this part is activated.
 *
 * @package Papaya-Library
 * @subpackage Administration
 */
class Blocker extends UI\Control\Interactive {

  /**
   * current page id
   *
   * @var integer
   */
  private $_pageId;

  /**
   * Cached synchronized result
   *
   * @var NULL|array
   */
  private $_synchronized;

  /**
   * Dependency content object buffer
   *
   * @var Content\Page\Dependency
   */
  private $_dependency;

  /**
   * Buffer variable for the dependencies list of the current origin id
   *
   * @var Content\Page\Dependencies
   */
  private $_dependencies;

  /**
   * Buffer variable for the views list
   *
   * @var Content\Views
   */
  private $_views;

  /**
   * Page information content buffer
   *
   * @var Content\Pages
   */
  private $_pages;

  /**
   * Dependency/Reference counter
   *
   * @var Counter
   */
  private $_counter;

  /**
   * Initialize object with page id and synchronisation element.
   *
   * @param integer $pageId
   * @internal param int $synchronization
   */
  public function __construct($pageId) {
    $this->_pageId = $pageId;
  }

  /**
   * Append the blocker message/goto dialog to the parent xml.
   *
   * @param XML\Element $parent
   * @return UI\Dialog
   */
  public function appendTo(XML\Element $parent) {
    $pageId = $this->dependency()->originId;
    $pages = $this->pages();
    $pages->load(
      array(
        'id' => $pageId,
        'language_id' => $this->papaya()->administrationLanguage->getCurrent()->id
      )
    );
    $pageTitle = isset($pages[$pageId])
      ? $pages[$pageId]['title'] : '[...]';

    $dialog = new UI\Dialog();
    $dialog->papaya($this->papaya());
    $dialog->caption = new UI\Text\Translated('Page dependency');
    $dialog->parameterGroup($this->parameterGroup());
    $dialog->options->useToken = FALSE;
    $dialog->hiddenFields->merge(
      array(
        'page_id' => $pageId
      )
    );
    $dialog->fields[] = new UI\Dialog\Field\Information(
      new UI\Text\Translated(
        'This part of the page is synchronized with page "%s #%d".',
        array($pageTitle, $pageId)
      ),
      'status-system-locked'
    );
    $dialog->buttons[] = new UI\Dialog\Button\Submit(
      new UI\Text\Translated('GoTo Origin Page')
    );
    $dialog->appendTo($parent);
    return $dialog;
  }

  /**
   * Check if the given synchronization is active. It will cache the results.
   *
   * @param integer $synchronization
   * @param boolean $reset , reset cache and load record again
   * @return boolean
   */
  public function isSynchronized($synchronization, $reset = FALSE) {
    $this->prepare($synchronization, $reset);
    return $this->_synchronized[$synchronization];
  }

  /**
   * Load dependency information for current clone if needed, store sync status if
   * asked for.
   *
   * @param integer $synchronization
   * @param bool $reset
   */
  private function prepare($synchronization = NULL, $reset = FALSE) {
    if (NULL === $this->_synchronized || $reset) {
      $this->_synchronized = array();
      $this->dependency()->load($this->_pageId);
    }
    if (
      NULL !== $synchronization &&
      !isset($this->_synchronized[$synchronization])
    ) {
      $this->_synchronized[$synchronization] =
        (bool)($this->dependency()->synchronization & $synchronization);
    }
  }

  /**
   * Return all the views of dependend pages that syn either only the view xor the content.
   *
   * @param integer $language
   * @param boolean $reset , reset cache and load record again
   * @return array
   */
  public function getSynchronizedViews($language, $reset = FALSE) {
    $result = array();
    $this->prepare(NULL, $reset);
    if ($this->dependency()->isOrigin($this->_pageId) &&
      $this->dependencies()->load($this->_pageId, $language)) {
      $viewIds = array();
      foreach ($this->dependencies() as $dependency) {
        if (($dependency['synchronization'] & Content\Page\Dependency::SYNC_VIEW) xor
          ($dependency['synchronization'] & Content\Page\Dependency::SYNC_CONTENT)) {
          $viewIds[$dependency['id']] = $dependency['view_id'];
        }
      }
      $views = $this->views();
      $views->load(array('id' => \array_values($viewIds)));
      foreach ($viewIds as $pageId => $viewId) {
        if (isset($views[$viewId])) {
          $result[$pageId] = $views[$viewId];
        }
      }
    }
    return $result;
  }

  /**
   * Get/Set an object for the current dependency.
   *
   * @param Content\Page\Dependency $dependency
   * @return Content\Page\Dependency
   */
  public function dependency(Content\Page\Dependency $dependency = NULL) {
    if (NULL !== $dependency) {
      $this->_dependency = $dependency;
    } elseif (NULL === $this->_dependency) {
      $this->_dependency = new Content\Page\Dependency();
      $this->_dependency->papaya($this->papaya());
    }
    return $this->_dependency;
  }

  /**
   * Getter/Setter for the dependencies list database object
   *
   * @param Content\Page\Dependencies $dependencies
   * @return Content\Page\Dependencies
   */
  public function dependencies(Content\Page\Dependencies $dependencies = NULL) {
    if (NULL !== $dependencies) {
      $this->_dependencies = $dependencies;
    } elseif (NULL === $this->_dependencies) {
      $this->_dependencies = new Content\Page\Dependencies();
    }
    return $this->_dependencies;
  }

  /**
   * Getter/Setter for the views list database object
   *
   * @param Content\Views $views
   * @return Content\Views
   */
  public function views(Content\Views $views = NULL) {
    if (NULL !== $views) {
      $this->_views = $views;
    } elseif (NULL === $this->_views) {
      $this->_views = new Content\Views();
    }
    return $this->_views;
  }

  /**
   * Access to the pages list, to load page information
   *
   * @param Content\Pages $pages
   * @return Content\Pages
   */
  public function pages(Content\Pages $pages = NULL) {
    if (NULL !== $pages) {
      $this->_pages = $pages;
    } elseif (NULL === $this->_pages) {
      $this->_pages = new Content\Pages();
      $this->_pages->papaya($this->papaya());
    }
    return $this->_pages;
  }

  /**
   * Provides count for depended pages and references
   *
   * @param Counter $counter
   * @return Counter
   */
  public function counter(Counter $counter = NULL) {
    if (NULL !== $counter) {
      $this->_counter = $counter;
    } elseif (NULL === $this->_counter) {
      $this->_counter = new Counter($this->_pageId);
      $this->_counter->papaya($this->papaya());
    }
    return $this->_counter;
  }
}
