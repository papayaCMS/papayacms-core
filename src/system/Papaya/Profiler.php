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
namespace Papaya;

/**
 * Papaya profile, collects and stores profilng data for requests. A divisor is used to
 * define a probability that the profiling is activated.
 *
 * @package Papaya-Library
 * @subpackage Profiler
 */
class Profiler {
  private $_collector;

  private $_storage;

  private $_divisor = 50;

  private $_allowRun = 50;

  private $_type = 'papaya';

  /**
   * Create Profiler and set collector and storage objects.
   *
   * @param \Papaya\Profiler\Collector $collector
   * @param \Papaya\Profiler\Storage $storage
   */
  public function __construct(Profiler\Collector $collector, Profiler\Storage $storage) {
    $this->_collector = $collector;
    $this->_storage = $storage;
  }

  /**
   * Set the divisor. That defines the probability that the profiler is activated.
   *
   *
   * @param int $divisor
   */
  public function setDivisor($divisor) {
    $this->_allowRun = NULL;
    if ($divisor < 1) {
      $this->_allowRun = FALSE;
    } elseif (1 === (int)$divisor) {
      $this->_divisor = 1;
      $this->_allowRun = TRUE;
    } elseif ($divisor > 999999) {
      $this->_divisor = 999999;
    } else {
      $this->_divisor = $divisor;
    }
  }

  /**
   * Return true if profiling data for the current run should be collected.
   *
   * If it is not defined otherwise, it will e calculated using the $divisor.
   *
   * @return boolean.
   */
  public function allowRun() {
    if (NULL === $this->_allowRun) {
      $this->_allowRun = (1 === \mt_rand(1, $this->_divisor));
    }
    return $this->_allowRun;
  }

  /**
   * Start the profiling if allowed.
   */
  public function start() {
    if ($this->allowRun()) {
      $this->_collector->enable();
    }
  }

  /**
   * Store the collected profiling data.
   */
  public function store() {
    if ($this->allowRun() && ($data = $this->_collector->disable())) {
      $this->_storage->saveRun($data, $this->_type);
    }
  }
}
