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

namespace Papaya\Database\Record;

/**
 * Callbacks that are used by the record object
 *
 * @package Papaya-Library
 * @subpackage Database
 *
 * @property \Papaya\BaseObject\Callback $onBeforeUpdate
 * @property \Papaya\BaseObject\Callback $onBeforeInsert
 * @property \Papaya\BaseObject\Callback $onBeforeDelete
 * @property \Papaya\BaseObject\Callback $onAfterUpdate
 * @property \Papaya\BaseObject\Callback $onAfterInsert
 * @property \Papaya\BaseObject\Callback $onAfterDelete
 * @method bool onBeforeUpdate(\Papaya\Database\Record $record)
 * @method bool onBeforeInsert(\Papaya\Database\Record $record)
 * @method bool onBeforeDelete(\Papaya\Database\Record $record)
 * @method bool onAfterUpdate(\Papaya\Database\Record $record)
 * @method bool onAfterInsert(\Papaya\Database\Record $record)
 * @method bool onAfterDelete(\Papaya\Database\Record $record)
 */
class Callbacks extends \Papaya\BaseObject\Callbacks {
  public function __construct() {
    parent::__construct(
      [
        'onBeforeUpdate' => TRUE,
        'onBeforeInsert' => TRUE,
        'onBeforeDelete' => TRUE,
        'onAfterUpdate' => TRUE,
        'onAfterInsert' => TRUE,
        'onAfterDelete' => TRUE,
      ]
    );
  }
}
