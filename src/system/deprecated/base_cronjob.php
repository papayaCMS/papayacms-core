<?php
/**
* Basic object for all cronjobs.
*
* Cronjob objects must inherit this class.
*
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
* @subpackage Modules
* @version $Id: base_cronjob.php 39654 2014-03-20 11:41:46Z weinert $
*/

/**
* Basic object for all cron jobs.
*
* Cronjob objects must inherit this class.
*
* @package Papaya
* @subpackage Modules
*/
class base_cronjob extends base_plugin {
  /**
  * Main execution method.
  *
  * @access public
  * @return mixed string error message or  int 0 for success
  */
  function execute() {
    return 0;
  }

  /**
  * Check execution parameters for execution. Return value can be also string for showing further
  * informations in papaya backend dialog for example.
  *
  * @access public
  * @return boolean|string execution possible?
  */
  function checkExecParams() {
    return FALSE;
  }

  /**
   * This outputs a string prefixed with the current timestamp formatted and
   * flushes the output.
   *
   * Use this if you want the cronexec.php script to output information on a
   * cronjob status.
   * <code>
   * // example
   * base_cronjob::cronOutput('Data for day xy has been processed.');
   * </code>
   *
   * @param string $str a string to output in a cronjob
   * @param bool $break
   */
  public static function cronOutput($str, $break = TRUE) {
    if (trim($str) != '') {
      print gmdate('Y-m-d H:i:s T: ', time()).$str;
      if ($break) {
        echo LF;
      }
      flush();
    }
  }

  /**
   * This calls cronOutput, if PAPAYA_DB_SHOW_DEBUGS is set and TRUE.
   *
   * Use this if you want to output debugging information, that should not be
   * seen on live systems. This reduces output size and thus increases speed of
   * cronjob execution.
   * <code>
   * // example
   * base_cronjob::cronDebug('Processing entry #xy.');
   * </code>
   *
   * @param string $str a string to output in a cronjob
   * @param bool $break
   */
  public static function cronDebug($str, $break = TRUE) {
    if (defined('PAPAYA_DBG_SHOW_DEBUGS') && PAPAYA_DBG_SHOW_DEBUGS) {
      base_cronjob::cronOutput($str, $break);
      base_cronjob::cronOutput(
        sprintf(
          'MEM: %s (%s)', memory_get_usage(), memory_get_peak_usage()
        )
      );
    }
  }

}

