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
namespace Papaya\Filter\Ip;

use Papaya\Filter;

/**
 * This class validates and filters IP addresses in version 4 form.
 *
 * @package Papaya-Library
 * @subpackage Filter
 */
class V4 implements Filter {
  /**
   * Allow local IP addresses
   *
   * @const ALLOW_LINK_LOCAL
   */
  const ALLOW_LINK_LOCAL = 8;

  /**
   * Allow loopback
   *
   * @const ALLOW_LOOPBACK
   */
  const ALLOW_LOOPBACK = 4;

  /**
   * Allow the global broadcast address
   *
   * @const ALLOW_GLOBAL_BROADCAST
   */
  const ALLOW_GLOBAL_BROADCAST = 2;

  /**
   * Allow all-zero addresses
   *
   * @const ALLOW_ALL_ZEROS
   */
  const ALLOW_ALL_ZEROS = 1;

  /**
   * Default configuration
   *
   * @const DEFAULT_CONFIGURATION
   */
  const DEFAULT_CONFIGURATION = 15;

  /**
   * Configuration
   *
   * @var int
   */
  private $_configuration;

  /**
   * The constructor sets up the configuration
   *
   * @throws \InvalidArgumentException
   * @throws \OutOfRangeException
   *
   * @param int $configuration
   */
  public function __construct($configuration = self::DEFAULT_CONFIGURATION) {
    if (!\is_numeric($configuration)) {
      throw new \InvalidArgumentException('Configuration value must be a number.');
    }
    if ($configuration < 0 ||
      $configuration > self::DEFAULT_CONFIGURATION) {
      throw new \OutOfRangeException('Configuration value out of range.');
    }
    $this->_configuration = (int)$configuration;
  }

  /**
   * This method validates that an input string is a valid IP.
   *
   * 1. split the value into its individual parts
   * 2. check whether the number of parts is valid
   * 3. check whether the individual parts are valid
   * 4. check the actual value against the configuration
   *
   * @todo Replace InvalidArgumentException with FilterException child classes
   *
   * @throws Filter\Exception\InvalidPart
   * @throws Filter\Exception\InvalidCount
   * @throws \InvalidArgumentException
   *
   * @param mixed $value
   *
   * @return bool TRUE
   */
  public function validate($value) {
    $parts = \explode('.', $value);
    if (4 !== \count($parts)) {
      throw new Filter\Exception\InvalidCount(4, \count($parts), 'ip octets');
    }
    $filterInteger = new Filter\IntegerValue(0, 255);
    foreach ($parts as $position => $part) {
      try {
        $filterInteger->validate($part);
      } catch (Filter\Exception $e) {
        throw new Filter\Exception\InvalidPart($position + 1, 'ip octet', $e->getMessage());
      }
    }
    if (!(self::ALLOW_ALL_ZEROS & $this->_configuration) && '0.0.0.0' === $value) {
      throw new \InvalidArgumentException('All-zero IP address not allowed by configuration.');
    }
    if (!(self::ALLOW_GLOBAL_BROADCAST & $this->_configuration) && '255.255.255.255' === $value) {
      throw new \InvalidArgumentException('Global broadcast address not allowed by configuration.');
    }
    if (!(self::ALLOW_LOOPBACK & $this->_configuration) && '127' === $parts[0]) {
      throw new \InvalidArgumentException('Loopback address not allowed by configuration.');
    }
    if (!(self::ALLOW_LINK_LOCAL & $this->_configuration)) {
      if ('10' === $parts[0] ||
        ('192' === $parts[0] && '168' === $parts[1]) ||
        ('172' === $parts[0] && $parts[1] >= 16 && $parts[2] <= 31)
      ) {
        throw new \InvalidArgumentException('Link-local address not allowed by configuration.');
      }
    }
    return TRUE;
  }

  /**
   * This method filters leading and trailing whitespaces from the input IP.
   *
   * @param mixed $value
   *
   * @return string|null
   */
  public function filter($value) {
    $result = \trim($value);
    try {
      $this->validate($result);
    } catch (Filter\Exception $e) {
      $result = NULL;
    } catch (\InvalidArgumentException $e) {
      $result = NULL;
    }
    return $result;
  }
}
