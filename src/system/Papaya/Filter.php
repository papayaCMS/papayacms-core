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

namespace Papaya;
/**
 * Papaya filter superclass
 *
 * @package Papaya-Library
 * @subpackage Filter
 */
interface Filter {

  const IS_CSS_COLOR = 'isCssColor';
  const IS_CSS_SIZE = 'isCssSize';
  const IS_EMAIL = 'isEmail';
  const IS_FILE_NAME = 'isFileName';
  const IS_FILE_PATH = 'isFilePath';
  const IS_FLOAT = 'isFloat';
  const IS_GEO_POSITON = 'isGeoPosition';
  const IS_GERMAN_DATE = 'isGermanDate';
  const IS_GERMAN_ZIP = 'isGermanZip';
  const IS_GUID = 'isGuid';
  const IS_INTEGER = 'isInteger';
  const IS_IP_ADDRESS = 'isIpAddress';
  const IS_IP_ADDRESS_V4 = 'isIpAddressV4';
  const IS_IP_ADDRESS_V6 = 'isIpAddressV6';
  const IS_ISO_DATE = 'isIsoDate';
  const IS_ISO_DATE_TIME = 'isIsoDateTime';
  const IS_NOT_EMPTY = 'isNotEmpty';
  const IS_NOT_XML = 'isNotXml';
  const IS_PASSWORD = 'isPassword';
  const IS_PHONE = 'isPhone';
  const IS_TEXT = 'isText';
  const IS_TIME = 'isTime';
  const IS_TEXT_WITH_NUMBERS = 'isTextWithNumbers';
  const IS_URL = 'isUrl';
  const IS_URL_HOST = 'isUrlHost';
  const IS_URL_HTTP = 'isUrlHttp';
  const IS_XML = 'isXml';

  /**
   * The filter function returns the filtered version of an input value.
   *
   * It removes invalid bytes from the input value. A possible implementation whould be a
   * trimmed version of the input.
   *
   * If the input is invalid it should NULL
   *
   * @param mixed|NULL $value
   * @return mixed
   */
  function filter($value);

  /**
   * Checks an input and return true if it is valid.
   *
   * It will throw an \PapayaFilterException if the input is invalid.
   *
   * @throws \PapayaFilterException
   * @param mixed $value
   * @return boolean
   */
  function validate($value);
}
