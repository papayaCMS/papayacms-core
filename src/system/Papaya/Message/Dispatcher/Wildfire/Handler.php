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
namespace Papaya\Message\Dispatcher\Wildfire {

  /**
   * Papaya Message Dispatcher Wildfire Handler, provides functions to use the wildfire protocol
   *
   * Wildfire ist the protocol behind FirePHP, an Firefox extension to display messages,
   * received in HTTP headers. {@link http://www.firephp.org}
   *
   * @package Papaya-Library
   * @subpackage Messages
   */
  class Handler {
    /**
     * Header type main
     *
     * @var int
     */
    const HEADER_MAIN = 0;

    /**
     * Header type console (messages)
     *
     * @var int
     */
    const HEADER_CONSOLE = 1;

    /**
     * Header type dump (variable dumps)
     *
     * @var int
     */
    const HEADER_DUMP = 2;

    /**
     * Header type data (headers with the actual data)
     *
     * @var int
     */
    const HEADER_DATA = 3;

    /**
     * Counters for send headers
     *
     * @var array
     */
    private static $_counter = [
      self::HEADER_MAIN => 0,
      self::HEADER_CONSOLE => 0,
      self::HEADER_DUMP => 0,
      self::HEADER_DATA => 0
    ];

    /**
     * Length limit for headers
     *
     * @var int
     */
    public static $lengthLimit = 5000;

    /**
     * Headers needed to initialize the the protocol
     *
     * @var array[]
     */
    private static $_initializationHeaders = [
      self::HEADER_MAIN => [
        'X-Wf-Protocol-1' => 'http://meta.wildfirehq.org/Protocol/JsonStream/0.2',
        'X-Wf-1-Plugin-1' => 'http://meta.firephp.org/Wildfire/Plugin/FirePHP/Library-FirePHPCore/0.3'
      ],
      self::HEADER_CONSOLE => [
        'X-Wf-1-Structure-1' =>
          'http://meta.firephp.org/Wildfire/Structure/FirePHP/FirebugConsole/0.1'
      ],
      self::HEADER_DUMP => [
        'X-Wf-1-Structure-2' => 'http://meta.firephp.org/Wildfire/Structure/FirePHP/Dump/0.1'
      ]
    ];

    /**
     * Callback function for sending headers  *
     *
     * @var callable
     */
    private $_callback;

    /**
     * Create Handler and set callback function
     *
     * @param \Callback $callback
     *
     * @throws \InvalidArgumentException
     */
    public function __construct($callback) {
      if (\is_callable($callback)) {
        $this->_callback = $callback;
      } else {
        throw new \InvalidArgumentException('Valid callback needed, to send HTTP headers.');
      }
    }

    /**
     * Reset class counters to initial values
     */
    public function resetCounters() {
      foreach (self::$_counter as $key => $value) {
        self::$_counter[$key] = 0;
      }
    }

    /**
     * Use callback to send headers
     *
     * @param string $name
     * @param string $value
     */
    private function _send($name, $value) {
      if (NULL !== ($callback = $this->_callback)) {
        $callback($name.': '.$value);
      }
    }

    /**
     * Send initialization headers if needed
     *
     * @param int $type
     */
    public function sendInitialization($type) {
      if (isset(self::$_initializationHeaders[$type]) && 0 === self::$_counter[$type]) {
        foreach (self::$_initializationHeaders[$type] as $name => $value) {
          $this->_send($name, $value);
        }
        self::$_counter[$type]++;
      }
    }

    /**
     * Send a message
     *
     * @param mixed $content
     * @param string $type
     * @param string $label
     */
    public function sendMessage($content, $type = 'LOG', $label = '') {
      $meta = [
        'Type' => $type
      ];
      if (!empty($label)) {
        $meta['Label'] = $label;
      }
      $this->sendData(self::HEADER_CONSOLE, $meta, $content);
    }

    /**
     * Send a variable dump
     *
     * @param mixed $content
     */
    public function sendDump($content) {
      $this->sendData(self::HEADER_CONSOLE, ['Type' => 'LOG'], $content);
    }

    /**
     * Start a collapsible group
     *
     * @param string $label
     */
    public function startGroup($label = ' ') {
      $meta = [
        'Type' => 'GROUP_START',
        'Label' => empty($label) ? ' ' : $label
      ];
      $this->sendData(self::HEADER_CONSOLE, $meta, NULL);
    }

    /**
     * End the previously opened group
     */
    public function endGroup() {
      $meta = [
        'Type' => 'GROUP_END'
      ];
      $this->sendData(self::HEADER_CONSOLE, $meta, NULL);
    }

    /**
     * Send a header containing data
     *
     * @param int $structure
     * @param array $meta
     * @param mixed $content
     */
    public function sendData($structure, $meta, $content) {
      $this->sendInitialization(self::HEADER_MAIN);
      $this->sendInitialization($structure);
      $structureIndex = (self::HEADER_DUMP === $structure) ? 2 : 1;
      switch ($structure) {
      case self::HEADER_DUMP :
        $value = \json_encode($content);
        break;
      case self::HEADER_CONSOLE :
      default :
        $value = '['.\json_encode($meta).','.\json_encode($content).']';
      }
      $lines = \str_split(
        $value,
        self::$lengthLimit > 0 ? (int)self::$lengthLimit : 5000
      );
      $max = \count($lines) - 1;
      foreach ($lines as $i => $line) {
        $name = 'X-Wf-1-'.$structureIndex.'-1-'.(++self::$_counter[self::HEADER_DATA]);
        $suffix = ($i < $max) ? '\\' : '';
        if (0 === $i) {
          $this->_send($name, \strlen($value).'|'.$line.'|'.$suffix);
        } else {
          $this->_send($name, '|'.$line.'|'.$suffix);
        }
      }
    }
  }
}
