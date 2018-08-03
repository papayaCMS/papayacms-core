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

namespace Papaya\Ui\Dialog\Field\Select;
/**
 * Callbacks that are used by the dialog select field
 *
 *
 *
 * @package Papaya-Library
 * @subpackage Ui
 *
 * @property \Papaya\BaseObject\Callback $getOptionCaption
 * @property \Papaya\BaseObject\Callback $getOptionGroupCaption
 * @property \Papaya\BaseObject\Callback $getOptionData
 * @method string getOptionCaption($option, $index)
 * @method string getOptionGroupCaption($option, $index)
 * @method array getOptionData($option, $index)
 */
class Callbacks extends \Papaya\BaseObject\Callbacks {

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
