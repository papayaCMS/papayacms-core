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

  use Papaya\Content\Tables;
  use Papaya\Database\Interfaces\Order;
  use Papaya\Database\Records\Lazy as LazyDatabaseRecords;

  class ProtocolEntries extends LazyDatabaseRecords {

    protected $_fields = [
      'id' => 'log_id',
      'severity' => 'log_msgno',
      'summary' => 'log_msg_short',
      'group_id' => 'log_msgtype',
      'created' => 'log_time'
    ];

    protected $_orderByProperties = [
      'created' => Order::DESCENDING,
      'id' => Order::ASCENDING
    ];

    protected $_tableName = Tables::LOG;
  }
}

