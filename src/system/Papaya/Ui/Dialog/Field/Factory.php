<?php
/**
* Create dialog fields using profile objects.
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
* @subpackage Ui
* @version $Id: Factory.php 39721 2014-04-07 13:13:23Z weinert $
*/

/**
* Create dialog fields using profile objects.
*
* The factory looks for a matching profile object/class, and uses it to create a dialog field.
*
* Profile objects can be registered using the register() method
*
* @package Papaya-Library
* @subpackage Ui
*/
class PapayaUiDialogFieldFactory {

  /**
   * @var array
   */
  private $_profiles = array();

  /**
   * Get the profile object for the given name.
   *
   * @param string $name
   * @return PapayaUiDialogFieldFactoryProfile
   */
  public function getProfile($name) {
    $class = $this->getProfileClass($name);
    $profile = new $class();
    return $profile;
  }

  /**
   * Get a field for the given profile type and options. Profile can either be a valid profile
   * object or a profile name.
   *
   * @param string|PapayaUiDialogFieldFactoryProfile $profile
   * @param PapayaUiDialogFieldFactoryOptions $options
   * @return \PapayaUiDialogField
   */
  public function getField($profile, PapayaUiDialogFieldFactoryOptions $options = NULL) {
    if (!($profile instanceof PapayaUiDialogFieldFactoryProfile)) {
      $profile = $this->getProfile($profile);
    }
    if (isset($options)) {
      $profile->options($options);
    }
    return $profile->getField();
  }

  /**
   * First check the $_profiles member variable for a registered profile class. If it is not
   * registered look for a class like "PapayaUiDialogFieldFactoryProfile$name". $name is
   * converted to camel case with the first letter uppercase.
   *
   * @param string $name
   * @throws PapayaUiDialogFieldFactoryExceptionInvalidProfile
   * @return string
   */
  private function getProfileClass($name) {
    $name = PapayaUtilStringIdentifier::toCamelCase($name, TRUE);
    if (isset($this->_profiles[$name])) {
      return $this->_profiles[$name];
    } elseif (empty($name)) {
      return __CLASS__.'ProfileInput';
    }
    $class = __CLASS__.'Profile'.$name;
    if (class_exists($class)) {
      return $class;
    }
    throw new PapayaUiDialogFieldFactoryExceptionInvalidProfile($name);
  }

  /**
   * Register profile classes. They must extend from PapayaUiDialogFieldFactoryProfile.
   *
   * The are not validated at this point. For a validation the need to be loaded and they may no be
   * needed.
   *
   * @param array|Traversable $profiles
   */
  public function registerProfiles($profiles) {
    PapayaUtilConstraints::assertArrayOrTraversable($profiles);
    foreach ($profiles as $name => $profile) {
      $this->_profiles[PapayaUtilStringIdentifier::toCamelCase($name, TRUE)] = $profile;
    }
  }
}