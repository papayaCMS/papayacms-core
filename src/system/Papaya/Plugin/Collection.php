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

namespace Papaya\Plugin;

/**
 * The PluginLoaderList allows to to load module/plugin data using a list of guids.
 *
 * It stores the loaded plugin data in an internal variable and loads additional data for missing
 * guids only. It does not reset the list with each load() call, but appends the new data.
 *
 * @package Papaya-Library
 * @subpackage Plugins
 */
class Collection extends \Papaya\Database\Records\Lazy {
  /**
   * @var array()
   */
  protected $_fields = [
    'guid' => 'm.module_guid',
    'type' => 'm.module_type',
    'class' => 'm.module_class',
    'path' => 'm.module_path',
    'file' => 'm.module_file',
    'active' => 'm.module_active',
    'prefix' => 'mg.modulegroup_prefix',
    'classes' => 'mg.modulegroup_classes'
  ];

  /**
   * Database table name containing plugins/modules
   *
   * @var string
   */
  protected $_tablePlugins = 'modules';

  /**
   * Database table name containing plugin/module groups
   *
   * @var string
   */
  protected $_tablePluginGroups = 'modulegroups';

  /**
   * @var array
   */
  protected $_identifierProperties = ['guid'];

  /**
   * Load plugin data for the provided
   *
   * @param array $filter
   * @param int|null $limit
   * @param int|null $offset
   * @return bool
   */
  public function load($filter = [], $limit = NULL, $offset = NULL) {
    $databaseAccess = $this->getDatabaseAccess();
    $fields = \implode(', ', $this->mapping()->getFields());
    $sql = "SELECT $fields
              FROM %s AS m, %s AS mg
             WHERE mg.modulegroup_id = m.modulegroup_id";
    $sql .= \Papaya\Utility\Text::escapeForPrintf(
      $this->_compileCondition($filter, ' AND ').$this->_compileOrderBy()
    );
    $parameters = [
      $databaseAccess->getTableName($this->_tablePlugins),
      $databaseAccess->getTableName($this->_tablePluginGroups)
    ];
    return $this->_loadRecords($sql, $parameters, $limit, $offset, $this->_identifierProperties);
  }

  /**
   * Fetch a list of all (active) plugins of a type
   * @param string $type
   * @param bool $activeOnly
   * @return \Papaya\Iterator\Filter\Callback
   */
  public function withType($type, $activeOnly = TRUE) {
    $this->lazyLoad();
    return new \Papaya\Iterator\Filter\Callback(
      $this,
      function($plugin) use ($type, $activeOnly) {
        return $plugin['type'] === $type && (!$activeOnly || $plugin['active']);
      }
    );
  }
}
