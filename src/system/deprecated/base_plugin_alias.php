<?php
/**
* basic object for all alias plugin objects
*
* alias plugins can implement special redirects (404 file handling)
*
* @copyright 2002-2007 by papaya Software GmbH - All rights reserved.
* @link http://www.papaya-cms.com/
* @license http://www.gnu.org/licenses/old-licenses/gpl-2.0.html GNU General Public License, version 2
*
* You can redistribute and/or modify this script under the terms of the GNU General Public
* License (GPL) version 2, provided that the copyright and license notes, including these
* lines, remain unmodified. papaya is distributed in the hope that it will be useful, but
* WITHOUT ANY WARRANTY; without even the implied warranty of MERCHANTABILITY or FITNESS
* FOR A PARTICULAR PURPOSE.
*
* @package Papaya
* @subpackage Modules
* @version $Id: base_plugin_alias.php 39260 2014-02-18 17:13:06Z weinert $
*/

/**
* basic object for all alias plugin objects
*
* alias plugins can implement special redirects (404 file handling)
*
* @package Papaya
* @subpackage Modules
*/
class base_plugin_alias extends base_plugin {

  /**
  * this function needs to be overloaded by the alias plugin class
  * if it returns FALSE, papaya CMS will output the 404 error page
  *
  * @param string $urlPart url part without alias and trailing slash
  * @access public
  * @return boolean | string - FALSE, TRUE or redirect URL
  */
  function redirect($urlPart) {
    return FALSE;
  }

  /**
   * Log events - currently not supported in alias modules.
   * Hint: If you have to debug this module you can use X-Headers.
   *
   * @return boolean FALSE
   */
  function logVariable() {
    return FALSE;
  }

  /**
   * Log events - currently not supported in alias modules
   * Hint: If you have to debug this module you can use X-Headers.
   *
   * @return boolean FALSE
   */
  function logMsg() {
    return FALSE;
  }
}

