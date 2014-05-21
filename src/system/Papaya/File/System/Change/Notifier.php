<?php
/**
* An file change notifier, other use this class to notify an url or shell script about a
* file they have changed. (added, delete, modified)
*
* @copyright 2012 by papaya Software GmbH - All rights reserved.
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
* @subpackage FileSystem
* @version $Id: Notifier.php 39480 2014-03-03 10:49:34Z weinert $
*/

/**
* An filter iterator to filter an given iterator using a pcre pattern.
*
* The elements of the inner iterator are casted to string, so they can be objects implemening
* the __toString method.
*
* @package Papaya-Library
* @subpackage FileSystem
*/
class PapayaFileSystemChangeNotifier {

  /**
   * File/directory was added
   * @var string
   */
  const ACTION_ADD = 'A';
  /**
   * File/directory was modified, be aware that an rename should be a ACTION_REMOVE and
   * and ACTION_ADD
   *
   * @var string
   */
  const ACTION_MODIFIED = 'M';
  /**
   * File/directory was deleted
   * @var string
   */
  const ACTION_DELETED = 'D';
  /**
   * Directory was cleared (all files/subdirectories were deleted)
   * @var string
   */
  const ACTION_CLEARED = 'C';

  /**
   * Directory was invalidated (the files/directories are not up to date but they are not deleted)
   * @var string
   */
  const ACTION_INVALIDATED = 'I';

  /**
   * @var PapayaFileSystemAction
   */
  private $_action = NULL;

  /**
   * Create object and store notification target
   *
   * @param string $target
   */
  public function __construct($target) {
    $this->setTarget($target);
  }

  /**
   * Set target and mode. If the target starts with http:// or https::// the mode will be
   * url, if it empty it will be disabled, otherwise script.
   *
   * @param string $target
   */
  public function setTarget($target) {
    if (preg_match('(^https?://)', $target)) {
      $this->_action = new PapayaFileSystemActionUrl($target);
    } elseif (!empty($target)) {
      $this->_action = new PapayaFileSystemActionScript($target);
    } else {
      $this->_action = NULL;
    }
  }

  /**
   * Trigger the notification, if an action is set
   *
   * @param string $action
   * @param string|NULL $file
   * @param string $path
   */
  public function notify($action, $file = NULL, $path = NULL) {
    $parameters = array(
      'action' => $action
    );
    if (isset($file)) {
      $parameters['file'] = $file;
    }
    if (isset($path)) {
      $parameters['path'] = $path;
    }
    if ($action = $this->action()) {
      $action->execute($parameters);
    }
  }

  /**
   * Get/Set the notifier action object, this will be set from setTarget usually.
   *
   * @param PapayaFileSystemAction $action
   * @return \PapayaFileSystemAction
   */
  public function action(PapayaFileSystemAction $action = NULL) {
    if (isset($action)) {
      $this->_action = $action;
    }
    return $this->_action;
  }
}