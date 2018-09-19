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
namespace Papaya\Profiler;

/**
 * Profiler objects builder, create the objects needed to initialize the profiler. The
 * Profiler needs an collector and a storage object. Which classes are created depends on the
 * current configuraiton.
 *
 * @package Papaya-Library
 * @subpackage Profiler
 */
class Builder extends \Papaya\Application\BaseObject {
  /**
   * Create the profiler collector object. Currently heres is only the xhprof wrapper.
   *
   * @return \Papaya\Profiler\Collector
   */
  public function createCollector() {
    return new Collector\Xhprof();
  }

  /**
   * Create the profiler storage object depending on the configuration.
   *
   * The default storage is a directory, and stores the data for the default xhprof html pages.
   *
   * "xhgui" stores the data into a database, optimized for XHGui
   * (https://github.com/preinheimer/xhprof).
   *
   * @return \Papaya\Profiler\Storage
   */
  public function createStorage() {
    switch ($this->papaya()->options->get('PAPAYA_PROFILER_STORAGE', 'file')) {
      case 'xhgui' :
        $storage = new Storage\Xhgui(
          $this->papaya()->options->get('PAPAYA_PROFILER_STORAGE_DATABASE', ''),
          $this->papaya()->options->get('PAPAYA_PROFILER_STORAGE_DATABASE_TABLE', 'details'),
          $this->papaya()->options->get('PAPAYA_PROFILER_SERVER_ID', '1')
        );
      break;
      case 'file' :
      default :
        $storage = new Storage\File(
          $this->papaya()->options->get(
            'PAPAYA_PROFILER_STORAGE_DIRECTORY', \ini_get('xhprof.output_dir')
          )
        );
      break;
    }
    return $storage;
  }
}
