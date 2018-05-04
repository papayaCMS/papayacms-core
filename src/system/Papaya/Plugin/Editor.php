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
* An abstract superclass for plugin content editors. They need access to the plugin,
* so it is stored in a buffer variable.
*
* @package Papaya-Library
* @subpackage Plugins
*/
abstract class PapayaPluginEditor extends PapayaUiControlInteractive {

  private $_content = NULL;
  private $_context = NULL;

  /**
   * Create object and store the editable content
   *
   * @param PapayaPluginEditableContent $content
   */
  public function __construct(PapayaPluginEditableContent $content) {
    $this->_content = $content;
  }

  /**
   * Return the stored content object.
   *
   * @return PapayaPluginEditableContent
   */
  public function getContent() {
    return $this->_content;
  }

  /**
   * The context specifies a parameter status needed to reach the editor/dialog. These
   * parameters need to be added to links and dialogs
   *
   * @param PapayaRequestParameters $context
   * @return PapayaRequestParameters
   */
  public function context(PapayaRequestParameters $context = NULL) {
    if (isset($context)) {
      $this->_context = $context;
    } elseif (NULL === $this->_context) {
      $this->_context = new \PapayaRequestParameters();
    }
    return $this->_context;
  }

}
