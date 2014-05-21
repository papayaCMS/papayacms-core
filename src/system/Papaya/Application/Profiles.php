<?php
/**
* Abstract class for Papaya Application Profile Collections
*
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
* @version $Id: Profiles.php 38584 2013-08-05 16:39:24Z weinert $
*/

/**
* Abstract class for Papaya Application Profile Collections
* @package Papaya-Library
* @subpackage Application
*/
interface PapayaApplicationProfiles {

  /**
  * Get a collection of application object profiles
  * @param PapayaApplication $application
  * @return array
  */
  function getProfiles($application);
}