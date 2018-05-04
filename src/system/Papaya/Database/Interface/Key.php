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

interface PapayaDatabaseInterfaceKey {

  const DATABASE_PROVIDED = 1;
  const CLIENT_GENERATED = 2;

  const ACTION_FILTER = 1;
  const ACTION_CREATE = 2;

  function clear();

  function assign(array $data);

  function getProperties();

  function getFilter($for = self::ACTION_FILTER);

  function getQualities();

  function exists();

  function __toString();
}
