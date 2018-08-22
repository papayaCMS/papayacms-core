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
namespace Papaya\Plugin {

  interface Types {
    const PAGE = 'page';
    const BOX = 'box';
    const IMAGE = 'image';

    const CONNECTOR = 'connector';

    const ALIAS = 'alias';

    const OUTPUT = 'output';
    const IMPORT = 'import';
    const FILTER = 'datafilter';

    const ADMINISTRATION = 'admin';
    const ADMINISTRATION_PARSER = 'parser';

    const DATE = 'date';
    const TIME = 'time';
    const CRON_JOB = 'cronjob';

    const LOGGER = 'logger';

    const ALL = [
      self::PAGE, self::BOX, self::IMAGE,
      self::CONNECTOR, self::ALIAS,
      self::OUTPUT, self::IMPORT, self::FILTER,
      self::DATE, self::TIME, self::CRON_JOB,
      self::LOGGER,
      self::ADMINISTRATION, self::ADMINISTRATION_PARSER
    ];
  }
}
