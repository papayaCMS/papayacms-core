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
namespace Papaya\CMS\Plugin\Options {

  use Papaya\Plugin\Editable;
  use Papaya\CMS\Plugin;

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
     * @var Editable\Content
     */
    private $_options;

    /**
     * The content is an {@see ArrayObject} child class containing the stored data.
     *
     * @param Editable\Options $options
     * @return Editable\Options
     */
    public function options(Editable\Options $options = NULL) {
      if (NULL !== $options) {
        $this->_options = $options;
      } elseif (NULL === $this->_options) {
        $this->_options = new Editable\Options(
          new Plugin\Options($this->getPluginGuid())
        );
        $this->_options->callbacks()->onCreateEditor = function (
          /** @noinspection PhpUnusedParameterInspection */
          $context, Editable\Options $options
        ) {
          return $this->createOptionsEditor($options);
        };
      }
      return $this->_options;
    }

    /**
     * @param Editable\Options $options
     *
     * @return \Papaya\Plugin\Editor
     */
    abstract public function createOptionsEditor(Editable\Options $options);

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
}
