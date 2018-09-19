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
namespace Papaya\Plugin;

/**
 * An abstract superclass for plugin content editors. They need access to the plugin,
 * so it is stored in a buffer variable.
 *
 * @package Papaya-Library
 * @subpackage Plugins
 */
abstract class Editor extends \Papaya\UI\Control\Interactive {
  /**
   * @var Editable\Data
   */
  private $_data;

  /**
   * @var \Papaya\Request\Parameters
   */
  private $_context;

  /**
   * Create object and store the editable content
   *
   * @param Editable\Data $data
   */
  public function __construct(Editable\Data $data) {
    $this->_data = $data;
  }

  /**
   * Return the stored data object.
   *
   * @return Editable\Data
   */
  public function getData() {
    return $this->_data;
  }

  /**
   * Return the stored data object - bc for old API
   *
   * @deprecated
   *
   * @return Editable\Data
   */
  public function getContent() {
    return $this->getData();
  }

  /**
   * The context specifies a parameter status needed to reach the editor/dialog. These
   * parameters need to be added to links and dialogs
   *
   * @param \Papaya\Request\Parameters $context
   *
   * @return \Papaya\Request\Parameters
   */
  public function context(\Papaya\Request\Parameters $context = NULL) {
    if (NULL !== $context) {
      $this->_context = $context;
    } elseif (NULL === $this->_context) {
      $this->_context = new \Papaya\Request\Parameters();
    }
    return $this->_context;
  }
}
