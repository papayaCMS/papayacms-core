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
namespace Papaya\CMS\Configuration\Storage {

  use Papaya\Application;
  use Papaya\Configuration\Storage as ConfigurationStorage;
  use Papaya\CMS\Content;
  use Papaya\Utility;

  /**
   * Loads the domain specific options from the database
   *
   * @package Papaya-Library
   * @subpackage Configuration
   */
  class Domain extends Application\BaseObject
    implements ConfigurationStorage {
    /**
     * member variable for the url scheme, set in constructor used in load()
     *
     * @var int
     */
    private $_scheme = Utility\Server\Protocol::BOTH;

    /**
     * member variable for the host name, set in constructor used in load()
     *
     * @var string
     */
    private $_host = '';

    /**
     * The domain subobject, representing a domain record.
     *
     * @var Domain
     */
    private $_domain;

    /**
     * Create storage object and store host name
     *
     * @param string $hostURL
     */
    public function __construct($hostURL) {
      if (\preg_match('((?P<scheme>http(?:s)?)://(?P<host>.*))i', $hostURL, $match)) {
        $this->_host = $match['host'];
        $this->_scheme = ('https' === $match['scheme'])
          ? Utility\Server\Protocol::HTTPS : Utility\Server\Protocol::HTTP;
      } else {
        $this->_host = $hostURL;
      }
    }

    public function getHost(): string {
      return $this->_host;
    }

    public function getScheme(): string {
      return $this->_scheme;
    }

    /**
     * Getter/Setter for domain record object
     *
     * @param Content\Domain $domain
     *
     * @return Content\Domain
     */
    public function domain(Content\Domain $domain = NULL) {
      if (NULL !== $domain) {
        $this->_domain = $domain;
      } elseif (NULL === $this->_domain) {
        $this->_domain = new Content\Domain();
      }
      return $this->_domain;
    }

    /**
     * Load domain record from database using the defined host name
     *
     * @return bool
     */
    public function load() {
      return $this->domain()->load(
        [
          'host' => $this->_host,
          'scheme' => [0, $this->_scheme]
        ]
      );
    }

    /**
     * Get iterator for options array(name => value)
     *
     * @return \Iterator
     */
    public function getIterator(): \Traversable {
      if (
        Content\Domain::MODE_VIRTUAL_DOMAIN === (int)$this->domain()->mode &&
        \is_array($this->domain()->options)
      ) {
        return new \ArrayIterator($this->domain()->options);
      }
      return new \EmptyIterator();
    }
  }
}
