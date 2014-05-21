<?php
/**
* Interface defining a Getter/Setter for the hook context.
*
* @copyright 2013 by papaya Software GmbH - All rights reserved.
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
* @subpackage Plugins
* @version $Id: Hookable.php 39416 2014-02-27 17:02:47Z weinert $
*/

/**
* An interface to define that an object is hookable and needs a context.
*
* The two methods provide access to the stored/edited content and the editor subsubject.
*
* @package Papaya-Library
* @subpackage Plugins
*/
interface PapayaPluginHookable {

  /**
   * Getter/Setter for the hook context.
   *
   * @param \PapayaPluginEditableContent $context
   * @return PapayaPluginEditableContent
   */
  function context(PapayaPluginEditableContent $context = NULL);
}
