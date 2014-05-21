<?php
/**
* Interface for message contexts
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
* @subpackage Messages
* @version $Id: Interface.php 34081 2010-04-23 15:20:30Z weinert $
*/

/**
* Interface for message contexts
*
* A message context describes additional information to a message, the intention is that the
* dispatcher has only to know the type of the data structure, not the actual content
*
* @package Papaya-Library
* @subpackage Messages
*/
interface PapayaMessageContextInterface {
}