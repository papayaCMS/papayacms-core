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
namespace Papaya\CMS\Content;

use Papaya\Database;
use Papaya\Utility;

/**
 * Load/save a domain record
 *
 * @package Papaya-Library
 * @subpackage Content
 *
 * @property int $id
 * @property string $host
 * @property int $hostLength
 * @property int $scheme
 * @property int $mode
 * @property string $data
 * @property array $options
 */
class Domain extends Database\Record {
  /**
   * No special handling
   */
  const MODE_DEFAULT = 0;

  /**
   * Redirect to another domain - keep request uri
   */
  const MODE_REDIRECT_DOMAIN = 1;

  /**
   * Redirct to a specific page on another domain
   */
  const MODE_REDIRECT_PAGE = 2;

  /**
   * Redirect to a start page in a specific language
   */
  const MODE_REDIRECT_LANGUAGE = 3;

  /**
   * Restrict access to a part of the page tree and allow to change options
   * This works like virtual servers.
   */
  const MODE_VIRTUAL_DOMAIN = 4;

  /**
   * Mapping fields
   *
   * @var array
   */
  protected $_fields = [
    'id' => 'domain_id',
    'host' => 'domain_hostname',
    'host_length' => 'domain_hostlength',
    'scheme' => 'domain_protocol',
    'mode' => 'domain_mode',
    'data' => 'domain_data',
    'options' => 'domain_options'
  ];

  /**
   * Table containing domain information
   *
   * @var string
   */
  protected $_tableName = Tables::DOMAINS;

  /**
   * Create the mapping objects and set callbacks to handle the
   * special fields like "domain_options" and "domain_hostlength"
   *
   * "domain_options" is an array serialized to xml and "domain_hostlength" is an denormalized index
   * used to order the domain lists in some cases.
   *
   * @return Database\Record\Mapping
   */
  public function _createMapping() {
    $mapping = parent::_createMapping();
    $mapping->callbacks()->onMapValue = function(
      /** @noinspection PhpUnusedParameterInspection */
      $context, $mode, $property, $field, $value
    ) {
      if ('options' === $property) {
        return (Database\Record\Mapping::PROPERTY_TO_FIELD === $mode)
          ? Utility\Text\XML::serializeArray($value)
          : Utility\Text\XML::unserializeArray($value);
      }
      return $value;
    };
    $mapping->callbacks()->onAfterMapping = function(
      /** @noinspection PhpUnusedParameterInspection */
      $context, $mode, $values, $record
    ) {
      if (Database\Record\Mapping::PROPERTY_TO_FIELD === $mode) {
        $result = $record;
        $result['domain_hostlength'] = \strlen($record['domain_hostname']);
      } else {
        $result = $values;
      }
      return $result;
    };
    return $mapping;
  }
}
