<?php
/**
* Profiling data collector interface.
*
* @copyright 2011 by papaya Software GmbH - All rights reserved.
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
* @subpackage Profiler
* @version $Id: Collector.php 36246 2011-09-27 19:26:09Z weinert $
*/

/**
* Profiling data collector interface.
*
* @package Papaya-Library
* @subpackage Profiler
*/
interface PapayaProfilerCollector {

  /**
  * Enable data collection
  */
  function enable();

  /**
  * Disable data collection and return collected data, If no data was collected, NULL is returned.
  *
  * @return NULL|array()
  */
  function disable();
}