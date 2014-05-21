<?php
/**
* Application object profile for the images
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
* @version $Id: Images.php 38637 2013-08-28 11:56:52Z weinert $
*/

/**
* Application object profile for the images
*
* Looks fopr an array $GLOBALS['PAPAYA_IMAGES'] and creates an instance of
* PapayaUiImages with it.
*
* @package Papaya-Library
* @subpackage Application
*/
class PapayaApplicationProfileImages implements PapayaApplicationProfile {

  /**
  * Create the profile object and return it
  * @param PapayaApplication $application
  * @return PapayaUiImages
  */
  public function createObject($application) {
    $images = new PapayaUiImages(
      empty($GLOBALS['PAPAYA_IMAGES']) ? array() : $GLOBALS['PAPAYA_IMAGES']
    );
    return $images;
  }
}