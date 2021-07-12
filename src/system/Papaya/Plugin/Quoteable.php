<?php
/*
 * papaya CMS
 *
 * @copyright 2000-2021 by papayaCMS project - All rights reserved.
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

use Papaya\XML;

/**
 * An interface to define that an plugin with a teaser appendable to an DOM element. It
 * is provides an additional method to \Papaya\Plugin\Appendable to append a "quote"/short version
 * of the content to the DOM.
 *
 * @package Papaya-Library
 * @subpackage Plugins
 */
interface Quoteable {
  /**
   * Append short content (aka "quote") to the parent xml element.
   *
   * @param XML\Element $parent
   *
   * @return null|XML\Element
   */
  public function appendQuoteTo(XML\Element $parent);
}
