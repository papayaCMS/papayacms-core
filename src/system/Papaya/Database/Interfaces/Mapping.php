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

namespace Papaya\Database\Interfaces;
/**
 * Interface for mapper objects to convert a database fields into object properties and back
 *
 * @package Papaya-Library
 * @subpackage Database
 * @version $Id: Mapping.php 39092 2014-01-30 17:06:00Z weinert $
 */

interface Mapping {

  const PROPERTY_TO_FIELD = 1;
  const FIELD_TO_PROPERTY = 2;

  /**
   * Map the database fields of an record to the object properties
   *
   * @param array $record
   * @return array
   */
  function mapFieldsToProperties(array $record);

  /**
   * Map the object properties to database fields
   *
   * @param array $values
   * @param bool $withAlias
   * @return array
   */
  function mapPropertiesToFields(array $values, $withAlias = TRUE);

  /**
   * Get a list of the used database fields
   *
   * @return array
   */
  function getProperties();

  /**
   * Get a list of the used database fields
   *
   * @param bool $withAlias
   * @return array
   */
  function getFields($withAlias = TRUE);

  /**
   * Get the property name for a field
   *
   * @param $field
   * @return string|FALSE
   */
  function getProperty($field);


  /**
   * Get the field name for a property
   *
   * @param $property
   * @param bool $withAlias
   * @return string|FALSE
   */
  function getField($property, $withAlias = TRUE);
}
