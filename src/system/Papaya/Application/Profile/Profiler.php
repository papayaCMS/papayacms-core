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

namespace Papaya\Application\Profile;
/**
 * Application object profile for profiler
 *
 * @package Papaya-Library
 * @subpackage Application
 */
class Profiler implements \Papaya\Application\Profile {

  private $_builder;

  /**
   * Create the profile object and return it
   *
   * @param \Papaya\Application|\Papaya\Application\Cms $application
   * @return \PapayaProfiler
   */
  public function createObject($application) {
    $builder = $this->builder();
    $builder->papaya($application);
    $profiler = new \PapayaProfiler($builder->createCollector(), $builder->createStorage());
    if ($application->options->get('PAPAYA_PROFILER_ACTIVE', FALSE)) {
      $profiler->setDivisor($application->options->get('PAPAYA_PROFILER_DIVISOR', 50));
    } else {
      $profiler->setDivisor(0);
    }
    return $profiler;
  }

  /**
   * Getter/Setter for profiler builder
   *
   * @param \PapayaProfilerBuilder $builder
   * @return \PapayaProfilerBuilder
   */
  public function builder(\PapayaProfilerBuilder $builder = NULL) {
    if (NULL !== $builder) {
      $this->_builder = $builder;
    } elseif (NULL === $this->_builder) {
      $this->_builder = new \PapayaProfilerBuilder();
    }
    return $this->_builder;
  }
}
