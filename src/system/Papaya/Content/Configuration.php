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
namespace Papaya\Content {

  use Papaya\Database;

  /**
   * Provide data encapsulation for the configuration options.
   *
   * @package Papaya-Library
   * @subpackage Content
   */
  class Configuration
    extends Database\Records\Lazy {
    /**
     * Map field names to value identifiers
     *
     * @var array
     */
    protected $_fields = [
      'name' => 'opt_name',
      'value' => 'opt_value'
    ];

    protected $_orderByProperties = ['name'];

    protected $_identifierProperties = ['name'];

    protected $_tableName = Tables::OPTIONS;
  }
}
