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
namespace Papaya\Message\Dispatcher {

  use Papaya\Application;
  use Papaya\Message;
  use Psr\Log;

  /**
   * Papaya Message Dispatcher PSR-3 implements the PHP-FIG interface for logging
   * defined in PSR-3.
   *
   * @package Papaya-Library
   * @subpackage Messages
   */
  class PSR3
    implements Application\Access, Message\Dispatcher {
    use Application\Access\Aggregation;

    private static $_SEVERITY_LEVELS = [
      Message::SEVERITY_DEBUG => Log\LogLevel::DEBUG,
      Message::SEVERITY_INFO => Log\LogLevel::INFO,
      Message::SEVERITY_NOTICE => Log\LogLevel::NOTICE,
      Message::SEVERITY_WARNING => Log\LogLevel::WARNING,
      Message::SEVERITY_ERROR => Log\LogLevel::ERROR,
      Message::SEVERITY_CRITICAL => Log\LogLevel::CRITICAL,
      Message::SEVERITY_ALERT => Log\LogLevel::ALERT,
      Message::SEVERITY_EMERGENCY => Log\LogLevel::EMERGENCY
    ];

    private $_logger;

    private $_enabled = TRUE;

    public function __construct(Log\LoggerInterface $logger = NULL) {
      $this->_logger = $logger;
    }

    /**
     * @return bool
     */
    public function isEnabled() {
      return $this->_enabled;
    }

    /**
     * Send log message to browser using the Wildfire protocol if possible
     *
     * @param Message $message
     *
     * @return bool
     *
     * @throws \InvalidArgumentException
     */
    public function dispatch(Message $message) {
      if (
        $this->_enabled &&
        $message instanceof Message\Logable
      ) {
        $this->send($message);
      }
      return FALSE;
    }

    /**
     * Send log message using the Wildfire protocol
     *
     * @param Message\Logable $message
     * @throws \InvalidArgumentException
     */
    public function send(Message\Logable $message) {
      try {
        $this->_logger->log(
          isset(self::$_SEVERITY_LEVELS[$message->getSeverity()])
            ? self::$_SEVERITY_LEVELS[$message->getSeverity()] : Log\LogLevel::DEBUG,
          $message->getMessage(),
          $this->getContextAsArray($message->context()) ?: []
        );
      } catch (\Exception $e) {
        $this->_enabled = FALSE;
        $this->papaya()->messages->dispatch(
          new Message\Exception($e)
        );
      }
    }

    private function getContextAsArray(Message\Context\Group $group) {
      $result = [];
      $lists = [];
      foreach ($group as $item) {
        $label = NULL;
        $value = NULL;
        if ($item instanceof Message\Context\Exception) {
          $label = 'exception';
          $value = $item->getException();
        } else {
          if ($item instanceof Message\Context\Interfaces\Labeled) {
            $label = $item->getLabel();
          }
          if ($item instanceof Message\Context\Group) {
            $value = $this->getContextAsArray($item);
          } elseif ($item instanceof Message\Context\Interfaces\Items) {
            $value = $item->asArray();
          } elseif ($item instanceof Message\Context\Interfaces\Text) {
            $value = $item->asString();
          }
        }
        if (NULL !== $value) {
          if (NULL !== $label) {
            if (isset($result[$label])) {
              if (isset($lists[$label])) {
                $result[$label][] = $value;
              } else {
                $result[$label] = [$result[$label], $value];
                $lists[$label] = TRUE;
              }
            } else {
              $result[$label] = $value;
            }
          } else {
            $result[] = $value;
          }
        }
      }
      return $result;
    }
  }
}
