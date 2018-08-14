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

namespace Papaya\Database\Sequence;
/**
 * Generator for a rndomized unique id hashed with md5().
 *
 * Usage:
 *   $sequence = new \Papaya\Database\Sequence\Md5(
 *     'tablename', 'fieldname', 5
 *   );
 *   $newId = $sequence->next();
 *
 * @package Papaya-Library
 * @subpackage Database
 */
class Md5 extends \Papaya\Database\Sequence {

  /**
   * Generate a random, unqiue id and use md5 to hash it
   *
   * @return string
   */
  public function create() {
    return md5(
      uniqid(
        function_exists('mt_rand' ? mt_rand() : rand()),
        TRUE
      )
    );
  }
}
