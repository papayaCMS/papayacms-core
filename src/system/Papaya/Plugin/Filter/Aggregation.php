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
 * This a standard implementation for content/data filters usage in a plugin.
 *
 * It provides access to a filters() methods that returns a PapayaPluginFilterContent
 * instance.
 *
 * To use the filters call prepare()/applyTo()/appendTo() in the
 * your PapayaPluginAppendable::appendTo() method.
 *
 * @package Papaya-Library
 * @subpackage Plugins
 */
trait PapayaPluginFilterAggregation {

  /**
   * @var PapayaPluginFilterContentRecords
   */
  private $_contentFilters;

  /**
   * @var base_topic|PapayaUiContentPage
   */
  private $_page;


  /**
   * @param PapayaPluginFilterContent|NULL $filters
   *
   * @return PapayaPluginFilterContent
   */
  public function filters(PapayaPluginFilterContent $filters = NULL) {
    if ($filters !== NULL) {
      $this->_contentFilters = $filters;
    } elseif (NULL === $this->_contentFilters) {
      $this->_contentFilters = new \PapayaPluginFilterContentRecords($this->getPage());
    }
    return $this->_contentFilters;
  }

  /**
   * Page modules get the page object as their constructor argument.
   * This implementation expects that it was stored in the private
   * field $_page.
   *
   * @return base_topic|PapayaUiContentPage
   */
  public function getPage() {
    return $this->_page;
  }
}
