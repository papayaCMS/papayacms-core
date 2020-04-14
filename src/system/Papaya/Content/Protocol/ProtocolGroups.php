<?php
/**
 * papaya CMS
 *
 * @copyright 2000-2020 by papayaCMS project - All rights reserved.
 * @link http://www.papaya-cms.com/
 * @license http://www.gnu.org/licenses/old-licenses/gpl-2.0.html GNU General Public License, version 2
 *
 *  You can redistribute and/or modify this script under the terms of the GNU General Public
 *  License (GPL) version 2, provided that the copyright and license notes, including these
 *  lines, remain unmodified. papaya is distributed in the hope that it will be useful, but
 *  WITHOUT ANY WARRANTY; without even the implied warranty of MERCHANTABILITY or FITNESS
 *  FOR A PARTICULAR PURPOSE.
 */

namespace Papaya\Content\Protocol {

  class ProtocolGroups implements \IteratorAggregate {

    const UNKNOWN = 0;
    const USER = 1;
    const PAGES = 2;
    const DATABASE = 3;
    const CALENDAR = 4;
    const CRON = 5;
    const COMMUNITY = 6;
    const SYSTEM = 7;
    const PLUGINS = 8;
    const PHP = 9;
    const DEBUG = 10;

    private static $_GROUPS = [
      self::UNKNOWN => '',
      self::USER => 'User',
      self::PAGES => 'Pages',
      self::DATABASE => 'Database',
      self::CALENDAR => 'Calender',
      self::CRON => 'Cronjobs',
      self::COMMUNITY => 'Community',
      self::SYSTEM => 'System',
      self::PLUGINS => 'Modules',
      self::PHP => 'PHP',
      self::DEBUG => 'Debug'
    ];

    public function getIterator() {
      return new \ArrayIterator(self::$_GROUPS);
    }

    public static function getLabel($id) {
      return isset(self::$_GROUPS[$id]) ? self::$_GROUPS[$id] : '';
    }
  }
}

