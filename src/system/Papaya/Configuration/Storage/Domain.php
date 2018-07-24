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

namespace Papaya\Configuration\Storage;
use PapayaContentDomain;

/**
 * Loads the domain specific options from the database
 *
 * @package Papaya-Library
 * @subpackage Configuration
 */
class Domain extends \PapayaObject
  implements \Papaya\Configuration\Storage {

  /**
   * member variable for the url scheme, set in constructor used in load()
   *
   * @var int
   */
  private $_scheme = \PapayaUtilServerProtocol::BOTH;

  /**
   * member variable for the host name, set in constructor used in load()
   *
   * @var string
   */
  private $_host = '';

  /**
   * The domain subobject, representing a domain record.
   *
   * @var PapayaContentDomain
   */
  private $_domain = NULL;

  /**
   * Create storage object and store host name
   *
   * @param string $hostUrl
   */
  public function __construct($hostUrl) {
    if (preg_match('((?P<scheme>http(?:s)?)://(?P<host>.*))', $hostUrl, $match)) {
      $this->_host = $match['host'];
      $this->_scheme = ($match['scheme'] == 'https')
        ? \PapayaUtilServerProtocol::HTTPS : \PapayaUtilServerProtocol::HTTP;
    } else {
      $this->_host = $hostUrl;
    }
  }

  /**
   * Getter/Setter for domain record object
   *
   * @param \PapayaContentDomain $domain
   * @return \PapayaContentDomain
   */
  public function domain(\PapayaContentDomain $domain = NULL) {
    if (isset($domain)) {
      $this->_domain = $domain;
    } elseif (is_null($this->_domain)) {
      $this->_domain = new \PapayaContentDomain();
    }
    return $this->_domain;
  }

  /**
   * Load domain record from database using the defined host name
   *
   * @return boolean
   */
  public function load() {
    return $this->domain()->load(
      array(
        'host' => $this->_host,
        'scheme' => array(0, $this->_scheme)
      )
    );
  }

  /**
   * Get iterator for options array(name => value)
   *
   * @return \Iterator
   */
  public function getIterator() {
    $options = array();
    if ($this->domain()->mode == \PapayaContentDomain::MODE_VIRTUAL_DOMAIN &&
      is_array($this->domain()->options)) {
      $options = $this->domain()->options;
    }
    return new \ArrayIterator($options);
  }
}
