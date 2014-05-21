<?php
/**
* Profiler objects builder, create the objects needed to initialize the profiler.
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
* @version $Id: Builder.php 39468 2014-02-28 19:51:17Z weinert $
*/

/**
* Profiler objects builder, create the objects needed to initialize the profiler. The
* Profiler needs an collector and a storage object. Which classes are created depends on the
* current configuraiton.
*
* @package Papaya-Library
* @subpackage Profiler
*/
class PapayaProfilerBuilder extends PapayaObject {

  /**
  * Create the profiler collector object. Currently heres is only the xhprof wrapper.
  *
  * @return PapayaProfilerCollector
  */
  public function createCollector() {
    return new PapayaProfilerCollectorXhprof();
  }

  /**
  * Create the profiler storage object depending on the configuration.
  *
  * The default storage is a directory, and stores the data for the default xhprof html pages.
  *
  * "xhgui" stores the data into a database, optimized for XHGui
  * (https://github.com/preinheimer/xhprof).
  *
  * @return PapayaProfilerStorage
  */
  public function createStorage() {
    switch ($this->papaya()->options->get('PAPAYA_PROFILER_STORAGE', 'file')) {
    case 'xhgui' :
      $storage = new PapayaProfilerStorageXhgui(
        $this->papaya()->options->get('PAPAYA_PROFILER_STORAGE_DATABASE', ''),
        $this->papaya()->options->get('PAPAYA_PROFILER_STORAGE_DATABASE_TABLE', 'details'),
        $this->papaya()->options->get('PAPAYA_PROFILER_SERVER_ID', '1')
      );
      break;
    case 'file' :
    default :
      $storage = new PapayaProfilerStorageFile(
        $this->papaya()->options->get(
          'PAPAYA_PROFILER_STORAGE_DIRECTORY', ini_get('xhprof.output_dir')
        )
      );
      break;
    }
    return $storage;
  }

}