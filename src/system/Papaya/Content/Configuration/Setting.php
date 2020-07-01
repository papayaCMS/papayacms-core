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

namespace Papaya\Content\Configuration {

  use Papaya\Content\Tables;
  use Papaya\Database;

  /**
   * Provide data encapsulation for the configuration options.
   *
   * @property string $name
   * @property string $value
   */
  class Setting
    extends Database\Record\Lazy {
    /**
     * Map field names to value identifiers
     *
     * @var array
     */
    protected $_fields = [
      'name' => 'opt_name',
      'value' => 'opt_value'
    ];

    protected $_tableName = Tables::OPTIONS;


    /**
     * @return Database\Record\Key\Fields
     */
    protected function _createKey() {
      return new Database\Record\Key\Fields($this, $this->_tableName, ['name']);
    }
  }
}
