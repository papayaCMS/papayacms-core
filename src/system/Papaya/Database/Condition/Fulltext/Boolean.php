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

class Boolean extends \Papaya\Database\Condition\Fulltext {

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
      if (FALSE !== strpos($field, '.')) {
        $table = substr($field, 0, strpos($field, '.'));
      } else {
        $table = '';
      }
      $fieldGroups[$table][] = $field;
    }
    $fieldGroups = array_values($fieldGroups);
    for ($i = 0, $c = count($fieldGroups); $i < $c; $i++) {
      if ($i > 0) {
        $result .= 'OR '.$this->getBooleanFilterLine($tokens, implode(',', $fieldGroups[$i]));
      } else {
        $result .= $this->getBooleanFilterLine($tokens, implode(',', $fieldGroups[$i]));
      }
    }
    return $result;
  }

  /**
   * Get Filters for MySQL MATCH Command in Boolean Mode (MySQL > 4.1)
   *
   * @param \PapayaParserSearchString $tokens
   * @param string $fieldString
   * @access public
   * @return string
   */
  private function getBooleanFilterLine(\PapayaParserSearchString $tokens, $fieldString) {
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
          $matchString .= ' +"'.addslashes($token['value']).'"';
        } else {
          $matchString .= ' +'.addslashes($token['value']);
        }
        break;
      case '-':
        if ($token['quotes']) {
          $matchString .= ' -"'.addslashes($token['value']).'"';
        } else {
          $matchString .= ' -'.addslashes($token['value']);
        }
        break;
      case ':':
        break;
      }
    }
    if ($indent > 0) {
      $matchString .= str_repeat(' )', $indent);
    }
    return sprintf(
      "(MATCH (%s) AGAINST ('%s' IN BOOLEAN MODE))", $fieldString, $matchString
    );
  }
}
