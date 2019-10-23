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
namespace Papaya\Database\Condition;

use Papaya\Parser\Search\Text as SearchTextParser;

abstract class Fulltext extends Condition {

  protected $_fields = '';

  protected $_searchFor = '';

  /**
   * Fulltext constructor.
   *
   * @param Group $parent
   * @param string|string[] $fields
   * @param string $searchFor
   */
  public function __construct(
    Group $parent, $fields, $searchFor
  ) {
    parent::__construct($parent);
    $this->_fields = \is_array($fields) ? $fields : [$fields];
    $this->_searchFor = $searchFor;
  }

  /**
   * @param SearchTextParser $tokens
   * @param array|\Traversable $fields
   *
   * @return string
   */
  abstract protected function getFullTextCondition(SearchTextParser $tokens, array $fields);

  /**
   * @param bool $silent
   * @return string
   */
  public function getSql($silent = FALSE) {
    try {
      $tokens = new SearchTextParser($this->_searchFor);
      return $this->getFullTextCondition(
        $tokens,
        \array_map(
          function($fieldName) {
            return $this->mapFieldName($fieldName);
          },
          $this->_fields
        )
      );
    } catch (\LogicException $e) {
      if (!$silent) {
        throw $e;
      }
      return '';
    }
  }
}
