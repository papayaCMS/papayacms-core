<?php
/**
* An interface for action on the file system
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
* @subpackage FileSystem
* @version $Id: Action.php 37289 2012-07-25 14:06:02Z weinert $
*/

/**
* An interface for action on the file system. This can be a script call or an stream wrapper read.
*
* @package Papaya-Library
* @subpackage FileSystem
*/
interface PapayaFileSystemAction {

  function execute(array $parameters);
}
