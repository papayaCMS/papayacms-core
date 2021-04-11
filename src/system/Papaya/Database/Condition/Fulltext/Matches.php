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
use Papaya\Parser\Search\Text as SearchTextParser;

class Matches extends Database\Condition\Fulltext {
  /**
   * Get filters for MySQL MATCH command
   *
   * @param SearchTextParser $tokens
   * @param array $fields
   *
   * @return string
   */
  protected function getFulltextCondition(SearchTextParser $tokens, array $fields) {
    $fieldGroups = [];
    foreach ($fields as $field) {
      if (FALSE !== \strpos($field, '.')) {
        $table = \substr($field, 0, \strpos($field, '.'));
      } else {
        $table = '';
      }
      $fieldGroups[$table][] = $field;
    }
    return \implode(
      ' AND ',
      \array_map(
        function(array $fieldGroup) use ($tokens) {
          return $this->getMatchFilterLine($tokens, \implode(',', $fieldGroup));
        },
        $fieldGroups
      )
    );
  }

  /**
   * Get filter line for MySQL MATCH command
   *
   * @param SearchTextParser $tokens
   * @param string $fieldString
   *
   * @return string
   */
  private function getMatchFilterLine(SearchTextParser $tokens, $fieldString) {
    $result = '';
    $connector = '';
    $indent = 0;
    foreach ($tokens as $token) {
      switch ($token['mode']) {
      case SearchTextParser::TOKEN_PARENTHESIS_START:
        $indent++;
        $result .= $connector.'(';
        $connector = '';
        break;
      case SearchTextParser::TOKEN_PARENTHESIS_END:
        $indent--;
        $result .= ')';
        break;
      case SearchTextParser::TOKEN_INCLUDE:
        $result .= \sprintf(
          "%s(MATCH (%s) AGAINST ('%s'))",
          $connector,
          $fieldString,
          \addslashes($token['value'])
        );
        $connector = ' AND ';
        break;
      case SearchTextParser::TOKEN_EXCLUDE:
        $result .= \sprintf(
          "%s(NOT(MATCH (%s) AGAINST ('%s')))",
          $connector,
          $fieldString,
          \addslashes($token['value'])
        );
        $connector = ' AND';
        break;
      case SearchTextParser::TOKEN_OPERATOR:
        $connector = ' '.$token['value'].' ';
          break;
      }
    }
    if ($indent > 0) {
      $result .= \str_repeat(')', $indent);
    }
    return $result;
  }
}
