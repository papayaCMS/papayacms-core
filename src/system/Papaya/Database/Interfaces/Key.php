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
namespace Papaya\Database\Interfaces;

interface Key {
  /**
   * Quality: key provided by DBMS
   */
  const DATABASE_PROVIDED = 1;

  /**
   * Quality: key generated by code
   */
  const CLIENT_GENERATED = 2;

  /**
   * Filter used for WHERE
   */
  const ACTION_FILTER = 1;

  /**
   * Filter used for CREATE action
   */
  const ACTION_CREATE = 2;

  /**
   * @return bool
   */
  public function clear();

  /**
   * @param array $data
   */
  public function assign(array $data);

  /**
   * @return array
   */
  public function getProperties();

  /**
   * @param int $for
   * @return array
   */
  public function getFilter($for = self::ACTION_FILTER);

  /**
   * @return int
   */
  public function getQualities();

  /**
   * @return bool
   */
  public function exists();

  /**
   * @return string
   */
  public function __toString();
}
