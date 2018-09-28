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
namespace Papaya\Plugin\Editable\Content;

use Papaya\Plugin;

/**
 * This a standard implementation for editable plugin content. It
 * makes implements the \Papaya\Plugin\Editable interface and
 * expects an implementation of the abstract method "createEditor".
 *
 * The method needs to return a \Papaya\Plugin\Editor instance.
 *
 * @package Papaya-Library
 * @subpackage Plugins
 */
trait Aggregation {
  /**
   * @var Plugin\Editable\Content
   */
  private $_content;

  /**
   * The content is an {@see ArrayObject} child class containing the stored data.
   *
   * @see \Papaya\Plugin\Editable::content()
   *
   * @param Plugin\Editable\Content $content
   *
   * @return Plugin\Editable\Content
   */
  public function content(Plugin\Editable\Content $content = NULL) {
    if (NULL !== $content) {
      $this->_content = $content;
    } elseif (NULL === $this->_content) {
      $this->_content = new Plugin\Editable\Content();
      $this->_content->callbacks()->onCreateEditor = function(
        /** @noinspection PhpUnusedParameterInspection */
        $callbackContext, Plugin\Editable\Content $content
      ) {
        return $this->createEditor($content);
      };
    }
    return $this->_content;
  }

  /**
   * @param Plugin\Editable\Content $content
   *
   * @return \Papaya\Plugin\Editor
   */
  abstract public function createEditor(Plugin\Editable\Content $content);
}
