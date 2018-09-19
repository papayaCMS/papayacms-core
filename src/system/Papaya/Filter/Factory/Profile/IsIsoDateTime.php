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

/**
 * Profile creating a filter for a  ISO 8601 datetime string
 *
 * @package Papaya-Library
 * @subpackage Filter
 */
class IsIsoDateTime extends \Papaya\Filter\Factory\Profile {
  /**
   * @see \Papaya\Filter\Factory\Profile::getFilter()
   */
  public function getFilter() {
    return new \Papaya\Filter\RegEx(
      '(
        ^
        ([12]\d{3})-(\d|(0\d)|(1[0-2]))-(([012]?\d)|(3[01]))
        [\sT]
        ([01]\d|2[0-4]):([0-5]\d)(\:([\0-5]\d))?
        (\s*[-+]\d{4})?
        $
       )Dux'
    );
  }
}
