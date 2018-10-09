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
namespace Papaya\Application;

/**
 * Papaya Object - papaya basic object
 *
 * @package Papaya-Library
 * @subpackage Objects
 */
abstract class BaseObject implements Access {
  use Access\Aggregation;

  /**
   * Get application object
   *
   * @deprecated {@see \Papaya\Application\Access\Aggregation::papaya()}
   *
   * @return \Papaya\Application
   */
  public function getApplication() {
    return $this->papaya();
  }

  /**
   * Set application object
   *
   * @deprecated {@see \Papaya\Application\Access\Aggregation::papaya()}
   *
   * @param \Papaya\Application $application
   */
  public function setApplication($application) {
    $this->papaya($application);
  }
}
