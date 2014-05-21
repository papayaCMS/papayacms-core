<?php
/**
* An interface to define that an plugin provides a list of traversable key => value list of
* named attributes.
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
* @subpackage Plugins
* @version $Id: Assignable.php 39730 2014-04-07 21:05:30Z weinert $
*/

/**
* An interface to define that an plugin provides a list of traversable key => value list of
* named attributes.
*
* @package Papaya-Library
* @subpackage Plugins
*/
interface PapayaPluginAssignable {

  /**
   * Return a traversable key => value list of attributes
   *
   * @return Traversable
   */
  function getAttributes();

}