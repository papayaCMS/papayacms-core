<?php
/**
* Callbacks that are used by the dialog select field
*
* @copyright 2010 by papaya Software GmbH - All rights reserved.
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
* @subpackage Ui
* @version $Id: Callbacks.php 37496 2012-08-31 14:28:35Z weinert $
*/

/**
* Callbacks that are used by the dialog select field
*
*
*
* @package Papaya-Library
* @subpackage Ui
*
* @property PapayaObjectCallback $getOptionCaption
* @property PapayaObjectCallback $getOptionGroupCaption
* @property PapayaObjectCallback $getOptionData
* @method string getOptionCaption
* @method string getOptionGroupCaption
* @method array getOptionData
*/
class PapayaUiDialogFieldSelectCallbacks extends PapayaObjectCallbacks {

  public function __construct() {
    parent::__construct(
      array(
        'getOptionCaption' => NULL,
        'getOptionGroupCaption' => NULL,
        'getOptionData' => array()
      )
    );
  }
}