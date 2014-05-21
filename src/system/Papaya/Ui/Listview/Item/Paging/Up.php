<?php
/**
* Provides several links to navigate to the next pages of a list in a listview. This
* output links to pages with a higher number.
*
* @copyright 2011 by papaya Software GmbH - All rights reserved.
* @link http://www.papaya-cms.com/
* @license http://www.gnu.org/licenses/old-licenses/gpl-2.0.html GNU General Public License, version 2
*
* You can redistribute and/or modify this script under the terms of the GNU General Public
* License (GPL) version 2, provided that the copyright and license notes, including these
* lines, remain unmodified. papaya is distributed in the hope that it will be useful, but
* WITHOUT ANY WARRANTY; without even the implied warranty of MERCHANTABILITY or FITNESS
* FOR A PARTICULAR PURPOSE.
*
* @package Papaya-Library
* @subpackage Ui
* @version $Id: Up.php 36336 2011-10-20 13:58:07Z weinert $
*/

/**
* Provides several links to navigate to the next pages of a list in a listview. This
* output links to pages with a higher number.
*
* @package Papaya-Library
* @subpackage Ui
*/
class PapayaUiListviewItemPagingUp extends PapayaUiListviewItemPaging {

  protected $_image = 'actions-go-next';

  /**
  * Provide pages with a higher page number than the current page
  *
  * @return array
  */
  public function getPages() {
    $minimum = $this->getCurrentPage() + 1;
    $maximum = $this->getCurrentPage() + $this->_pageLimit;
    $lastPage = $this->getLastPage();
    if ($maximum > $lastPage) {
      $maximum = $lastPage;
    }
    $pages = array();
    for ($i = $minimum; $i <= $maximum; ++$i) {
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
    $next = $this->getCurrentPage() + 1;
    $lastPage = $this->getLastPage();
    return ($lastPage < $next) ? $lastPage : $next;
  }
}