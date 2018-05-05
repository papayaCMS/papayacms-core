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

/**
* Papaya Application Profile Collection for papaya CMS
*
* @package Papaya-Library
* @subpackage Application
*/
class PapayaApplicationProfilesCms implements \PapayaApplicationProfiles {

  /**
  * Get a collection of application object profiles
  * @param $application
  * @return array
  */
  public function getProfiles($application) {
    $profiles = array();
    $profiles['Database'] = new \PapayaApplicationProfileDatabase();
    $profiles['Images'] = new \PapayaApplicationProfileImages();
    $profiles['Languages'] = new \PapayaApplicationProfileLanguages();
    $profiles['Messages'] = new \PapayaApplicationProfileMessages();
    $profiles['Options'] = new \PapayaApplicationProfileOptions();
    $profiles['Plugins'] = new \PapayaApplicationProfilePlugins();
    $profiles['Profiler'] = new \PapayaApplicationProfileProfiler();
    $profiles['Request'] = new \PapayaApplicationProfileRequest();
    $profiles['Response'] = new \PapayaApplicationProfileResponse();
    $profiles['Session'] = new \PapayaApplicationProfileSession();
    $profiles['Surfer'] = new \PapayaApplicationProfileSurfer();

    $profiles['AdministrationUser'] = new \PapayaApplicationProfileAdministrationUser();
    $profiles['AdministrationLanguage'] = new \PapayaApplicationProfileAdministrationLanguage();

    $profiles['References'] = new \PapayaApplicationProfileReferences();
    $profiles['PageReferences'] = new \PapayaApplicationProfilePageReferences();
    return $profiles;
  }
}
