<?php
/**
* Superclass for dialog element description. In the most cases this is a separate page opened
* directly or in an popup, so it needs a reference
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
* @subpackage Ui
* @version $Id: Description.php 36780 2012-02-29 12:31:36Z weinert $
*/

/**
* Superclass for dialog element description. In the most cases this is a separate page opened
* directly or in an popup, so it needs a reference
*
* For simple text information the dialog fields use the "hint".
*
* @package Papaya-Library
* @subpackage Ui
*
* @codeCoverageIgnore
*/
class PapayaUiDialogElementDescription extends PapayaUiControlCollection {

  protected $_itemClass = 'PapayaUiDialogElementDescriptionItem';

  protected $_tagName = 'description';
}