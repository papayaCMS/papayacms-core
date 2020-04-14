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
  use Papaya\Database\Record\Lazy as LazyDatabaseRecord;

  /**
   * @property int $id
   * @property int $severity
   * @property int $groupId
   * @property string $summary
   * @property string $content
   * @property int $createdAt
   * @property string $requestURL
   * @property string $script
   * @property string $clientIP
   * @property string $refererURL
   * @property string $cookies
   * @property string $papayaVersion
   * @property string $projectVersion
   * @property string $userID
   * @property string $userName
   */
  class ProtocolEntry extends LazyDatabaseRecord {

    const SEVERITY_INFO = 0;
    const SEVERITY_WARNING = 1;
    const SEVERITY_ERROR = 2;
    const SEVERITY_DEBUG = 3;

    const SEVERITY_LABELS = [
      self::SEVERITY_INFO => 'Info',
      self::SEVERITY_WARNING => 'Warning',
      self::SEVERITY_ERROR => 'Error',
      self::SEVERITY_DEBUG => 'Debug',
    ];

    protected $_fields = [
      'id' => 'log_id',
      'created_at' => 'log_time',
      'severity' => 'log_msgno',
      'group_id' => 'log_msgtype',
      'summary' => 'log_msg_short',
      'content' => 'log_msg_long',
      'request_url' => 'log_msg_uri',
      'script' => 'log_msg_script',
      'client_ip' => 'log_msg_from_ip',
      'referer_url' => 'log_msg_referer',
      'cookies' => 'log_msg_cookies',
      'papaya_version' => 'log_version_papaya',
      'project_version' => 'log_version_project',
      'user_id' => 'user_id',
      'user_name' => 'username'
    ];

    protected $_tableName = Tables::LOG;

    public static function getSeverityLabel($severity) {
      $labels = self::SEVERITY_LABELS;
      return isset($labels[$severity]) ? $labels[$severity] : '';
    }
  }
}

