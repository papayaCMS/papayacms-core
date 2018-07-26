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
* An interface to define that an object has information (in an subobject)
* which conditions decide how and if it is cachable.
*
* @package Papaya-Library
* @subpackage Plugins
*/
interface PapayaPluginCacheable {

  /**
   * An subobject implementing {@see \Papaya\Cache\Identifier\PapayaCacheIdentifierDefinition} compiling the cache parameter
   * status
   *
   * @param \Papaya\Cache\Identifier\Definition $definition
   * @return \Papaya\Cache\Identifier\Definition
   */
  function cacheable(\Papaya\Cache\Identifier\Definition $definition = NULL);

}
