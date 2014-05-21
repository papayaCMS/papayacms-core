<?php
/**
* Application object profile for default request object
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
* @version $Id: Request.php 39484 2014-03-03 11:21:06Z weinert $
*/

/**
* Application object profile for default request object
*
* @package Papaya-Library
* @subpackage Application
*/
class PapayaApplicationProfileRequest implements PapayaApplicationProfile {

  /**
  * Create the profile object and return it
  * @param PapayaApplication|PapayaApplicationCms $application
  * @return stdClass
  */
  public function createObject($application) {
    $request = new PapayaRequest($application->options);
    $request->papaya($application);
    $request->load(new PapayaUrlCurrent());
    return $request;
  }
}