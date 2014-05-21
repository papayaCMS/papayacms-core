<?php
/**
* Application object profile for references factory
*
* @copyright 2012 by papaya Software GmbH - All rights reserved.
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
* @version $Id: References.php 38941 2013-11-15 12:51:29Z weinert $
*/

/**
* Application object profile for references factory
*
* @package Papaya-Library
* @subpackage Application
*/
class PapayaApplicationProfileReferences implements PapayaApplicationProfile {

  /**
  * Create the profile object and return it
  * @param PapayaApplication $application
  * @return PapayaDatabaseManager
  */
  public function createObject($application) {
    $references = new PapayaUiReferenceFactory();
    $references->papaya($application);
    return $references;
  }
}
