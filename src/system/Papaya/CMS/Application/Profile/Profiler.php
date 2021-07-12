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
namespace Papaya\CMS\Application\Profile;

use Papaya\Application;
use Papaya\Profiler as Profiling;

/**
 * Application object profile for profiler
 *
 * @package Papaya-Library
 * @subpackage Application
 */
class Profiler implements Application\Profile {
  private $_builder;

  /**
   * Create the profile object and return it
   *
   * @param \Papaya\CMS\CMSApplication $application
   *
   * @return Profiling
   */
  public function createObject($application) {
    $builder = $this->builder();
    $builder->papaya($application);
    $profiler = new Profiling($builder->createCollector(), $builder->createStorage());
    if ($application->options->get(\Papaya\CMS\CMSConfiguration::PROFILER_ACTIVE, FALSE)) {
      $profiler->setDivisor($application->options->get(\Papaya\CMS\CMSConfiguration::PROFILER_DIVISOR, 50));
    } else {
      $profiler->setDivisor(0);
    }
    return $profiler;
  }

  /**
   * Getter/Setter for profiler builder
   *
   * @param Profiling\Builder $builder
   *
   * @return Profiling\Builder
   */
  public function builder(Profiling\Builder $builder = NULL) {
    if (NULL !== $builder) {
      $this->_builder = $builder;
    } elseif (NULL === $this->_builder) {
      $this->_builder = new Profiling\Builder();
    }
    return $this->_builder;
  }
}
