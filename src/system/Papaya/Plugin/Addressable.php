<?php
/**
 * Allow to validate if the current url is a valid address for the content/plugin
 *
 * @copyright 2016 by papaya Software GmbH - All rights reserved.
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
 * @version $Id: Editable.php 39416 2014-02-27 17:02:47Z weinert $
 */

/**
 * An interface to define that an object is editable.
 *
 * Allow to validate if the current url is a valid address for the content/plugin
 *
 * @package Papaya-Library
 * @subpackage Plugins
 */
interface PapayaPluginAddressable {

  /**
   * Getter/Setter for the content.
   *
   * @param PapayaRequest $request
   * @return FALSE|string|TRUE
   */
  function validateUrl(PapayaRequest $request);
}
