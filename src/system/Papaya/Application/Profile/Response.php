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
 * Application object profile for the standard response object
 *
 * @package Papaya-Library
 * @subpackage Application
 */
class Response implements \Papaya\Application\Profile {

  /**
   * Create the profile object and return it
   *
   * @param \Papaya\Application $application
   * @return \PapayaResponse
   */
  public function createObject($application) {
    $response = new \PapayaResponse();
    $response->papaya($application);
    return $response;
  }
}
