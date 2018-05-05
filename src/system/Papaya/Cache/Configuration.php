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

/**
* Cache configuration class, defines the options curretly used by the cache services
*
* @package Papaya-Library
* @subpackage Cache
*/
class PapayaCacheConfiguration extends \PapayaConfiguration {

  /**
  * Create object and define options
  */
  public function __construct() {
    parent::__construct(
      array(
        'SERVICE' => 'file',
        'FILESYSTEM_PATH' => '/tmp',
        'FILESYSTEM_NOTIFIER_SCRIPT' => '',
        'FILESYSTEM_DISABLE_CLEAR' => FALSE,
        'MEMCACHE_SERVERS' => ''
      )
    );
  }
}
