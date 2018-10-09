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
namespace Papaya\UI\Dialog\Field\Factory;

/**
 * Abstract superclass for field factory profiles.
 *
 * Each profile defines how a field {@see \Papaya\UI\Dialog\Field} is created for a specified
 * type. Here is an options subobject to provide data for the field configuration.
 *
 * @package Papaya-Library
 * @subpackage UI
 */
abstract class Profile {
  /**
   * @var Options
   */
  private $_options;

  /**
   * Create the field and return it. Throw an exception if something goes wrong
   *
   * @return \Papaya\UI\Dialog\Field
   *
   * @throws \Papaya\UI\Dialog\Field\Factory\Exception
   */
  abstract public function getField();

  /**
   * Getter/Setter for the options subobject
   *
   * @param Options $options
   *
   * @return Options
   *
   * @throws \Papaya\UI\Dialog\Field\Factory\Exception\InvalidOption
   */
  public function options(Options $options = NULL) {
    if (NULL !== $options) {
      $this->_options = $options;
    } elseif (NULL === $this->_options) {
      $this->_options = new Options();
    }
    return $this->_options;
  }
}
