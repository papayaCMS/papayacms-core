<?php
/**
* Basic object for all date box objects (calendar)
*
* @copyright 2002-2007 by papaya Software GmbH - All rights reserved.
* @link http://www.papaya-cms.com/
* @license http://www.gnu.org/licenses/old-licenses/gpl-2.0.html GNU General Public License, version 2
*
* You can redistribute and/or modify this script under the terms of the GNU General Public
* License (GPL) version 2, provided that the copyright and license notes, including these
* lines, remain unmodified. papaya is distributed in the hope that it will be useful, but
* WITHOUT ANY WARRANTY; without even the implied warranty of MERCHANTABILITY or FITNESS
* FOR A PARTICULAR PURPOSE.
*
* @package Papaya
* @subpackage Modules
* @version $Id: base_datebox.php 39350 2014-02-26 14:17:51Z weinert $
*/

/**
* Basic object for all date box objects (calendar)
*
* Date box objects must inherit this class
*
* @package Papaya
* @subpackage Modules
*/
class base_datebox extends base_plugin {

  /**
   * Base eventbox
   *
   * @param object $calendar
   * @access public
   */
  function __construct($calendar) {
    $args = func_get_args();
    $this->calendar = $calendar;
    $this->parentObj = NULL;
    if (isset($args[1])) {
      $this->paramName = $this->getParamname($args[1]);
    } else {
      $this->paramName = $this->getParamname($paramName = 'bab');
    }
    $this->initializeParams();
    $this->baseLink = $this->getBaseLink();
  }

  /**
  * Take over XML for site
  * @return string
  */
  function getParsedData() {
    return '';
  }

  /**
  * Initialisize dialog
  *
  * @access public
  */
  function initializeDialog($hidden = NULL, $dialogId = NULL) {
    if (!(isset($this->dialog) && is_object($this->dialog))) {
      if (!$hidden) {
        $hidden = array(
          'save' => 1,
          'mode' => $this->calendar->sessionParams['mode']
        );
        if (isset($this->calendar->regular['regdate_id'])) {
          $hidden['regtopic'] = $this->calendar->regular['regdate_id'];
        } elseif (isset($this->calendar->loadedTopic['topic_id'])) {
          $hidden['topic'] = $this->calendar->loadedTopic['topic_id'];
        } else {
          $hidden['topic'] = '';
        }
      }
      $this->dialog = new base_dialog(
        $this, $this->paramName, $this->editFields, $this->data, $hidden
      );
      if ($dialogId) {
        $this->dialog->dialogId = $dialogId;
      }
      $this->dialog->inputFieldSize = $this->inputFieldSize;
      $this->dialog->paramName = $this->paramName;
      $this->dialog->params = $this->params;
      $this->dialog->baseLink = $this->baseLink;
      $this->dialog->loadParams();
    }
  }
}
