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

class Boolean extends Database\Condition\Fulltext {
  /**
   * Get filters for MySQL MATCH command
   *
   * @param Parser\Search\Text $tokens
   * @param array $fields
   *
   * @return string
   */
  protected function getFulltextCondition(Parser\Search\Text $tokens, array $fields) {
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
   * @param Parser\Search\Text $tokens
   * @param string $fieldString
   *
   * @return string
   */
  private function getBooleanFilterLine(Parser\Search\Text $tokens, $fieldString) {
    $connector = '';
    $indent = 0;
    $matchString = '';
    foreach ($tokens as $token) {
      switch ($token['mode']) {
      case '(':
        $indent++;
        $matchString .= $connector.' (';
        $connector = '';
        break;
      case ')':
        $indent--;
        $matchString .= ') ';
        break;
      case '+':
        if ($token['quotes']) {
          $matchString .= ' +"'.\addslashes($token['value']).'"';
        } else {
          $matchString .= ' +'.\addslashes($token['value']);
        }
        break;
      case '-':
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
