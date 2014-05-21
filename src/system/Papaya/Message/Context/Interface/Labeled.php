<?php
/**
* Interface for an addition label for contexts.
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
* @version $Id: Labeled.php 34220 2010-05-12 09:04:44Z weinert $
*/

/**
* Interface for an addition label for contexts.
*
* Message contexts which implemeted this interface will get a title/label,
* depending on the dispatcher.
*
* @package Papaya-Library
* @subpackage Messages
*/
interface PapayaMessageContextInterfaceLabeled
  extends PapayaMessageContextInterface {

  /**
  * Get label for the context
  *
  * @return string
  */
  function getLabel();
}