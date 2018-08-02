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

namespace Papaya\Ui\Dialog\Field;
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
class Factory {

  /**
   * @var array
   */
  private $_profiles = array();

  /**
   * Get the profile object for the given name.
   *
   * @param string $name
   * @return Factory\Profile
   * @throws Factory\Exception\InvalidProfile
   */
  public function getProfile($name) {
    $class = $this->getProfileClass($name);
    return new $class();
  }

  /**
   * Get a field for the given profile type and options. Profile can either be a valid profile
   * object or a profile name.
   *
   * @param string|Factory\Profile $profile
   * @param Factory\Options $options
   * @return \PapayaUiDialogField
   * @throws \Papaya\Ui\Dialog\Field\Factory\Exception
   */
  public function getField($profile, Factory\Options $options = NULL) {
    if (!($profile instanceof Factory\Profile)) {
      $profile = $this->getProfile($profile);
    }
    if (NULL !== $options) {
      $profile->options($options);
    }
    return $profile->getField();
  }

  /**
   * First check the $_profiles member variable for a registered profile class. If it is not
   * registered look for a class like "Papaya\Ui\Dialog\Field\Factory\Profile\$name". $name is
   * converted to camel case with the first letter uppercase.
   *
   * @param string $name
   * @throws Factory\Exception\InvalidProfile
   * @return string
   */
  private function getProfileClass($name) {
    $name = \Papaya\Utility\Text\Identifier::toCamelCase($name, TRUE);
    if (isset($this->_profiles[$name])) {
      return $this->_profiles[$name];
    }
    if (empty($name)) {
      return __CLASS__.'\\Profile\\Input';
    }
    $class = __CLASS__.'\\Profile\\'.$name;
    if (class_exists($class)) {
      return $class;
    }
    throw new Factory\Exception\InvalidProfile($name);
  }

  /**
   * Register profile classes. They must extend from \Papaya\Ui\Dialog\Field\Factory\Profile.
   *
   * The are not validated at this point. For a validation the need to be loaded and they may no be
   * needed.
   *
   * @param array|\Traversable $profiles
   */
  public function registerProfiles($profiles) {
    \Papaya\Utility\Constraints::assertArrayOrTraversable($profiles);
    foreach ($profiles as $name => $profile) {
      $this->_profiles[\Papaya\Utility\Text\Identifier::toCamelCase($name, TRUE)] = $profile;
    }
  }
}
