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
* superclass for administration plugins
*
* @package Papaya
* @subpackage Modules
*/
class base_module extends base_plugin {

  protected $permissions = array();

  /**
  * Guid
  * @var string $guid
  */
  var $guid;

  /**
   * layout object
   *
   * @var \Papaya\Template $layout
   */
  var $layout = NULL;

  /**
  * Get XML
  *
  * @access public
  */
  function getXML() {
    if (is_object($this->layout)) {
      $this->execModule();
    }
  }

  /**
  * Execute module
  *
  * @access public
  */
  function execModule() {
  }

  /**
  * check if user has permissions
  *
  * @param integer $permId permission
  * @param boolean $showMessage optional, default value FALSE
  * @access public
  * @return boolean
  */
  function hasPerm($permId, $showMessage = FALSE) {
    $administrationUser = $this->papaya()->administrationUser;
    if ($administrationUser->hasModulePerm($permId, $this->guid)) {
      return TRUE;
    } elseif ($administrationUser->isModulePermActive($permId, $this->guid) &&
              $administrationUser->isAdmin()) {
      return TRUE;
    }
    if ($showMessage) {
      $this->addMsg(
        MSG_ERROR,
        papaya_strings::escapeHTMLChars($this->_gt('You don\'t have the needed permissions.'))
      );
    }
    return FALSE;
  }

  /**
  * get an icon uri like module:moduleguid/iconfile
  *
  * @param $iconPath
  * @access public
  * @return string
  */
  function getIconURI($iconPath) {
    return 'module:'.$this->guid.'/'.$iconPath;
  }
}


