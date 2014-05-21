<?php
/**
* Interface definition for the page synchronization actions.
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
* @subpackage Administration
* @version $Id: Synchronization.php 36009 2011-08-01 14:43:24Z weinert $
*/

/**
* Interface definition for the page synchronization actions.
*
* @package Papaya-Library
* @subpackage Administration
*/
interface PapayaAdministrationPagesDependencySynchronization {

  /**
  * Synchronize a dependency
  *
  * @param array $targetIds
  * @param integer $originId
  * @param array|NULL $languages
  */
  function synchronize(array $targetIds, $originId, array $languages = NULL);
}