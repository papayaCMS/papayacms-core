<?php
/**
 * papaya CMS
 *
 * @copyright 2000-2018 by papayaCMS project - All rights reserved.
 * @link http://www.papaya-cms.com/
 * @license http://www.gnu.org/licenses/old-licenses/gpl-2.0.html GNU General Public License, version 2
 *
 *  You can redistribute and/or modify this script under the terms of the GNU General Public
 *  License (GPL) version 2, provided that the copyright and license notes, including these
 *  lines, remain unmodified. papaya is distributed in the hope that it will be useful, but
 *  WITHOUT ANY WARRANTY; without even the implied warranty of MERCHANTABILITY or FITNESS
 *  FOR A PARTICULAR PURPOSE.
 */

/**
* Define a interface of additional functions implemented in \PapayaXmlDocument and \PapayaXmlElement.
*
* The interface allows to use type hints for appendTo() methods.
*
* @package Papaya-Library
* @subpackage Xml
*/
interface PapayaXmlNodeInterface {
  public function appendElement($name, array $attributes = array(), $content = NULL);
  public function appendXml($content);
}
