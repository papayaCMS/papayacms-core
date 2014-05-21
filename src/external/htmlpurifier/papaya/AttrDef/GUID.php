<?php
/**
 * HTML Purifier: papaya CMS GUID checker
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
 * @subpackage Validation
 * @version $Id: GUID.php 32578 2009-10-14 14:03:56Z weinert $
 */

/**
 * load parent filter class
 */
require_once HTMLPURIFIER_INCLUDE_PATH.'HTMLPurifier/Filter.php';
require_once PAPAYA_INCLUDE_PATH.'system/sys_checkit.php';

/**
 * HTML purifier attr check if valid guid
 *
 * @package Papaya
 * @subpackage Validation
 */
class HTMLPurifier_AttrDef_GUID extends HTMLPurifier_AttrDef {
  function validate($string, $config, &$context) {
    return checkit::isGUID($string) ? $string : '';
  }
}
?>