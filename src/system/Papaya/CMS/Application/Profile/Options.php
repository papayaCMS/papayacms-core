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
use Papaya\Configuration;

/**
 * Application object profile for default options object
 *
 * @package Papaya-Library
 * @subpackage Application
 */
class Options implements Application\Profile {
  /**
   * Create the profile object and return it
   *
   * @param Application $application
   *
   * @return \Papaya\CMS\CMSConfiguration
   */
  public function createObject($application) {
    $options = new \Papaya\CMS\CMSConfiguration();
    $options->storage(new \Papaya\CMS\Configuration\Storage\Database());
    return $options;
  }
}
