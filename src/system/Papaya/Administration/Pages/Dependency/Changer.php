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

use Papaya\Administration;
use Papaya\Content;
use Papaya\Filter;
use Papaya\UI;
use Papaya\XML;

/**
 * Administration interface for changes on the dependencies of a page.
 *
 * @package Papaya-Library
 * @subpackage Administration
 */
class Changer extends UI\Control\Interactive {
  /**
   * Currently selected page
   *
   * @var int
   */
  private $_pageId = 0;

  /**
   * Currently selected origin page, this will be the origin page of the current dependency or
   * the current page id.
   *
   * @var int
   */
  private $_originId = 0;

  /**
   * Target page id of the reference to load.
   *
   * @var int
   */
  private $_targetId = 0;

  /**
   * Buffer variable for the current dependency
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
   * @var Content\Page\Reference $_reference
   */
  private $_reference;

  /**
   * @var Content\Page\References $_references
   */
  private $_references;

  /**
   * Command controller for the needed actions
   *
   * @var UI\Control\Command\Controller
   */
  private $_commands;

  /**
   * Menu object, for buttons depending on the current status
   *
   * @var UI\Toolbar
   */
  private $_menu;

  /**
   * Dependencies listview
   *
   * @var ListView
   */
  private $_listview;

  /**
   * Dependencies synchronization information
   *
   * @var Synchronizations
   */
  private $_synchronizations;

  /**
   * Return current page id
   *
   * @return int
   */
  public function getPageId() {
    return $this->_pageId;
  }

  /**
   * Return current origin page id
   *
   * @return int
   */
  public function getOriginId() {
    return $this->_originId;
  }

  /**
   * Getter/Setter for the dependency database object
   *
   * @param Content\Page\Dependency $dependency
   * @return Content\Page\Dependency
   */
  public function dependency(Content\Page\Dependency $dependency = NULL) {
    if (NULL !== $dependency) {
      $this->_dependency = $dependency;
    } elseif (NULL === $this->_dependency) {
      $this->_dependency = new Content\Page\Dependency();
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
   * Getter/Setter for the reference database object
   *
   * @param Content\Page\Reference $reference
   * @return Content\Page\Reference
   */
  public function reference(Content\Page\Reference $reference = NULL) {
    if (NULL !== $reference) {
      $this->_reference = $reference;
    } elseif (NULL === $this->_reference) {
      $this->_reference = new Content\Page\Reference();
    }
    return $this->_reference;
  }

  /**
   * Getter/Setter for the references list database object
   *
   * @param Content\Page\References $references
   * @return Content\Page\References
   */
  public function references(Content\Page\References $references = NULL) {
    if (NULL !== $references) {
      $this->_references = $references;
    } elseif (NULL === $this->_references) {
      $this->_references = new Content\Page\References();
    }
    return $this->_references;
  }

  /**
   * Execute commands and append output to xml.
   *
   * @param XML\Element $parent
   */
  public function appendTo(XML\Element $parent) {
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
        [
          'id' => $this->getOriginId(),
          'language_id' => $this->papaya()->administrationLanguage->getCurrent()->id
        ]
      );
      $this->listview()->parameterGroup($this->parameterGroup());
      $this->listview()->appendTo($parent);
    }
  }

  /**
   * Initialize parameters and store them into properties.
   */
  public function prepare() {
    $this->_pageId = (int)$this->parameters()->get('page_id', 0, new Filter\IntegerValue(0));
    if ($this->_pageId > 0) {
      if ($this->dependency()->load($this->_pageId)) {
        $this->_originId = (int)$this->dependency()->originId;
      } elseif ($this->dependency()->isOrigin($this->_pageId)) {
        $this->_originId = $this->_pageId;
      }
      $this->_targetId = (int)$this->parameters()->get('target_id', 0, new Filter\IntegerValue(0));
      if ($this->_targetId > 0) {
        $this->reference()->load(
          ['source_id' => $this->_pageId, 'target_id' => $this->_targetId]
        );
      }
    }
  }

  /**
   * Getter/Setter for commands, define commands on implicit create.
   *
   * @param UI\Control\Command\Controller $commands
   * @return UI\Control\Command\Controller
   */
  public function commands(UI\Control\Command\Controller $commands = NULL) {
    if (NULL !== $commands) {
      $this->_commands = $commands;
    } elseif (NULL === $this->_commands) {
      $commands = new UI\Control\Command\Controller('cmd', 'dependency_show');
      $commands->owner($this);
      $commands['dependency_show'] = new Command\Change();
      $commands['dependency_delete'] = new Command\Delete();
      $commands['reference_change'] = new Administration\Pages\Reference\Command\Change();
      $commands['reference_delete'] = new Administration\Pages\Reference\Command\Delete();
      $this->_commands = $commands;
    }
    return $this->_commands;
  }

  /**
   * Getter/Setter for the menu (action/command buttons)
   *
   * @param UI\Toolbar $menu
   * @return UI\Toolbar
   */
  public function menu(UI\Toolbar $menu = NULL) {
    if (NULL !== $menu) {
      $this->_menu = $menu;
    } elseif (NULL === $this->_menu) {
      $this->_menu = new UI\Toolbar();
    }
    return $this->_menu;
  }

  /**
   * Append buttons to menu/toolbar depending on the current status.
   */
  private function appendButtons() {
    if (\in_array($this->parameters()->get('cmd'), ['reference_change', 'reference_delete'])) {
      $this->menu()->elements[] = $button = new UI\Toolbar\Button();
      $button->image = 'status-page-modified';
      $button->caption = new UI\Text\Translated('Edit dependency');
      $button->reference->setParameters(
        ['cmd' => 'dependency_change', 'page_id' => $this->_pageId],
        $this->parameterGroup()
      );
    }
    if ($this->dependency()->id > 0) {
      $this->menu()->elements[] = $button = new UI\Toolbar\Button();
      $button->image = 'actions-page-delete';
      $button->caption = new UI\Text\Translated('Delete dependency');
      $button->reference->setParameters(
        ['cmd' => 'dependency_delete', 'page_id' => $this->_pageId],
        $this->parameterGroup()
      );
    }
    $this->menu()->elements[] = new UI\Toolbar\Separator();
    $this->menu()->elements[] = $button = new UI\Toolbar\Button();
    $button->image = 'actions-link-add';
    $button->caption = new UI\Text\Translated('Add reference');
    $button->reference->setParameters(
      ['cmd' => 'reference_change', 'page_id' => $this->_pageId, 'target_id' => 0],
      $this->parameterGroup()
    );
    if ($this->reference()->sourceId > 0 && $this->reference()->targetId > 0) {
      $this->menu()->elements[] = $button = new UI\Toolbar\Button();
      $button->image = 'actions-link-delete';
      $button->caption = new UI\Text\Translated('Delete reference');
      $button->reference->setParameters(
        [
          'cmd' => 'reference_delete',
          'page_id' => $this->_pageId,
          'target_id' => (int)$this->reference()->sourceId === $this->_pageId
            ? $this->reference()->targetId : $this->reference()->sourceId
        ],
        $this->parameterGroup()
      );
    }
  }

  /**
   * Getter/Setter for the dependencies listview.
   *
   * @param ListView $listview
   * @return ListView
   */
  public function listview(ListView $listview = NULL) {
    if (NULL !== $listview) {
      $this->_listview = $listview;
    } elseif (NULL === $this->_listview) {
      $this->_listview = new ListView(
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
   * @param Synchronizations $synchronizations
   * @return Synchronizations
   */
  public function synchronizations(Synchronizations $synchronizations = NULL) {
    if (NULL !== $synchronizations) {
      $this->_synchronizations = $synchronizations;
    }
    if (NULL === $this->_synchronizations) {
      $this->_synchronizations = new Synchronizations();
    }
    return $this->_synchronizations;
  }
}
