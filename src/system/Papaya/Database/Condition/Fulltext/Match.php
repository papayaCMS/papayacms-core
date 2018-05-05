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

class PapayaDatabaseConditionFulltextMatch extends \PapayaDatabaseConditionFulltext {

  /**
   * Get filters for MySQL MATCH command
   *
   * @param \PapayaParserSearchString $tokens
   * @param array $fields
   * @return string
   */
  protected function getFulltextCondition(\PapayaParserSearchString $tokens, array $fields) {
    $result = '';
    $fieldGroups = array();
    foreach ($fields as $field) {
      if (strpos($field, '.') !== FALSE) {
        $table = substr($field, 0, strpos($field, '.'));
      } else {
        $table = '';
      }
      $fieldGroups[$table][] = $field;
    }
    $fieldGroups = array_values($fieldGroups);
    for ($i = 0; $i < count($fieldGroups); $i++) {
      if ($i > 0) {
        $result .= ' OR '.$this->getMatchFilterLine($tokens, implode(',', $fieldGroups[$i]));
      } else {
        $result .= $this->getMatchFilterLine($tokens, implode(',', $fieldGroups[$i]));
      }
    }
    return $result;
  }

  /**
   * Get filter line for MySQL MATCH command
   *
   * @param \PapayaParserSearchString $tokens
   * @param string $fieldString
   * @return string
   */
  private function getMatchFilterLine(\PapayaParserSearchString $tokens, $fieldString) {
    $result = "";
    $connector = '';
    $indent = 0;
    foreach ($tokens as $token) {
      switch ($token['mode']) {
      case '(':
        $indent++;
        $result .= $connector.'(';
        $connector = '';
        break;
      case ')':
        $indent--;
        $result .= ')';
        break;
      case '+':
        $result .= sprintf(
          "%s(MATCH (%s) AGAINST ('%s'))",
          $connector,
          $fieldString,
          addslashes($token['value'])
        );
        $connector = " AND ";
        break;
      case '-':
        $result .= sprintf(
          "%s(NOT(MATCH (%s) AGAINST ('%s')))",
          $connector,
          $fieldString,
          addslashes($token['value'])
        );
        $connector = " AND";
        break;
      case ':':
        $connector = " ".$token['value'];
        continue;
      }
    }
    if ($indent > 0) {
      $result .= str_repeat(")", $indent);
    }
    return $result;
  }
}
