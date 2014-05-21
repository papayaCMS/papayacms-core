<?php
/**
* Define some status tables
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
* @subpackage Core
* @version $Id: base_statictables.php 39730 2014-04-07 21:05:30Z weinert $
*/

/**
* Define some status tables
* @package Papaya
* @subpackage Core
*/
abstract class base_statictables {

  /**
  * States for a page
   *
  * @return array
  */
  public static function getTableStates() {
    return array(
      1 => 'Created',
      2 => 'Ready',
      3 => 'Edited',
      4 => 'Working'
    );
  }

  /**
  * States for a page access permissions
   *
  * @return array
  */
  public static function getTableAccessStates() {
    return array(
      1 => 'Own permissions',
      2 => 'Inherited permissions',
      3 => 'Additional permissions'
    );
  }

  /**
  * Option groups
   *
  * @return array
  */
  public static function getTableOptGroups() {
    return array(
      0 => 'Unknown',
      1 => 'Tables',
      2 => 'Files and Directories',
      3 => 'Debugging',
      4 => 'Language',
      5 => 'Project defaults',
      6 => 'Internals',
      7 => 'System',
      8 => 'Layout',
      9 => 'Default pages',
      10 => 'Charsets',
      11 => 'Overview page',
      12 => 'Login',
      13 => 'Support',
      14 => 'MediaDB',
      15 => 'Cache',
      16 => 'Log',
      17 => 'Session',
    );
  }

  /**
  * Log message groups
   *
  * @return array
  */
  public static function getTableLogGroups() {
    return array(
      1 => 'User',
      2 => 'Pages',
      3 => 'Database',
      4 => 'Calender',
      5 => 'Cronjobs',
      6 => 'Community',
      7 => 'System',
      8 => 'Modules',
      9 => 'PHP',
      10 => 'Debug'
    );
  }

  /**
  * Authentication permission groups
   *
  * @return array
  */
  public static function getAuthPermGroups() {
    return array(
      1 => 'Misc',
      2 => 'Pages',
      3 => 'Administration',
      4 => 'Media database',
      5 => 'Boxes',
      6 => 'Internals',
      7 => 'Applications'
    );
  }

  /**
  * Get change levels
   *
  * @return array
  */
  public static function getChangeLevels() {
    return array(
      1 => 'Minor',
      2 => 'Major',
      3 => 'Normal',
      4 => 'New',
    );
  }

  /**
  * Get priorities for task list
  *
  * @return array
  */
  public static function getTodoPriorities() {
    return array(
      0 => 'normal',
      1 => 'high',
      2 => 'urgent',
     );
  }

  /**
  * Get states for task list
  *
  * @return array
  */
  public static function getTodoStates() {
    return array(
      0 => 'normal',
      1 => 'urgent',
      2 => 'overdue',
    );
  }

  /**
  * get change frequence values
  * return array of strings with the values of the sites changing frequence
   *
  * @return array of strings, integer indexed (0-6)
  */
  public static function getChangeFrequencyValues() {
    return array(
      0 => 'never',
      1 => 'yearly',
      2 => 'monthly',
      3 => 'weekly',
      4 => 'daily',
      5 => 'hourly',
      6 => 'always');
  }

  /**
  * get priority values
  * return array of strings indexed by the sites priority as percentage value
   *
  * @return array of strings, integer indexed (30,50,80)
  */
  public static function getPriorityValues() {
    return array(
      10 => '10%',
      20 => '20%',
      30 => '30%',
      40 => '40%',
      50 => '50%',
      60 => '60%',
      70 => '70%',
      80 => '80%',
      90 => '90%',
      100 => '100%');
  }

}
