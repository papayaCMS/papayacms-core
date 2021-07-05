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
namespace Papaya\Database\Source;

use Papaya\BaseObject\Interfaces\StringCastable;
use Papaya\Database;
use Papaya\Request;

/**
 * Database source name (DSN) specifies a data structure that contains the information
 * about a specific data source
 *
 * @package Papaya-Library
 * @subpackage Database
 *
 * @property string $api
 * @property string $platform
 * @property string $filename
 * @property string $username
 * @property string $password
 * @property string $host
 * @property string $port
 * @property string $socket
 * @property string $database
 * @property Request\Parameters $parameters
 */
class Name implements StringCastable, \IteratorAggregate {
  /**
   * Raw dsn string
   *
   * @var string
   */
  private $_name = '';

  /**
   * Parsed dsn information
   *
   * @var array
   */
  private $_properties = [];

  /**
   * Additional parameters
   *
   * @var Request\Parameters
   */
  private $_parameters;

  /**
   * Construct a dsn object and set it's properties from a dsn string
   *
   * @throws \InvalidArgumentException
   *
   * @param string $name
   *
   * @throws Database\Exception\ConnectionFailed
   */
  public function __construct($name) {
    $this->setName($name);
  }

  public function __toString() {
    return $this->_name;
  }

  public function getIterator(): \ArrayIterator {
    return new \ArrayIterator($this->_properties);
  }

  /**
   * Initialize object from a dsn string
   *
   * Parses the dsn string and build an array containing all parts. All version can have a
   * query string providing additional parameters
   *
   * Syntax 1:
   *   api(platform):user:pass@host:port/database
   *
   * The platform argument is optional, if it is not set, the api is used for this option, too.
   *
   * The authentication part is optional. If an authentication is given the password is optional,
   * but the username is not.
   *
   * The port is an optional suffix to the host.
   *
   * Syntax 2:
   *   api(platform):user:pass@unix(/path/to/socket)/database
   *
   * Nearly the same as syntax 1 but with a socket not a host.
   *
   * Syntax 3:
   *   api(platform):/path/file.sqlite
   *
   * The platform argument is optional, if it is not set, the api is used for this option, too.
   *
   * The filename needs to be an absolute path and file.
   *
   *
   * @param string $name
   *
   * @throws Database\Exception\ConnectionFailed
   */
  public function setName($name) {
    if (empty($name)) {
      throw new Database\Exception\ConnectionFailed(
        'Can not initialize database connection from empty dsn.'
      );
    }
    $patternServer = '(^
      (?P<api>[A-Za-z\d]+) # api name
      (?:\\((?P<platform>[A-Za-z\d]+)\\))? # platform name, optional - default is the api name
      :(?://)? # separator : or ://
      (?: # authentication, optional
        (?P<user>[^.:@][^:@]*) # username, any char except : and @ (no . as first char)
        (?::(?P<pass>[^@]+))? # password, any char except @, optional
        @
      )?
      (?: # host or socket
        (?:
          (?P<host>[a-zA-Z\d][a-zA-Z\d._-]*) # host name or ip
          (?::(?P<port>\d+))? # port number, optional
        )|
        (?:
          unix\\((?P<socket>(?:[/\\\\][^?<>/\\\\:*|]+)+)\\) # unix file socket
        )
      )
      / # separator /
      (?P<database>[^/\\s]+) # database name
    $)xD';
    $patternFile = '(^
      (?P<api>[A-Za-z\d]+) # api name
      (?:\\((?P<platform>[A-Za-z\d]+)\\))? # platform name, optional - default is the api name
      :(?://)?  # separator : or ://
      (?P<file>
        (?:[a-zA-Z]:(?:[/\\\\][^?<>/\\\\:*|]+)+)| # local windows file name
        (?:(?:[/\\\\][^?<>/\\\\:*|]+)+)| # unix file name
        (?:[.]{1,2}(?:[/\\\\][^?<>/\\\\:*|]+)+) # relative file name
      )
    $)xD';
    $queryStringStart = \strpos($name, '?');
    if ($queryStringStart > 0) {
      $dsn = \substr($name, 0, $queryStringStart - 1);
    } else {
      $dsn = $name;
    }
    if (\preg_match($patternServer, $dsn, $matches) ||
      \preg_match($patternFile, $dsn, $matches)) {
      $this->_name = $name;
      $this->_properties = [
        'api' => $matches['api'],
        'platform' => $this->_getMatchValue($matches, 'platform', $matches['api']),
        'filename' => $this->_getMatchValue($matches, 'file'),
        'username' => $this->_getMatchValue($matches, 'user'),
        'password' => $this->_getMatchValue($matches, 'pass'),
        'host' => $this->_getMatchValue($matches, 'host'),
        'port' => (int)$this->_getMatchValue($matches, 'port'),
        'socket' => $this->_getMatchValue($matches, 'socket'),
        'database' => $this->_getMatchValue($matches, 'database')
      ];
      if ($queryStringStart > 0) {
        $query = new Request\Parameters\QueryString();
        $this->_parameters = $query->setString(\substr($name, $queryStringStart + 1))->values();
      } else {
        $this->_parameters = new Request\Parameters();
      }
    } else {
      throw new Database\Exception\ConnectionFailed(
        'Can not initialize database connection from invalid dsn.'
      );
    }
  }

  /**
   * Check if $name exists in $matches, return $default if not.
   *
   * @param array $matches
   * @param string $name
   * @param mixed $default
   *
   * @return mixed|null
   */
  private function _getMatchValue($matches, $name, $default = NULL) {
    if (empty($matches[$name])) {
      return $default;
    }
    return $matches[$name];
  }

  /**
   * Check if a dsn property does exists (contains a value in this case)
   *
   * @param name
   *
   * @return bool
   */
  public function __isset($name) {
    if (empty($this->_properties[$name])) {
      return FALSE;
    }
    return TRUE;
  }

  /**
   * Get dynamic properties
   *
   * Provides read access to the values in the _properties array and the parameters property.
   *
   * @throws \ErrorException
   *
   * @param string $name
   *
   * @return mixed
   */
  public function __get($name) {
    if (\array_key_exists($name, $this->_properties)) {
      return $this->_properties[$name];
    }
    if ('parameters' === $name) {
      return $this->_parameters;
    }
    throw new \ErrorException(
      \sprintf('Undefined property: %s::$%s', __CLASS__, $name),
      0,
      0,
      __FILE__,
      __LINE__
    );
  }

  /**
   * Proptect properties agains changes from the outside
   *
   * @param string $name
   * @param mixed $value
   *
   * @throws \BadMethodCallException
   */
  public function __set($name, $value) {
    throw new \BadMethodCallException(
      \sprintf('Property %s::$%s is not writable.', __CLASS__, $name)
    );
  }
}
