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
namespace Papaya\UI\Dialog\Field\Factory\Exception;

use Papaya\UI;

/**
 * The option name is invalid, aka the option does not exist
 *
 * @package Papaya-Library
 * @subpackage UI
 */
class InvalidOption
  extends UI\Dialog\Field\Factory\Exception {
  /**
   * Create exception with compiled message
   *
   * @param string $optionName
   */
  public function __construct($optionName) {
    parent::__construct(
      \sprintf('Invalid field factory option name "%s".', $optionName)
    );
  }
}
