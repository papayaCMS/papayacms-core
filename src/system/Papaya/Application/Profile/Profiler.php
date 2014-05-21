<?php
/**
* Application object profile for profiler
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
* @package Papaya-Library
* @subpackage Application
* @version $Id: Profiler.php 39484 2014-03-03 11:21:06Z weinert $
*/

/**
* Application object profile for profiler
*
* @package Papaya-Library
* @subpackage Application
*/
class PapayaApplicationProfileProfiler implements PapayaApplicationProfile {

  private $_builder = NULL;

  /**
  * Create the profile object and return it
  * @param PapayaApplication|PapayaApplicationCms $application
  * @return stdClass
  */
  public function createObject($application) {
    $builder = $this->builder();
    $builder->papaya($application);
    $profiler = new PapayaProfiler($builder->createCollector(), $builder->createStorage());
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
   * @param PapayaProfilerBuilder $builder
   * @return PapayaProfilerBuilder
   */
  public function builder(PapayaProfilerBuilder $builder = NULL) {
    if (isset($builder)) {
      $this->_builder = $builder;
    } elseif (is_null($this->_builder)) {
      $this->_builder = new PapayaProfilerBuilder();
    }
    return $this->_builder;
  }
}