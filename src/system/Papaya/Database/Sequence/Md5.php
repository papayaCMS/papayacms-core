<?php
/**
* Generator for a rndomized unique id hashed with md5().
*
* @copyright 2010 by papaya Software GmbH - All rights reserved.
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
* @subpackage Database
* @version $Id: Md5.php 34436 2010-06-29 11:42:28Z weinert $
*/

/**
* Generator for a rndomized unique id hashed with md5().
*
* Usage:
*   $sequence = new PapayaDatabaseSequenceMd5(
*     'tablename', 'fieldname', 5
*   );
*   $newId = $sequence->next();
*
* @package Papaya-Library
* @subpackage Database
*/
class PapayaDatabaseSequenceMd5 extends PapayaDatabaseSequence {

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
