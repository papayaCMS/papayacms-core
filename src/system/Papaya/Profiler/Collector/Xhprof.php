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
* Profiling data collector for XHProf.
*
* @package Papaya-Library
* @subpackage Profiler
*/
class PapayaProfilerCollectorXhprof implements \PapayaProfilerCollector {

  /**
  * Store if it is currently enabled (data is collected)
  *
  * @var boolean
  */
  private $_enabled = FALSE;

  /**
  * Enable data collection
  */
  public function enable() {
    if (!$this->_enabled && extension_loaded('xhprof')) {
      // @codeCoverageIgnoreStart
      /** @noinspection PhpUndefinedFunctionInspection */
      /** @noinspection PhpUndefinedConstantInspection */
      xhprof_enable(XHPROF_FLAGS_CPU + XHPROF_FLAGS_MEMORY + XHPROF_FLAGS_NO_BUILTINS);
      $this->_enabled = TRUE;
    }
    // @codeCoverageIgnoreEnd
    return $this->_enabled;
  }

  /**
  * Disable data collection and return collected data, If no data was collected, NULL is returned.
  *
  * @return NULL|array()
  */
  public function disable() {
    if ($this->_enabled && extension_loaded('xhprof')) {
      // @codeCoverageIgnoreStart
      $this->_enabled = FALSE;
      /** @noinspection PhpUndefinedFunctionInspection */
      return xhprof_disable();
      // @codeCoverageIgnoreEnd
    }
    return NULL;
  }
}
