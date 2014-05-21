<?php
/**
* Callbacks that are used by the record mapping object
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
* @subpackage Database
* @version $Id: Callbacks.php 39694 2014-03-26 13:33:49Z weinert $
*/

/**
* Callbacks that are used by the record mapping object
*
* More specific callbacks are called before the unspecific, like "onMapValueFromFieldToProperty()"
* before "onMapValue".
*
* @package Papaya-Library
* @subpackage Database
*
* @property PapayaObjectCallback $onBeforeUpdate
* @property PapayaObjectCallback $onBeforeInsert
* @property PapayaObjectCallback $onBeforeDelete
* @method boolean onBeforeUpdate
* @method boolean onBeforeInsert
* @method boolean onBeforeDelete
*/
class PapayaDatabaseRecordCallbacks extends PapayaObjectCallbacks {

  public function __construct() {
    parent::__construct(
      array(
        'onBeforeUpdate' => TRUE,
        'onBeforeInsert' => TRUE,
        'onBeforeDelete' => TRUE,
      )
    );
  }
}