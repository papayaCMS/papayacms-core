<?php
/**
 * This exception is thrown if an invalid key in an array is encountered.
 *
 * @copyright 2011 by papaya Software GmbH - All rights reserved.
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
 * @subpackage Filter
 * @version $Id: Invalid.php 35397 2011-02-04 15:52:49Z rekowski $
 */

/**
 * This exception is thrown if an invalid key in an array is encountered.
 *
 * @package Papaya-Library
 * @subpackage Filter
 */
class PapayaFilterExceptionArrayKeyInvalid extends PapayaFilterException {

  /**
   * The constructor expects the name of the invalid key
   *
   * @param string $key
   */
  public function __construct($key) {
    parent::__construct(
      sprintf(
        'Invalid key "%s" in array.',
        $key
      )
    );
  }
}