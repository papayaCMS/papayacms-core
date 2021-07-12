<?php
/**
* Basic object of all time calculation modules
*
* Objects must inherit this class
*
* @copyright 2002-2014 by papaya Software GmbH - All rights reserved.
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
* @version $Id: base_crontime.php 39344 2014-02-26 13:20:08Z weinert $
*/

/**
* Basic object of all time calculation modules
*
* Objects must inherit this class
*
* @package Papaya
* @subpackage Modules
*/
abstract class base_crontime extends base_plugin {

  /**
   * next execution time
   *
   * 0 = stop execution
   *
   * @param int $from
   * @return int
   */
  abstract public function getNextDateTime($from);
}

