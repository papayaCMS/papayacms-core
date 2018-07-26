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

namespace Papaya\File\System\Action;
/**
 * Read an url to trigger an remote script
 *
 * @package Papaya-Library
 * @subpackage FileSystem
 */
class Url implements \PapayaFileSystemAction {

  private $_url;

  public function __construct($url) {
    $this->_url = $url;
  }

  /**
   * Load an external url to trigger a script on the (remote) server
   *
   * @param array $parameters
   * @return bool
   */
  public function execute(array $parameters = array()) {
    $queryString = '';
    foreach ($parameters as $name => $value) {
      $queryString .= '&'.urlencode($name).'='.urlencode($value);
    }
    return $this->fetch($this->_url.'?'.substr($queryString, 1));
  }

  /**
   * fetch the external resource (trigger the script)
   *
   * @param string $url
   * @return bool
   * @codeCoverageIgnore
   */
  protected function fetch($url) {
    return (boolean)file_get_contents($url);
  }
}
