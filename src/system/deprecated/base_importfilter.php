<?php
/**
* import filter
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
* @version $Id: base_importfilter.php 39260 2014-02-18 17:13:06Z weinert $
*/

/**
* import filter
*
* @package Papaya
* @subpackage Modules
*/
class base_importfilter extends base_plugin {

  /**
  * Edit fields
  * @var array $editFields
  */
  var $editFields = array();

  /**
  * Abstract import method, overload in module to define import action
  * @return boolean
  */
  function import() {
    return FALSE;
  }

  /**
  * Get dialog
  *
  * @see base_dialog:getDialogXML
  * @access public
  * @return string
  */
  function getDialog() {
    $this->initializeDialog();
    $this->dialog->dialogTitle = $this->_gt('Edit filter properties');
    $this->dialog->dialogDoubleButtons = FALSE;
    return $this->dialog->getDialogXML();
  }
}
