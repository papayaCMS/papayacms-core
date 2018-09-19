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
 * Profile creating a server address (host name or ip)
 *
 * @package Papaya-Library
 * @subpackage Filter
 */
class IsServerAddress extends \Papaya\Filter\Factory\Profile {
  /**
   * @see \Papaya\Filter\Factory\Profile::getFilter()
   */
  public function getFilter() {
    return new \Papaya\Filter\LogicalOr(
      new \Papaya\Filter\URL\Host(),
      new \Papaya\Filter\Ip\V4(),
      new \Papaya\Filter\Ip\V6()
    );
  }
}
