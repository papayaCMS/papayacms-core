<?php

class PapayaDatabaseConditionFulltextContains extends PapayaDatabaseConditionFulltext {

  /**
   * Get filters for a LIKE condition
   *
   * @param PapayaParserSearchString $tokens
   * @param array $fields
   * @return string
   */
  protected function getFulltextCondition(PapayaParserSearchString $tokens, array $fields) {
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
        $result .= $connector.'(';
        $s = '';
        foreach ($fields as $field) {
          $result .= $s.sprintf(
              "(%s LIKE '%%%s%%')",
              $field,
              addslashes($token['value'])
            );
          $s = ' OR ';
        }
        $result .= ')';
        $connector = "\n AND \n";
        break;
      case '-':
        $result .= $connector.'(NOT(';
        $s = '';
        foreach ($fields as $field) {
          $result .= $s.sprintf(
              "(%s LIKE '%%%s%%')",
              $field,
              addslashes($token['value'])
            );
          $s = ' OR ';
        }
        $result .= '))';
        $connector = "\n AND \n";
        break;
      case ':':
        $connector = "\n ".$token['value'];
        continue;
      }
    }
    if ($indent > 0) {
      $result .= str_repeat("\n)", $indent);
    }
    return $result;
  }
}
