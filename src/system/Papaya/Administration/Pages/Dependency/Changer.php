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
use Papaya\Administration\Pages\Dependency\ListView;
use Papaya\Administration\Pages\Dependency\Synchronizations;
use Papaya\Content\Page\Dependencies;
use Papaya\Content\Page\Dependency;
use Papaya\UI\Control\Command\Controller;
use Papaya\UI\Toolbar;

/**
 * Administration interface for changes on the dependencies of a page.
 *
 * @package Papaya-Library
 * @subpackage Administration
 */
class Changer extends \Papaya\UI\Control\Interactive {

  /**
   * Currently selected page
   *
   * @var integer
   */
  private $_pageId = 0;

  /**
   * Currently selected origin page, this will be the origin page of the current dependency or
   * the current page id.
   *
   * @var integer
   */
  private $_originId = 0;

  /**
   * Target page id of the reference to load.
   *
   * @var integer
   */
  private $_targetId = 0;

  /**
   * Buffer variable for the current dependency
   *
   * @var \Papaya\Content\Page\Dependency
   */
  private $_dependency = NULL;

  /**
   * Buffer variable for the dependencies list of the current origin id
   *
   * @var Dependencies
   */
  private $_dependencies = NULL;

  private $_reference = NULL;

  private $_references = NULL;

  /**
   * Command controller for the needed actions
   *
   * @var \Papaya\UI\Control\Command\Controller
   */
  private $_commands = NULL;

  /**
   * Menu object, for buttons depending on the current status
   *
   * @var \Papaya\UI\Toolbar
   */
  private $_menu = NULL;

  /**
   * Dependencies listview
   *
   * @var ListView
   */
  private $_listview = NULL;

  /**
   * Dependencies synchronization informations
   *
   * @var Synchronizations
   */
  private $_synchronizations = NULL;

  /**
   * Return current page id
   *
   * @return integer
   */
  public function getPageId() {
    return $this->_pageId;
  }

  /**
   * Return current origin page id
   *
   * @return integer
   */
  public function getOriginId() {
    return $this->_originId;
  }

  /**
   * Getter/Setter for the dependency database object
   *
   * @param \Papaya\Content\Page\Dependency $dependency
   * @return \Papaya\Content\Page\Dependency
   */
  public function dependency(\Papaya\Content\Page\Dependency $dependency = NULL) {
    if (isset($dependency)) {
      $this->_dependency = $dependency;
    } elseif (is_null($this->_dependency)) {
      $this->_dependency = new \Papaya\Content\Page\Dependency();
    }
    return $this->_dependency;
  }

  /**
   * Getter/Setter for the dependencies list database object
   *
   * @param \Papaya\Content\Page\Dependencies $dependencies
   * @return \Papaya\Content\Page\Dependencies
   */
  public function dependencies(\Papaya\Content\Page\Dependencies $dependencies = NULL) {
    if (isset($dependencies)) {
      $this->_dependencies = $dependencies;
    } elseif (is_null($this->_dependencies)) {
      $this->_dependencies = new \Papaya\Content\Page\Dependencies();
    }
    return $this->_dependencies;
  }

  /**
   * Getter/Setter for the reference database object
   *
   * @param \Papaya\Content\Page\Reference $reference
   * @return \Papaya\Content\Page\Reference
   */
  public function reference(\Papaya\Content\Page\Reference $reference = NULL) {
    if (isset($reference)) {
      $this->_reference = $reference;
    } elseif (is_null($this->_reference)) {
      $this->_reference = new \Papaya\Content\Page\Reference();
    }
    return $this->_reference;
  }

  /**
   * Getter/Setter for the references list database object
   *
   * @param \Papaya\Content\Page\References $references
   * @return \Papaya\Content\Page\References
   */
  public function references(\Papaya\Content\Page\References $references = NULL) {
    if (isset($references)) {
      $this->_references = $references;
    } elseif (is_null($this->_references)) {
      $this->_references = new \Papaya\Content\Page\References();
    }
    return $this->_references;
  }

  /**
   * Execute commands and append output to xml.
   *
   * @param \Papaya\XML\Element $parent
   */
  public function appendTo(\Papaya\XML\Element $parent) {
    $this->prepare();
    if ($this->getPageId() > 0) {
      $this->appendButtons();
      $this->commands()->appendTo($parent);
      if ($this->getOriginId() > 0) {
        $this->dependencies()->load(
          $this->_originId, $this->papaya()->administrationLanguage->getCurrent()->id
        );
      }
      $this->references()->load(
        $this->_pageId, $this->papaya()->administrationLanguage->getCurrent()->id
      );
      $this->listview()->pages()->load(
        array(
          'id' => $this->getOriginId(),
          'language_id' => $this->papaya()->administrationLanguage->getCurrent()->id
        )
      );
      $this->listview()->parameterGroup($this->parameterGroup());
      $this->listview()->appendTo($parent);
    }
  }

  /**
   * Initialize parameters and store them into properties.
   */
  public function prepare() {
    $this->_pageId = $this->parameters()->get('page_id', 0, new \Papaya\Filter\IntegerValue(0));
    if ($this->_pageId > 0) {
      if ($this->dependency()->load($this->_pageId)) {
        $this->_originId = (int)$this->dependency()->originId;
      } elseif ($this->dependency()->isOrigin($this->_pageId)) {
        $this->_originId = (int)$this->_pageId;
      }
      $this->_targetId = $this->parameters()->get('target_id', 0, new \Papaya\Filter\IntegerValue(0));
      if ($this->_targetId > 0) {
        $this->reference()->load(
          array('source_id' => $this->_pageId, 'target_id' => $this->_targetId)
        );
      }
    }
  }

  /**
   * Getter/Setter for commands, define commands on implicit create.
   *
   * @param \Papaya\UI\Control\Command\Controller $commands
   * @return \Papaya\UI\Control\Command\Controller
   */
  public function commands(\Papaya\UI\Control\Command\Controller $commands = NULL) {
    if (isset($commands)) {
      $this->_commands = $commands;
    } elseif (is_null($this->_commands)) {
      $commands = new \Papaya\UI\Control\Command\Controller('cmd', 'dependency_show');
      $commands->owner($this);
      $commands['dependency_show'] = new \Papaya\Administration\Pages\Dependency\Command\Change();
      $commands['dependency_delete'] = new \Papaya\Administration\Pages\Dependency\Command\Delete();
      $commands['reference_change'] = new \Papaya\Administration\Pages\Reference\Command\Change();
      $commands['reference_delete'] = new \Papaya\Administration\Pages\Reference\Command\Delete();
      $this->_commands = $commands;
    }
    return $this->_commands;
  }

  /**
   * Getter/Setter for the menu (action/command buttons)
   *
   * @param \Papaya\UI\Toolbar $menu
   * @return \Papaya\UI\Toolbar
   */
  public function menu(\Papaya\UI\Toolbar $menu = NULL) {
    if (isset($menu)) {
      $this->_menu = $menu;
    } elseif (is_null($this->_menu)) {
      $this->_menu = new \Papaya\UI\Toolbar();
    }
    return $this->_menu;
  }

  /**
   * Append buttons to menu/toolbar depending on the current status.
   */
  private function appendButtons() {
    if (in_array($this->parameters()->get('cmd'), array('reference_change', 'reference_delete'))) {
      $this->menu()->elements[] = $button = new \Papaya\UI\Toolbar\Button();
      $button->image = 'status-page-modified';
      $button->caption = new \Papaya\UI\Text\Translated('Edit dependency');
      $button->reference->setParameters(
        array('cmd' => 'dependency_change', 'page_id' => $this->_pageId),
        $this->parameterGroup()
      );
    }
    if ($this->dependency()->id > 0) {
      $this->menu()->elements[] = $button = new \Papaya\UI\Toolbar\Button();
      $button->image = 'actions-page-delete';
      $button->caption = new \Papaya\UI\Text\Translated('Delete dependency');
      $button->reference->setParameters(
        array('cmd' => 'dependency_delete', 'page_id' => $this->_pageId),
        $this->parameterGroup()
      );
    }
    $this->menu()->elements[] = new \Papaya\UI\Toolbar\Separator();
    $this->menu()->elements[] = $button = new \Papaya\UI\Toolbar\Button();
    $button->image = 'actions-link-add';
    $button->caption = new \Papaya\UI\Text\Translated('Add reference');
    $button->reference->setParameters(
      array('cmd' => 'reference_change', 'page_id' => $this->_pageId, 'target_id' => 0),
      $this->parameterGroup()
    );
    if ($this->reference()->sourceId > 0 && $this->reference()->targetId > 0) {
      $this->menu()->elements[] = $button = new \Papaya\UI\Toolbar\Button();
      $button->image = 'actions-link-delete';
      $button->caption = new \Papaya\UI\Text\Translated('Delete reference');
      $button->reference->setParameters(
        array(
          'cmd' => 'reference_delete',
          'page_id' => $this->_pageId,
          'target_id' => $this->reference()->sourceId == $this->_pageId
            ? $this->reference()->targetId : $this->reference()->sourceId
        ),
        $this->parameterGroup()
      );
    }
  }

  /**
   * Getter/Setter for the dependencies listview.
   *
   * @param \Papaya\Administration\Pages\Dependency\ListView $listview
   * @return \Papaya\Administration\Pages\Dependency\ListView
   */
  public function listview(\Papaya\Administration\Pages\Dependency\ListView $listview = NULL) {
    if (isset($listview)) {
      $this->_listview = $listview;
    } elseif (is_null($this->_listview)) {
      $this->_listview = new \Papaya\Administration\Pages\Dependency\ListView(
        $this->getOriginId(),
        $this->getPageId(),
        $this->dependencies(),
        $this->references(),
        $this->synchronizations()
      );
    }
    return $this->_listview;
  }

  /**
   * Getter/Setter for the synchronizations list
   *
   * @param \Papaya\Administration\Pages\Dependency\Synchronizations $synchronizations
   * @return \Papaya\Administration\Pages\Dependency\Synchronizations
   */
  public function synchronizations(
    \Papaya\Administration\Pages\Dependency\Synchronizations $synchronizations = NULL
  ) {
    if (isset($synchronizations)) {
      $this->_synchronizations = $synchronizations;
    }
    if (is_null($this->_synchronizations)) {
      $this->_synchronizations = new \Papaya\Administration\Pages\Dependency\Synchronizations();
    }
    return $this->_synchronizations;
  }
}
