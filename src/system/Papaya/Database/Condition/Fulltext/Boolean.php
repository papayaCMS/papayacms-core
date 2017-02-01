<?php

class PapayaDatabaseConditionFulltextBoolean extends PapayaDatabaseConditionFulltext {

  /**
   * Get filters for MySQL MATCH command
   *
   * @param PapayaParserSearchString $tokens
   * @param array $fields
   * @return string
   */
  protected function getFulltextCondition(PapayaParserSearchString $tokens, array $fields) {
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
   * @param PapayaParserSearchString $tokens
   * @param string $fieldString
   * @access public
   * @return string
   */
  private function getBooleanFilterLine(PapayaParserSearchString $tokens, $fieldString) {
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
        continue;
      }
    }
    if ($indent > 0) {
      $matchString .= str_repeat(" )", $indent);
    }
    return sprintf(
      "(MATCH (%s) AGAINST ('%s' IN BOOLEAN MODE))", $fieldString, $matchString
    );
  }
}
