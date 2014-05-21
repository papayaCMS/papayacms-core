<?php
/**
* Papaya Message Dispatcher Wildfire Handler, provides functions to use the wildfire protocol
*
* @copyright 2010 by papaya Software GmbH - All rights reserved.
* @link http://www.papaya-cms.com/
* @license http://www.gnu.org/licenses/old-licenses/gpl-2.0.html GNU General Public License, version 2
*
* You can redistribute and/or modify this script under the terms of the GNU General Public
* License (GPL) version 2, provided that the copyright and license notes, including these
* lines, remain unmodified. papaya is distributed in the hope that it will be useful, but
* WITHOUT ANY WARRANTY; without even the implied warranty of MERCHANTABILITY or FITNESS
* FOR A PARTICULAR PURPOSE.
*
* @package Papaya-Library
* @subpackage Messages
* @version $Id: Handler.php 39409 2014-02-27 16:36:19Z weinert $
*/

/**
* Papaya Message Dispatcher Wildfire Handler, provides functions to use the wildfire protocol
*
* Wildfire ist the protocol behind FirePHP, an Firefox extension to display messages,
* recieved in HTTP headers. {@link http://www.firephp.org}
*
* @package Papaya-Library
* @subpackage Messages
*/
class PapayaMessageDispatcherWildfireHandler {

  /**
  * Header type main
  * @var integer
  */
  const HEADER_MAIN = 0;
  /**
  * Header type console (messages)
  * @var integer
  */
  const HEADER_CONSOLE = 1;
  /**
  * Header type dump (variable dumps)
  * @var integer
  */
  const HEADER_DUMP = 2;
  /**
  * Header type data (headers with the actual data)
  * @var integer
  */
  const HEADER_DATA = 3;

  /**
  * Counters for send headers
  *
  * @var array
  */
  private static $_counter = array(
    self::HEADER_MAIN => 0,
    self::HEADER_CONSOLE => 0,
    self::HEADER_DUMP => 0,
    self::HEADER_DATA => 0
  );

  /**
  * Length limit for headers
  * @var integer
  */
  public static $lengthLimit = 5000;

  /**
  * Headers needed to initialize the the protocol
  *
  * @var array
  */
  private $_initializationHeaders = array(
    self::HEADER_MAIN => array(
      'X-Wf-Protocol-1' => 'http://meta.wildfirehq.org/Protocol/JsonStream/0.2',
      'X-Wf-1-Plugin-1' => 'http://meta.firephp.org/Wildfire/Plugin/FirePHP/Library-FirePHPCore/0.3'
    ),
    self::HEADER_CONSOLE => array(
      'X-Wf-1-Structure-1' =>
        'http://meta.firephp.org/Wildfire/Structure/FirePHP/FirebugConsole/0.1'
    ),
    self::HEADER_DUMP => array(
      'X-Wf-1-Structure-2' => 'http://meta.firephp.org/Wildfire/Structure/FirePHP/Dump/0.1'
    )
  );

  /**
  * Callback function for sending headers  *
  * @var Callback
  */
  private $_callback = NULL;

  /**
   * Create Handler and set callback function
   * @param Callback $callback
   * @throws InvalidArgumentException
   */
  public function __construct($callback) {
    if (is_callable($callback)) {
      $this->_callback = $callback;
    } else {
      throw new InvalidArgumentException('Valid callback needed, to send HTTP headers.');
    }
  }

  /**
  * Reset class counters to inital values
  */
  public function resetCounters() {
    self::$_counter = array(
      self::HEADER_MAIN => 0,
      self::HEADER_CONSOLE => 0,
      self::HEADER_DUMP => 0,
      self::HEADER_DATA => 0
    );
  }

  /**
  * Use callback to send headers
  *
  * @param string $name
  * @param string $value
  */
  private function _send($name, $value) {
    if (isset($this->_callback)) {
      call_user_func($this->_callback, $name.': '.$value);
    }
  }

  /**
  * Send initialization headers if needed
  *
  * @param integer $type
  */
  public function sendInitialization($type) {
    if (isset($this->_initializationHeaders[$type]) &&
        self::$_counter[$type] == 0) {
      foreach ($this->_initializationHeaders[$type] as $name => $value) {
        $this->_send($name, $value);
      }
      self::$_counter[$type]++;
    }
  }

  /**
   * Send a message
   *
   * @param string $content
   * @param string $type
   * @param string $label
   */
  public function sendMessage($content, $type = 'LOG', $label = '') {
    $meta = array(
      'Type' => $type
    );
    if (!empty($label)) {
      $meta['Label'] = $label;
    }
    $this->sendData(self::HEADER_CONSOLE, $meta, $content);
  }

  /**
  * Send a variable dump
  * @param array $content
  */
  public function sendDump($content) {
    $this->sendData(self::HEADER_CONSOLE, array('Type' => 'LOG'), $content);
  }

  /**
   * Start a collapseable group
   *
   * @param string $label
   */
  public function startGroup($label = ' ') {
    /**
    */
    $meta = array(
      'Type' => 'GROUP_START',
      'Label' => empty($label) ? ' ' : $label
    );
    $this->sendData(self::HEADER_CONSOLE, $meta, NULL);
  }

  /**
  * End the previously opened group
  */
  public function endGroup() {
    $meta = array(
      'Type' => 'GROUP_END'
    );
    $this->sendData(self::HEADER_CONSOLE, $meta, NULL);
  }

  /**
  * Send a header containing data
  *
  * @param integer $structure
  * @param array $meta
  * @param mixed $content
  */
  public function sendData($structure, $meta, $content) {
    $this->sendInitialization(self::HEADER_MAIN);
    $this->sendInitialization($structure);
    $structureIndex = ($structure == self::HEADER_DUMP) ? 2 : 1;
    switch ($structure) {
    case self::HEADER_DUMP :
      $value = json_encode($content);
      break;
    case self::HEADER_CONSOLE :
    default :
      $value = '['.json_encode($meta).','.json_encode($content).']';
    }
    $lines = str_split(
      $value,
      self::$lengthLimit > 0 ? (int)self::$lengthLimit : 5000
    );
    $max = count($lines) - 1;
    for ($i = 0; $i <= $max; $i++) {
      $name = 'X-Wf-1-'.$structureIndex.'-1-'.(++self::$_counter[self::HEADER_DATA]);
      $suffix = ($i < $max) ? '\\' : '';
      if ($i == 0) {
        $this->_send($name, strlen($value).'|'.$lines[$i].'|'.$suffix);
      } else {
        $this->_send($name, '|'.$lines[$i].'|'.$suffix);
      }
    }
  }
}