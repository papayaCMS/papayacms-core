<?php
/**
 * papaya CMS
 *
 * @copyright 2000-2019 by papayaCMS project - All rights reserved.
 * @link http://www.papaya-cms.com/
 * @license http://www.gnu.org/licenses/old-licenses/gpl-2.0.html GNU General Public License, version 2
 *
 *  You can redistribute and/or modify this script under the terms of the GNU General Public
 *  License (GPL) version 2, provided that the copyright and license notes, including these
 *  lines, remain unmodified. papaya is distributed in the hope that it will be useful, but
 *  WITHOUT ANY WARRANTY; without even the implied warranty of MERCHANTABILITY or FITNESS
 *  FOR A PARTICULAR PURPOSE.
 */

namespace Papaya\CMS\Content\Media {

  use Papaya\Database\Interfaces\Order as DatabaseOrder;

  class MimeTypes extends \Papaya\Database\Records\Lazy {

    protected $_fields = [
      'id' => 'mimetype_id',
      'group_id' => 'mimegroup_id',
      'type' => 'mimetype',
      'icon' => 'mimetype_icon',
      'extension' => 'mimetype_ext',
      'supports_ranges' => 'range_support',
      'enable_shaping' => 'shaping',
      'shaping_limit' => 'shaping_limit',
      'shaping_offset' => 'shaping_offset',
      'download_octet_stream' => 'download_octet_stream'
    ];

    protected $_tableName = \Papaya\CMS\Content\Tables::MEDIA_MIMETYPES;

    protected $_identifierProperties = ['id'];
    protected $_orderByProperties = [
      'name' => DatabaseOrder::ASCENDING,
      'id' => DatabaseOrder::ASCENDING,
    ];

  }
}

