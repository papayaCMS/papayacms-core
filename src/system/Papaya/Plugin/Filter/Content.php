<?php
/**
* An interface to define that allows to apply filter to a string content
*
* @copyright 2014 by papaya Software GmbH - All rights reserved.
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
* @version $Id: Content.php 39687 2014-03-24 15:01:05Z weinert $
*/

/**
* An interface to define that allows to apply filter to a string content
*
* @package Papaya-Library
* @subpackage Plugins
*/
interface PapayaPluginFilterContent extends PapayaXmlAppendable {

  /**
   * @param string $content
   * @param PapayaObjectParameters $options
   */
  function prepare($content, PapayaObjectParameters $options = NULL);

  /**
   * @param string $content
   * @return string
   */
  function applyTo($content);
}