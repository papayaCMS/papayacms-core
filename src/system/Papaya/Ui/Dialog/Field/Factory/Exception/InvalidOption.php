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

namespace Papaya\Ui\Dialog\Field\Factory\Exception;
/**
 * The option name is invalid, aka the option does not exist
 *
 * @package Papaya-Library
 * @subpackage Ui
 */
class InvalidOption
  extends \Papaya\Ui\Dialog\Field\Factory\Exception {

  /**
   * Create exception with compiled message
   *
   * @param string $optionName
   */
  public function __construct($optionName) {
    parent::__construct(
      sprintf('Invalid field factory option name "%s".', $optionName)
    );
  }
}