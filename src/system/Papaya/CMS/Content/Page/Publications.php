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
namespace Papaya\CMS\Content\Page;

use Papaya\CMS\Content;

/**
 * This object loads public page data by different conditions.
 *
 * @package Papaya-Library
 * @subpackage Content
 */
class Publications extends Content\Pages {
  /**
   * Table containing page information
   *
   * @var string
   */
  protected $_tablePages = Content\Tables::PAGE_PUBLICATIONS;

  /**
   * Table containing language specific page information
   *
   * @var string
   */
  protected $_tablePageTranslations = Content\Tables::PAGE_PUBLICATION_TRANSLATIONS;

  /**
   * Loading published stuff using a timestamp so that only pages are loaded, that are really
   * accessible.
   *
   * @param mixed $filter
   * @param string $prefix
   *
   * @return string
   */
  protected function _compileCondition($filter, $prefix = 'WHERE') {
    $conditions = parent::_compileCondition($filter, $prefix);
    if (isset($filter['time'])) {
      $conditions .= empty($conditions) ? $prefix : ' AND ';
      $conditions .= \sprintf(
        " ((t.published_from <= '%1\$d' AND t.published_to >= '%1\$d')
         OR t.published_to <= t.published_from)",
        (int)$filter['time']
      );
    }
    return $conditions;
  }

  /**
   * @return bool
   */
  public function isPublic() {
    return TRUE;
  }
}
