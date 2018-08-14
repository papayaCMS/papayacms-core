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

namespace Papaya\UI\Listview\Item\Paging;
/**
 * Provides several links to navigate to previous pages of a list in a listview. This
 * output links to pages with a lower number.
 *
 * @package Papaya-Library
 * @subpackage UI
 */
class Down extends \Papaya\UI\Listview\Item\Paging {

  protected $_image = 'actions-go-previous';

  /**
   * Provide pages with a lower page number than the current page
   *
   * @return array
   */
  public function getPages() {
    $minimum = $this->getCurrentPage() - $this->_pageLimit;
    $maximum = $this->getCurrentPage();
    if ($minimum < 1) {
      $minimum = 1;
    }
    $pages = array();
    for ($i = $minimum; $i < $maximum; ++$i) {
      $pages[] = $i;
    }
    return $pages;
  }

  /**
   * Return the page that will be used for the image link
   *
   * @return integer
   */
  public function getImagePage() {
    $previous = $this->getCurrentPage() - 1;
    return ($previous < 1) ? 1 : $previous;
  }
}