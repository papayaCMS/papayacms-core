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
namespace Papaya\UI\Dialog\Database;

use Papaya\BaseObject;
use Papaya\Database;

/**
 * Callbacks that are used by the database dialogs.
 *
 * @package Papaya-Library
 * @subpackage UI
 *
 * @property BaseObject\Callback $onBeforeSave
 * @property BaseObject\Callback $onBeforeDelete
 *
 * @method bool onBeforeSave(Database\Interfaces\Record $record)
 * @method bool onBeforeDelete(Database\Interfaces\Record $record)
 */
class Callbacks extends BaseObject\Callbacks {
  /**
   * Initialize object and set callback definition
   */
  public function __construct() {
    parent::__construct(
      [
        'onBeforeDelete' => TRUE,
        'onBeforeSave' => TRUE
      ]
    );
  }
}
