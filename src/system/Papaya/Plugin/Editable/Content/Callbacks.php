<?php
/**
* Callbacks for the plugin editable content
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
* @version $Id: Callbacks.php 38384 2013-04-10 14:50:30Z weinert $
*/

/**
* Callbacks for the plugin editable content
*
* @package Papaya-Library
* @subpackage Plugins
*
* @property PapayaObjectCallback $onCreateEditor
* @method PapayaPluginEditor onCreateEditor
*/
class PapayaPluginEditableContentCallbacks extends PapayaObjectCallbacks {

  public function __construct() {
    parent::__construct(
      array(
        'onCreateEditor' => NULL
      )
    );
  }
}