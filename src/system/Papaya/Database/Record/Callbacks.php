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
* Callbacks that are used by the record object
*
* @package Papaya-Library
* @subpackage Database
*
 * @property PapayaObjectCallback $onBeforeUpdate
 * @property PapayaObjectCallback $onBeforeInsert
 * @property PapayaObjectCallback $onBeforeDelete
 * @property PapayaObjectCallback $onAfterUpdate
 * @property PapayaObjectCallback $onAfterInsert
 * @property PapayaObjectCallback $onAfterDelete
 * @method boolean onBeforeUpdate(\PapayaDatabaseRecord $record)
 * @method boolean onBeforeInsert(\PapayaDatabaseRecord $record)
 * @method boolean onBeforeDelete(\PapayaDatabaseRecord $record)
 * @method boolean onAfterUpdate(\PapayaDatabaseRecord $record)
 * @method boolean onAfterInsert(\PapayaDatabaseRecord $record)
 * @method boolean onAfterDelete(\PapayaDatabaseRecord $record)
*/
class PapayaDatabaseRecordCallbacks extends PapayaObjectCallbacks {

  public function __construct() {
    parent::__construct(
      array(
        'onBeforeUpdate' => TRUE,
        'onBeforeInsert' => TRUE,
        'onBeforeDelete' => TRUE,
        'onAfterUpdate' => TRUE,
        'onAfterInsert' => TRUE,
        'onAfterDelete' => TRUE,
      )
    );
  }
}
