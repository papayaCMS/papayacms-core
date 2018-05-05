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

class PapayaMessageDispatcherCli
  extends \PapayaObject
  implements \PapayaMessageDispatcher {

  const TARGET_STDOUT = 'stdout';
  const TARGET_STDERR = 'stderr';

  /**
  * Options for message formatting
  * @var array
  */
  private $_messageOptions = array(
    \PapayaMessage::SEVERITY_ERROR => array(
      'label' => 'Error'
    ),
    \PapayaMessage::SEVERITY_WARNING => array(
      'label' => 'Warning'
    ),
    \PapayaMessage::SEVERITY_INFO => array(
      'label' => 'Information'
    ),
    \PapayaMessage::SEVERITY_DEBUG => array(
      'label' => 'Debug'
    )
  );

  /**
  * The php sapi name
  * @var string
  */
  private $_phpSapiName = NULL;

  /**
   * Output streams
   *
   * @var array(resource)
   */
  private $_streams = array(
    self::TARGET_STDOUT => NULL,
    self::TARGET_STDERR => NULL
  );

  /**
  * Output log message to stdout
  *
  * @param \PapayaMessage $message
  * @return boolean
  */
  public function dispatch(\PapayaMessage $message) {
    if ($message instanceof \PapayaMessageLogable &&
        $this->allow()) {
      $options = $this->getOptionsFromType($message->getType());
      $isError = in_array(
        $message->getType(), array(\PapayaMessage::SEVERITY_ERROR, \PapayaMessage::SEVERITY_WARNING)
      );
      fwrite(
        $this->stream($isError ? self::TARGET_STDERR : self::TARGET_STDOUT),
        sprintf(
          "\n\n%s: %s %s\n",
          $options['label'],
          $message->getMessage(),
          $message->context()->asString()
        )
      );
    }
    return FALSE;
  }

  /**
  * Get/set the php sapi name
  *
  * @see php_sapi_name()
  * @param string $name
  * @return string
  */
  public function phpSapiName($name = NULL) {
    if (isset($name)) {
      $this->_phpSapiName = $name;
    }
    if (is_null($this->_phpSapiName)) {
      $this->_phpSapiName = strToLower(PHP_SAPI);
    }
    return $this->_phpSapiName;
  }

  /**
  * Check if it is allowed to use the dispatcher
  */
  public function allow() {
    return ('cli' === $this->phpSapiName());
  }

  /**
  * Get formating options for the error message
  *
  * @param integer $type
  * @return array
  */
  public function getOptionsFromType($type) {
    if (isset($this->_messageOptions[$type])) {
      return $this->_messageOptions[$type];
    } else {
      return $this->_messageOptions[\PapayaMessage::SEVERITY_ERROR];
    }
  }

  /**
   * Getter/Setter for the target output streams (stdout/stderr)
   *
   * @param string $target
   * @param resource $stream
   * @throws \InvalidArgumentException
   * @return resource
   */
  public function stream($target, $stream = NULL) {
    if (!array_key_exists($target, $this->_streams)) {
      throw new \InvalidArgumentException(
        sprintf('Invalid output target "%s".', $target)
      );
    }
    if (isset($stream)) {
      \PapayaUtilConstraints::assertResource($stream);
      $this->_streams[$target] = $stream;
    } elseif (NULL === $this->_streams[$target]) {
      $name = 'php://'.$target;
      $this->_streams[$target] = fopen($name, 'w');
    }
    return $this->_streams[$target];
  }
}
