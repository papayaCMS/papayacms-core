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
namespace Papaya\Plugin\Hookable;

use Papaya\Plugin;
use Papaya\Utility;

/**
 * An context for an hookable plugin. Meaning that the current object provides context data
 * to the plugin. Like itself or another object and data in an parameters object
 *
 * @package Papaya-Library
 * @subpackage Plugins
 */
class Context {
  /**
   * @var null|object
   */
  private $_parent;

  /**
   * @var null|Plugin\Editable\Content
   */
  private $_data;

  /**
   * Create the context with data
   *
   * @param object $parent
   * @param Plugin\Editable\Content|array|\Traversable|null $data
   */
  public function __construct($parent = NULL, $data = NULL) {
    if (NULL !== $parent) {
      Utility\Constraints::assertObject($parent);
      $this->_parent = $parent;
    }
    if (NULL !== $data) {
      $this->data($data);
    }
  }

  /**
   * Check if an parent object was provided to the context.
   */
  public function hasParent() {
    return NULL !== $this->_parent;
  }

  /**
   * Return the parent object if it was provided
   */
  public function getParent() {
    if (NULL === $this->_parent) {
      throw new \LogicException('No parent object was provided for this context.');
    }
    return $this->_parent;
  }

  /**
   * Getter/Setter for the context data. If a \Papaya\Plugin\Editable\Content ist provided it will
   * be set a new context data, if an array or \Traversable ist provided a new editable content will be created an the
   * data assigned.
   *
   * @param Plugin\Editable\Content|array|\Traversable|null $data
   *
   * @return Plugin\Editable\Content
   */
  public function data($data = NULL) {
    if (NULL !== $data) {
      $this->_data = $data instanceof Plugin\Editable\Content ? $data : new Plugin\Editable\Content($data);
    } elseif (NULL === $this->_data) {
      $this->_data = new Plugin\Editable\Content();
    }
    return $this->_data;
  }
}
