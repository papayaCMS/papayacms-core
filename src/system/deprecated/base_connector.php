<?php
/**
* basic class for connector plugins
*
* @copyright 2002-2009 by papaya Software GmbH - All rights reserved.
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
* @subpackage Modules
* @version $Id: base_connector.php 39260 2014-02-18 17:13:06Z weinert $
*/

/**
* basic class for connector plugin objects
*
* @package Papaya
* @subpackage Modules
*/
class base_connector extends base_plugin {

  /**
  * Set parent object (owner)
  * @param $aOwner
  * @return base_connector
  */
  public function __construct($aOwner = NULL) {
    $this->parentObj = $aOwner;
  }
}
