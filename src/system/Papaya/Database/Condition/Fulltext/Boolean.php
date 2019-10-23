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

class Boolean extends Database\Condition\Fulltext {
  /**
   * Get filters for MySQL MATCH command
   *
   * @param SearchStringParser $tokens
   * @param array $fields
   *
   * @return string
   */
  protected function getFulltextCondition(SearchStringParser $tokens, array $fields) {
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
      \array_map(
        function(array $fieldGroup) use ($tokens) {
          return $this->getBooleanFilterLine($tokens, \implode(',', $fieldGroup));
        },
        $fieldGroups
      )
    );
  }

  /**
   * Get Filters for MySQL MATCH Command in Boolean Mode (MySQL > 4.1)
   *
   * @param SearchStringParser $tokens
   * @param string $fieldString
   *
   * @return string
   */
  private function getBooleanFilterLine(SearchStringParser $tokens, $fieldString) {
    $connector = '';
    $indent = 0;
    $matchString = '';
    foreach ($tokens as $token) {
      switch ($token['mode']) {
      case SearchStringParser::TOKEN_PARENTHESIS_START:
        $indent++;
        $matchString .= $connector.' (';
        $connector = '';
        break;
      case SearchStringParser::TOKEN_PARENTHESIS_END:
        $indent--;
        $matchString .= ') ';
        break;
      case SearchStringParser::TOKEN_INCLUDE:
        if ($token['quotes']) {
          $matchString .= ' +"'.\addslashes($token['value']).'"';
        } else {
          $matchString .= ' +'.\addslashes($token['value']);
        }
        break;
      case SearchStringParser::TOKEN_EXCLUDE:
        if ($token['quotes']) {
          $matchString .= ' -"'.\addslashes($token['value']).'"';
        } else {
          $matchString .= ' -'.\addslashes($token['value']);
        }
        break;
      case ':':
        break;
      }
    }
    if ($indent > 0) {
      $matchString .= \str_repeat(' )', $indent);
    }
    return \sprintf(
      "(MATCH (%s) AGAINST ('%s' IN BOOLEAN MODE))", $fieldString, $matchString
    );
  }
}
