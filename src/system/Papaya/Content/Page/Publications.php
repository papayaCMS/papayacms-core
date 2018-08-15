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

namespace Papaya\Content\Page;
/**
 * This object loads public page data by different conditions.
 *
 * @package Papaya-Library
 * @subpackage Content
 */
class Publications extends \Papaya\Content\Pages {
  /**
   * Table containing page informations
   *
   * @var string
   */
  protected $_tablePages = \Papaya\Content\Tables::PAGE_PUBLICATIONS;

  /**
   * Table containing language specific page informations
   *
   * @var string
   */
  protected $_tablePageTranslations = \Papaya\Content\Tables::PAGE_PUBLICATION_TRANSLATIONS;

  /**
   * Loading published stuff using a timestamp so that only pages are loaded, that are really
   * accessible.
   *
   * @param mixed $filter
   * @param string $prefix
   * @return array
   */
  protected function _compileCondition($filter, $prefix = 'WHERE') {
    $conditions = parent::_compileCondition($filter, $prefix);
    if (isset($filter['time'])) {
      $conditions .= empty($conditions) ? $prefix : ' AND ';
      $conditions .= sprintf(
        " ((t.published_from <= '%1\$d' AND t.published_to >= '%1\$d')
         OR t.published_to <= t.published_from)",
        (int)$filter['time']
      );
    }
    return $conditions;
  }

  public function isPublic() {
    return TRUE;
  }
}
