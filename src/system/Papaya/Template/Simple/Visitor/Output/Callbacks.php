<?php
/**
* Callbacks that are used by the record mapping object
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
* @subpackage Database
* @version $Id: Callbacks.php 37635 2012-11-05 18:29:02Z weinert $
*/

/**
* Callbacks that are used by the simple template output visitor
*
* @package Papaya-Library
* @subpackage Database
*
* @property PapayaObjectCallback $onGetValue
* @method string onGetValue
*/
class PapayaTemplateSimpleVisitorOutputCallbacks extends PapayaObjectCallbacks {

  public function __construct() {
    parent::__construct(
      array(
        'onGetValue' => NULL
      )
    );
  }
}