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
namespace Papaya\Filter\Factory\Profile;

use Papaya\Filter;

/**
 * Profile creating a filter for string that contains something except whitespaces
 *
 * @package Papaya-Library
 * @subpackage Filter
 */
class IsNotEmpty extends Filter\Factory\Profile {
  /**
   * @return \Papaya\Filter
   */
  public function getFilter() {
    return new Filter\NotEmpty();
  }
}
