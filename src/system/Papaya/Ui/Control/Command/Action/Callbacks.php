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
* Callbacks that are used by the action command
*
* @package Papaya-Library
* @subpackage Ui
*
* @property \Papaya\BaseObject\Callback $onValidationSuccessful
* @property \Papaya\BaseObject\Callback $onValidationFailed
* @property \Papaya\BaseObject\Callback $getDefinition
* @method void onValidationSuccessful
* @method void onValidationFailed
* @method array getDefinition
*/
class PapayaUiControlCommandActionCallbacks extends \Papaya\BaseObject\Callbacks {

  /**
  * Initialize object and set callback definition
  */
  public function __construct() {
    parent::__construct(
      array(
        'getDefinition' => array(),
        'onValidationSuccessful' => NULL,
        'onValidationFailed' => NULL
      )
    );
  }
}
