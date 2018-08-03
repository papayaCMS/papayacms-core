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
 * Encapsulate the synchronization definitions and provide access in different formats for other
 * object.
 *
 * @package Papaya-Library
 * @subpackage Administration
 */
class Synchronizations {

  /**
   * Synchronization definitions
   *
   * @var array
   */
  private $_definitions = array(
    \Papaya\Content\Page\Dependency::SYNC_PROPERTIES => array(
      'caption' => 'Properties',
      'hint' => 'Page properties',
      'image' => 'categories-properties',
      'class' => Synchronization\Properties::class
    ),
    \Papaya\Content\Page\Dependency::SYNC_VIEW => array(
      'caption' => 'View',
      'hint' => 'Page view',
      'image' => 'items-view',
      'class' => Synchronization\View::class
    ),
    \Papaya\Content\Page\Dependency::SYNC_CONTENT => array(
      'caption' => 'Content',
      'hint' => 'Page content',
      'image' => 'categories-content',
      'class' => Synchronization\Content::class
    ),
    \Papaya\Content\Page\Dependency::SYNC_BOXES => array(
      'caption' => 'Boxes',
      'hint' => 'Box links',
      'image' => 'items-box',
      'class' => Synchronization\Boxes::class
    ),
    \Papaya\Content\Page\Dependency::SYNC_TAGS => array(
      'caption' => 'Tags',
      'hint' => 'Page tags/labels',
      'image' => 'items-tag',
      'class' => Synchronization\Tags::class
    ),
    \Papaya\Content\Page\Dependency::SYNC_ACCESS => array(
      'caption' => 'Access',
      'hint' => 'Access permissions for visitors',
      'image' => 'categories-access',
      'class' => Synchronization\Access::class
    ),
    \Papaya\Content\Page\Dependency::SYNC_PUBLICATION => array(
      'caption' => 'Publication',
      'hint' => 'Publication action',
      'image' => 'items-publication',
      'class' => Synchronization\Publication::class
    )
  );

  /**
   * Buffer variable for icon list
   *
   * @var \Papaya\Ui\Icon\Collection
   */
  private $_icons = NULL;

  /**
   * Buffer variable for array(id => caption).
   *
   * @var array(integer => string)
   */
  private $_list = NULL;

  /**
   * Dependencies records list.
   *
   * @var \Papaya\Content\Page\Dependencies
   */
  private $_dependencies = NULL;

  /**
   * Create {@see \Papaya\Ui\Icon\PapayaUiIconList} from definitions and return it.
   *
   * @return \Papaya\Ui\Icon\Collection
   */
  public function getIcons() {
    if (is_null($this->_icons)) {
      $this->_icons = new \Papaya\Ui\Icon\Collection;
      foreach ($this->_definitions as $synchronization => $data) {
        $this->_icons[$synchronization] = new \PapayaUiIcon(
          $data['image'],
          new \Papaya\Ui\Text\Translated($data['caption']),
          new \Papaya\Ui\Text\Translated($data['hint'])
        );
      }
    }
    return $this->_icons;
  }

  /**
   * Get synchronization as an array
   *
   * @return array
   */
  public function getList() {
    if (is_null($this->_list)) {
      $this->_list = array();
      foreach ($this->_definitions as $synchronization => $data) {
        $this->_list[$synchronization] = new \Papaya\Ui\Text\Translated($data['caption']);
      }
    }
    return $this->_list;
  }

  /**
   * Getter/setter for the dependcies database list
   *
   * @param \Papaya\Content\Page\Dependencies|NULL $dependencies
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
   * Get the action object for an synchronization.
   *
   * @param integer $synchronization
   * @return NULL|\Papaya\Administration\Pages\Dependency\Synchronization
   */
  public function getAction($synchronization) {
    if (isset($this->_definitions[$synchronization])) {
      $className = $this->_definitions[$synchronization]['class'];
      return new $className();
    }
    return NULL;
  }

  /**
   * Get the targets for an given synchonization. The targets are dependent pages that
   * are configured to be syncronized.
   *
   * @param integer $originId
   * @param integer $synchronization
   * @return array|NULL
   */
  public function getTargets($originId, $synchronization) {
    $targetIds = array();
    $this->dependencies()->load($originId);
    foreach ($this->dependencies() as $dependency) {
      if ($dependency['synchronization'] & $synchronization) {
        $targetIds[] = $dependency['id'];
      }
    }
    return empty($targetIds) ? NULL : $targetIds;
  }

  /**
   * Synchronize a dependency - this is called is a dependency is created or changed.
   *
   * @param \Papaya\Content\Page\Dependency $dependency
   */
  public function synchronizeDependency(\Papaya\Content\Page\Dependency $dependency) {
    foreach ($this->_definitions as $synchronization => $data) {
      if ($dependency->synchronization & $synchronization &&
        $synchronization != \Papaya\Content\Page\Dependency::SYNC_PUBLICATION &&
        ($action = $this->getAction($synchronization))) {
        $action->synchronize(array($dependency->id), $dependency->originId);
      }
    }
  }

  /**
   * Synchronize all dependencies if the original ist changed. This is triggered by an action.
   *
   * @param integer $synchronizations
   * @param integer $originId
   * @param array|NULL $languages
   */
  public function synchronizeAction($synchronizations, $originId, array $languages = NULL) {
    foreach ($this->_definitions as $identifier => $data) {
      if ($synchronizations & $identifier) {
        if ($targetIds = $this->getTargets($originId, $identifier)) {
          if ($action = $this->getAction($identifier)) {
            $action->synchronize($targetIds, $originId, $languages);
          }
        }
      }
    }
  }
}
