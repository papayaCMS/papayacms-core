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
namespace Papaya\Database\Condition\Fulltext;

use Papaya\Database;
use Papaya\Parser;
use Papaya\Parser\Search\Text as SearchStringParser;

class Contains extends Database\Condition\Fulltext {
  /**
   * Get filters for a LIKE condition
   *
   * @param SearchStringParser $tokens
   * @param array $fields
   *
   * @return string
   */
  protected function getFulltextCondition(SearchStringParser $tokens, array $fields) {
    $result = '';
    $connector = '';
    $indent = 0;
    foreach ($tokens as $token) {
      switch ($token['mode']) {
      case SearchStringParser::TOKEN_PARENTHESIS_START:
        $indent++;
        $result .= $connector.'(';
        $connector = '';
        break;
      case SearchStringParser::TOKEN_PARENTHESIS_END:
        $indent--;
        $result .= ')';
        break;
      case SearchStringParser::TOKEN_INCLUDE:
        $result .= $connector.'(';
        $s = '';
        foreach ($fields as $field) {
          $result .= $s.\sprintf(
              "(%s LIKE '%%%s%%')",
              $field,
              \addslashes($token['value'])
            );
          $s = ' OR ';
        }
        $result .= ')';
        $connector = "\n AND \n";
        break;
      case SearchStringParser::TOKEN_EXCLUDE:
        $result .= $connector.'(NOT(';
        $s = '';
        foreach ($fields as $field) {
          $result .= $s.\sprintf(
              "(%s LIKE '%%%s%%')",
              $field,
              \addslashes($token['value'])
            );
          $s = ' OR ';
        }
        $result .= '))';
        $connector = "\n AND \n";
        break;
      case SearchStringParser::TOKEN_OPERATOR:
        $connector = "\n ".$token['value'];
        break;
      }
    }
    if ($indent > 0) {
      $result .= \str_repeat("\n)", $indent);
    }
    return $result;
  }
}
