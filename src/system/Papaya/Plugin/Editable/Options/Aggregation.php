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
namespace Papaya\Plugin\Editable\Options;

/**
 * This a standard implementation for editable plugin options. It
 * makes implements the \Papaya\Plugin\Editable interface and
 * expects an implementation of the abstract method "createOptionsEditor".
 *
 * The method needs to return a \Papaya\Plugin\Editor instance.
 *
 * @package Papaya-Library
 * @subpackage Plugins
 */
trait Aggregation {
  /**
   * @var \Papaya\Plugin\Editable\Content
   */
  private $_options;

  /**
   * The content is an {@see ArrayObject} child class containing the stored data.
   *
   * @see \Papaya\Plugin\Adaptable::options()
   *
   * @param \Papaya\Plugin\Editable\Options $options
   *
   * @return \Papaya\Plugin\Editable\Options
   */
  public function options(\Papaya\Plugin\Editable\Options $options = NULL) {
    if (NULL !== $options) {
      $this->_options = $options;
    } elseif (NULL === $this->_options) {
      $this->_options = new \Papaya\Plugin\Editable\Options(
        new \Papaya\Plugin\Options($this->getPluginGuid())
      );
      $this->_options->callbacks()->onCreateEditor = function($context, \Papaya\Plugin\Editable\Options $content) {
        return $this->createOptionsEditor($content);
      };
    }
    return $this->_options;
  }

  /**
   * @param \Papaya\Plugin\Editable\Options $content
   *
   * @return \Papaya\Plugin\Editor
   */
  abstract public function createOptionsEditor(\Papaya\Plugin\Editable\Options $content);

  /**
   * The plugin guid will be set as a public property by the plugin manager.
   *
   * @return string
   */
  public function getPluginGuid() {
    if (isset($this->guid)) {
      return $this->guid;
    }
    throw new \LogicException('No plugin guid found.');
  }
}
