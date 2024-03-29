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
namespace Papaya\CMS\Administration\Pages\Dependency;

use Papaya\CMS\Content;
use Papaya\UI;
use Papaya\Utility;

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
  private $_definitions = [
    Content\Page\Dependency::SYNC_PROPERTIES => [
      'caption' => 'Properties',
      'hint' => 'Page properties',
      'image' => 'categories-properties',
      'class' => Synchronization\Properties::class
    ],
    Content\Page\Dependency::SYNC_VIEW => [
      'caption' => 'View',
      'hint' => 'Page view',
      'image' => 'items-view',
      'class' => Synchronization\View::class
    ],
    Content\Page\Dependency::SYNC_CONTENT => [
      'caption' => 'Content',
      'hint' => 'Page content',
      'image' => 'categories-content',
      'class' => Synchronization\Content::class
    ],
    Content\Page\Dependency::SYNC_BOXES => [
      'caption' => 'Boxes',
      'hint' => 'Box links',
      'image' => 'items-box',
      'class' => Synchronization\Boxes::class
    ],
    Content\Page\Dependency::SYNC_TAGS => [
      'caption' => 'Tags',
      'hint' => 'Page tags/labels',
      'image' => 'items-tag',
      'class' => Synchronization\Tags::class
    ],
    Content\Page\Dependency::SYNC_ACCESS => [
      'caption' => 'Access',
      'hint' => 'Access permissions for visitors',
      'image' => 'categories-access',
      'class' => Synchronization\Access::class
    ],
    Content\Page\Dependency::SYNC_PUBLICATION => [
      'caption' => 'Publication',
      'hint' => 'Publication action',
      'image' => 'items-publication',
      'class' => Synchronization\Publication::class
    ]
  ];

  /**
   * Buffer variable for icon list
   *
   * @var UI\Icon\Collection
   */
  private $_icons;

  /**
   * Buffer variable for array(id => caption).
   *
   * @var array(integer => string)
   */
  private $_list;

  /**
   * Dependencies records list.
   *
   * @var Content\Page\Dependencies
   */
  private $_dependencies;

  /**
   * Create {@see \Papaya\UI\Icon\Collection} from definitions and return it.
   *
   * @return UI\Icon\Collection
   */
  public function getIcons() {
    if (NULL === $this->_icons) {
      $this->_icons = new UI\Icon\Collection();
      foreach ($this->_definitions as $synchronization => $data) {
        $this->_icons[$synchronization] = new UI\Icon(
          $data['image'],
          new UI\Text\Translated($data['caption']),
          new UI\Text\Translated($data['hint'])
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
    if (NULL === $this->_list) {
      $this->_list = [];
      foreach ($this->_definitions as $synchronization => $data) {
        $this->_list[$synchronization] = new UI\Text\Translated($data['caption']);
      }
    }
    return $this->_list;
  }

  /**
   * Getter/setter for the dependencies database list
   *
   * @param Content\Page\Dependencies|null $dependencies
   *
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
   * Get the action object for an synchronization.
   *
   * @param int $synchronization
   *
   * @return null|\Papaya\CMS\Administration\Pages\Dependency\Synchronization
   */
  public function getAction($synchronization) {
    if (isset($this->_definitions[$synchronization])) {
      $className = $this->_definitions[$synchronization]['class'];
      return new $className();
    }
    return;
  }

  /**
   * Get the targets for an given synchronization. The targets are dependent pages that
   * are configured to be synchronized.
   *
   * @param int $originId
   * @param int $synchronization
   *
   * @return array|null
   */
  public function getTargets($originId, $synchronization) {
    $targetIds = [];
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
   * @param Content\Page\Dependency $dependency
   */
  public function synchronizeDependency(Content\Page\Dependency $dependency) {
    foreach ($this->_definitions as $synchronization => $data) {
      if (
        Content\Page\Dependency::SYNC_PUBLICATION !== (int)$synchronization &&
        Utility\Bitwise::inBitmask($synchronization, $dependency->synchronization) &&
        ($action = $this->getAction($synchronization))) {
        $action->synchronize([$dependency->id], $dependency->originId);
      }
    }
  }

  /**
   * Synchronize all dependencies if the original ist changed. This is triggered by an action.
   *
   * @param int $synchronizations
   * @param int $originId
   * @param array|null $languages
   */
  public function synchronizeAction($synchronizations, $originId, array $languages = NULL) {
    foreach ($this->_definitions as $identifier => $data) {
      if (
        Utility\Bitwise::inBitmask($identifier, $synchronizations) &&
        ($targetIds = $this->getTargets($originId, $identifier)) &&
        ($action = $this->getAction($identifier))
      ) {
        $action->synchronize($targetIds, $originId, $languages);
      }
    }
  }
}
