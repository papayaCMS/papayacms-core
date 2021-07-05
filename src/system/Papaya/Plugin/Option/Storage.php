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
namespace Papaya\Plugin\Option;

use Papaya\Application;
use Papaya\Configuration;
use Papaya\Content;
use Papaya\Utility;

/**
 * This configuration storage load the module option records using
 * {@see \Papaya\Content\Module\Options} by the module guid and maps them into an associative array.
 *
 * @package Papaya-Library
 * @subpackage Plugins
 */
class Storage
  implements Application\Access, Configuration\Storage {
  use Application\Access\Aggregation;

  /**
   * @var string
   */
  private $_guid;

  /**
   * @var Content\Module\Options
   */
  private $_options;

  /**
   * Create storage object and store module guid
   *
   * @param string $guid
   */
  public function __construct($guid) {
    $this->_guid = Utility\Text\Guid::toLower($guid);
  }

  public function getGUID(): string {
    return $this->_guid;
  }

  /**
   * Explicitly load module options from database
   *
   * @return bool
   */
  public function load() {
    return $this->options()->load(['guid' => $this->_guid]);
  }

  /**
   * Map and return module options
   *
   * @return \Traversable
   */
  public function getIterator() {
    $result = [];
    foreach ($this->options() as $option) {
      $result[$option['name']] = $option['value'];
    }
    return new \ArrayIterator($result);
  }

  /**
   * Getter/Setter: Options database encapsulation subobject
   *
   * @param Content\Module\Options $options
   *
   * @return Content\Module\Options
   */
  public function options(Content\Module\Options $options = NULL) {
    if (NULL !== $options) {
      $this->_options = $options;
    } elseif (NULL === $this->_options) {
      $this->_options = new Content\Module\Options();
      $this->_options->papaya($this->papaya());
      $this->_options->activateLazyLoad(['guid' => $this->_guid]);
    }
    return $this->_options;
  }
}
