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
namespace Papaya\Plugin\Filter;

/**
 * This a standard implementation for content/data filters usage in a plugin.
 *
 * It provides access to a filters() methods that returns a \Papaya\Plugin\Filter\Content
 * instance.
 *
 * To use the filters call prepare()/applyTo()/appendTo() in the
 * your \Papaya\Plugin\Appendable::appendTo() method.
 *
 * @package Papaya-Library
 * @subpackage Plugins
 */
trait Aggregation {
  /**
   * @var Content\Records
   */
  private $_contentFilters;

  /**
   * @var \base_topic|\Papaya\UI\Content\Page
   */
  private $_page;

  /**
   * @param Content|null $filters
   *
   * @return Content
   */
  public function filters(Content $filters = NULL) {
    if (NULL !== $filters) {
      $this->_contentFilters = $filters;
    } elseif (NULL === $this->_contentFilters) {
      $this->_contentFilters = new Content\Records($this->getPage());
    }
    return $this->_contentFilters;
  }

  /**
   * Page modules get the page object as their constructor argument.
   * This implementation expects that it was stored in the private
   * field $_page.
   *
   * @return \base_topic|\Papaya\UI\Content\Page
   */
  public function getPage() {
    return $this->_page;
  }
}
