<?php
/**
* An interface to define that an object is a plugin appendable to an DOM element. This
* extends PapayaXmlAppendable to provide the additional information that it is an content plugin.
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
* @version $Id: Configurable.php 39505 2014-03-04 11:17:21Z weinert $
*/

/**
* An interface to define that an object is a plugin appendable to an DOM element. This
* extends PapayaXmlAppendable to provide the additional information that it is an content plugin.
*
* @package Papaya-Library
* @subpackage Plugins
*/
interface PapayaPluginConfigurable {

  /**
   * @param PapayaObjectParameters $configuration
   * @return PapayaObjectParameters
   */
  public function configuration(PapayaObjectParameters $configuration = NULL);
}