<?php
/**
* simple template system for emails and small user texts
*
* @copyright 2002-2007 by papaya Software GmbH - All rights reserved.
* @link      http://www.papaya-cms.com/
* @license http://www.gnu.org/licenses/old-licenses/gpl-2.0.html GNU General Public License, version 2
*
* You can redistribute and/or modify this script under the terms of the GNU General Public
* License (GPL) version 2, provided that the copyright and license notes, including these
* lines, remain unmodified. papaya is distributed in the hope that it will be useful, but
* WITHOUT ANY WARRANTY; without even the implied warranty of MERCHANTABILITY or FITNESS
* FOR A PARTICULAR PURPOSE.
*
* @package Papaya-Library
* @subpackage Template-Simple
* @version $Id: base_simpletemplate.php 39630 2014-03-19 14:40:47Z weinert $
*/

/**
* Template token type - Text
*/
define('PAPAYA_SIMPLETEMPLATE_TEXT', 0);
/**
* Template token type - Value
*/
define('PAPAYA_SIMPLETEMPLATE_VALUE', 1);
/**
* Template token type - If begin
*/
define('PAPAYA_SIMPLETEMPLATE_IF', 2);
/**
* Template token type - If end
*/
define('PAPAYA_SIMPLETEMPLATE_ENDIF', 3);

/**
* simple template for emails and small user texts
*
* @package Papaya-Library
* @subpackage Template-Simple
*/
class base_simpletemplate {

  /**
  * opening string for template commands
  * @var string
  */
  var $tagOpen = '{%';
  /**
  * closing string for template commands
  * @var string
  */
  var $tagClose = '%}';

  /**
  * template
  * @var string
  * @access private
  */
  var $_template = NULL;
  /**
  * template values
  * @var array
  * @access private
  */
  var $_values = array();
  /**
  * stack for parsed template
  * @var array
  * @access private
  */
  var $_stack = array();

  /**
   * @var int
   */
  private $_offset;

  /**
   * @var array
   */
  private $_status = NULL;

  /**
  * parse, execute and return result
  *
  * @param string $template
  * @param array $values
  * @access public
  * @return string
  */
  function parse($template, $values) {
    $this->_template = $template;
    $this->_values = array();
    $this->_stack = array();
    if (isset($values) && is_array($values)) {
      foreach ($values as $key => $val) {
        $this->_values[strtoupper($key)] = $val;
      }
    }
    $this->_parse();
    unset($this->_template);
    return $this->_execute();
  }

  /**
  * execute template stack
  *
  * @access private
  * @return string
  */
  function _execute() {
    if (is_array($this->_stack) && count($this->_stack) > 0) {
      $ifIndent = 0;
      $result = '';
      foreach ($this->_stack as $element) {
        if ($ifIndent <= 0) {
          switch ($element[0]) {
          case PAPAYA_SIMPLETEMPLATE_TEXT :
            $result .= $element[1];
            break;
          case PAPAYA_SIMPLETEMPLATE_VALUE :
            $keyword = strtoupper($element[1]);
            if (isset($this->_values[$keyword])) {
              $result .= $this->_values[$keyword];
            }
            break;
          case PAPAYA_SIMPLETEMPLATE_IF :
            if (!$this->_checkCondition($element[2], $element[3], $element[4])) {
              $ifIndent = $element[1];
            }
            break;
          }
        } elseif ($element[0] == PAPAYA_SIMPLETEMPLATE_ENDIF && $element[1] == $ifIndent) {
          $ifIndent = 0;
        }
      }
      return $result;
    }
    return '';
  }

  /**
  * parse template string to stack
  *
  * @access private
  */
  function _parse() {
    if (isset($this->_template)) {
      $this->_offset = 0;
      $this->_status = array(
        'in_cmd' => FALSE,
        'if_indent' => 0
      );
      //start it
      while (FALSE !== ($data = $this->_getNext())) {
        //we got some data
        if ($this->_status['in_cmd']) {
          //this is a cmd - parse and add it
          $this->_addCmdToken($data);
        } else {
          //this is some text
          $this->_addTextToken($data);
        }
        $this->_status['in_cmd'] = !$this->_status['in_cmd'];
      }
      if ($this->_offset < strlen($this->_template)) {
        $data = substr($this->_template, $this->_offset);
        if ($this->_status['in_cmd']) {
          $this->_addCmdToken($data);
        } else {
          $this->_addTextToken($data);
        }
      }
    }
  }

  /**
  * get next text/cmd token
  *
  * @access private
  * @return string
  */
  function _getNext() {
    $tag = ($this->_status['in_cmd']) ? $this->tagClose : $this->tagOpen;
    $pos = strpos($this->_template, $tag, $this->_offset);
    if ($pos !== FALSE) {
      $result = substr($this->_template, $this->_offset, $pos - $this->_offset);
      $this->_offset = $pos + strlen($tag);
      return $result;
    }
    return FALSE;
  }

  /**
  * add cmd token to stack
  *
  * @param string $data
  * @access private
  */
  function _addCmdToken($data) {
    $conditionPattern = '(^\s*IF\s+([a-zA-Z\d:_.-]+)(\s*(!==|==|!=|<>|<=|>=|>|<|=)(.*))?\s*$)i';
    if (strtoupper($data) == 'ENDIF') {
      $this->_stack[] = array(PAPAYA_SIMPLETEMPLATE_ENDIF, $this->_status['if_indent']--);
    } elseif (preg_match($conditionPattern, $data, $matches)) {
      $keyWord = trim($matches[1]);
      $operator = (!empty($matches[3])) ? trim($matches[3]) : '';
      $value = (isset($matches[4])) ? trim($matches[4]) : '';
      if (strlen($value) > 1) {
        $firstChar = substr($value, 0, 1);
        $lastChar = substr($value, -1);
        if ($firstChar == $lastChar &&
            in_array($firstChar, array('\'', '"'))) {
          $value = substr($value, 1, -1);
        }
      }
      $this->_stack[] = array(
        PAPAYA_SIMPLETEMPLATE_IF,
        ++$this->_status['if_indent'],
        $keyWord,
        $operator,
        $value
      );
    } elseif (trim($data) != '') {
      $this->_stack[] = array(PAPAYA_SIMPLETEMPLATE_VALUE, trim($data));
    }
  }

  /**
  * add text token to stack
  *
  * @param string $data
  * @access private
  */
  function _addTextToken($data) {
    if ($data !== '') {
      $this->_stack[] = array(PAPAYA_SIMPLETEMPLATE_TEXT, $data);
    }
  }

  /**
  * test the current condition
  *
  * @param string $keyword
  * @param string $operator
  * @param string $value
  * @access private
  * @return boolean
  */
  function _checkCondition($keyword, $operator, $value) {
    if (empty($keyword)) {
      return FALSE;
    } elseif (empty($operator)) {
      return !empty($this->_values[strtoupper($keyword)]);
    } else {
      $keyValue = $this->_values[strtoupper($keyword)];
      switch ($operator) {
      case '=' :
        //compare
        return (strcmp($keyValue, $value) == 0);
      case '!=' :
      case '<>' :
        return (strcmp($keyValue, $value) != 0);
      case '==' :
        //compare case sentitive
        return ($keyValue == $value);
      case '!==' :
        return ($keyValue != $value);
      case '<' :
        //numeric/ascii compare
        return $keyValue < $value;
      case '>' :
        return $keyValue > $value;
      case '<=' :
        return $keyValue <= $value;
      case '>=' :
        return $keyValue >= $value;
      }
    }
    return FALSE;
  }
}

