<?php
/**
* An interface to define that an object has information (in an subobject)
* which conditions decide how and if it is cachable.
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
* @version $Id: Cacheable.php 39416 2014-02-27 17:02:47Z weinert $
*/

/**
* An interface to define that an object has information (in an subobject)
* which conditions decide how and if it is cachable.
*
* @package Papaya-Library
* @subpackage Plugins
*/
interface PapayaPluginCacheable {

  /**
   * An subobject implementing {@see PapayaCacheIdentifierDefinition} compiling the cache parameter
   * status
   *
   * @param PapayaCacheIdentifierDefinition $definition
   * @return PapayaCacheIdentifierDefinition
   */
  function cacheable(PapayaCacheIdentifierDefinition $definition = NULL);

}
