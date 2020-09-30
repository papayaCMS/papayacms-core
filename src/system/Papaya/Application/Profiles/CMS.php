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
namespace Papaya\Application\Profiles;

use Papaya\Application\Profile;

/**
 * Papaya Application Profile Collection for papaya CMS
 *
 * @package Papaya-Library
 * @subpackage Application
 */
class CMS implements \Papaya\Application\Profiles {
  /**
   * Get a collection of application object profiles
   *
   * @param $application
   *
   * @return array
   */
  public function getProfiles($application) {
    $profiles = [];
    $profiles['Database'] = new Profile\Database();
    $profiles['Front'] = new Profile\Front();
    $profiles['Images'] = new Profile\Images();
    $profiles['Languages'] = new Profile\Languages();
    $profiles['Media'] = new Profile\Media();
    $profiles['Messages'] = new Profile\Messages();
    $profiles['Options'] = new Profile\Options();
    $profiles['Plugins'] = new Profile\Plugins();
    $profiles['Profiler'] = new Profile\Profiler();
    $profiles['Request'] = new Profile\Request();
    $profiles['Response'] = new Profile\Response();
    $profiles['Session'] = new Profile\Session();
    $profiles['Surfer'] = new Profile\Surfer();

    $profiles['AdministrationUser'] = new Profile\Administration\User();
    $profiles['AdministrationLanguage'] = new Profile\Administration\Language();
    $profiles['AdministrationPhrases'] = new Profile\Administration\Phrases();
    $profiles['AdministrationRichText'] = new Profile\Administration\RichText();

    $profiles['References'] = new Profile\References();
    $profiles['PageReferences'] = new Profile\Page\References();
    return $profiles;
  }
}
