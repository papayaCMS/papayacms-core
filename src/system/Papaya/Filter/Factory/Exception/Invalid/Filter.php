<?php
/**
* Exception: invalid filter class in profile
*
* @copyright 2012 by papaya Software GmbH - All rights reserved.
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
* @version $Id: Filter.php 37370 2012-08-06 14:40:01Z weinert $
*/

/**
* Exception: invalid filter class in profile
*
* @package Papaya-Library
* @subpackage Filter
*/
class PapayaFilterFactoryExceptionInvalidFilter extends PapayaFilterFactoryException {

  /**
   * @param string $name
   */
  public function __construct($name) {
    parent::__construct(
      sprintf('Can not use invalid filter class: "%s".', $name)
    );
  }

}
