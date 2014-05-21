<?php
/**
* This extends the base_db class to allow separate databases for statistics
*
* @copyright 2002-2007 by papaya Software GmbH - All rights reserved.
* @link http://www.papaya-cms.com/
* @license http://www.gnu.org/licenses/old-licenses/gpl-2.0.html GNU General Public License, version 2
*
* You can redistribute and/or modify this script under the terms of the GNU General Public
* License (GPL) version 2, provided that the copyright and license notes, including these
* lines, remain unmodified. papaya is distributed in the hope that it will be useful, but
* WITHOUT ANY WARRANTY; without even the implied warranty of MERCHANTABILITY or FITNESS
* FOR A PARTICULAR PURPOSE.
*
* @package Papaya
* @subpackage Statistic
* @version $Id: sys_base_db_statistic.php 39260 2014-02-18 17:13:06Z weinert $
*/

// if no specific db uri for statistic is set, use the default papaya db uri
if (!defined('PAPAYA_DB_URI_STATISTIC')) {
  define('PAPAYA_DB_URI_STATISTIC', PAPAYA_DB_URI);
}

// if no specific db write uri for statistic is set
if (!defined('PAPAYA_DB_URI_STATISTIC_WRITE')) {
  // use the statistic uri if it is set and no specific db write uri is set
  if (defined('PAPAYA_DB_URI_STATISTIC') &&
      (!defined('PAPAYA_DB_URI_WRITE') || PAPAYA_DB_URI_WRITE == NULL)) {
    /**
    * @ignore
    */
    define('PAPAYA_DB_URI_STATISTIC_WRITE', PAPAYA_DB_URI_STATISTIC);
    // otherwise use the papaya db write uri
  } else {
    define('PAPAYA_DB_URI_STATISTIC_WRITE', PAPAYA_DB_URI_WRITE);
  }
}

// generate a reasonable id for the webserver if none exists.
if (!defined('PAPAYA_WEBSERVER_IDENT')) {
  define(
    'PAPAYA_WEBSERVER_IDENT',
    md5(
      (empty($_SERVER['SERVER_ADDR']) ? '' : $_SERVER['SERVER_ADDR']).
      (empty($_SERVER['SERVER_NAME']) ? '' : $_SERVER['SERVER_NAME']).
      (empty($_SERVER['HTTP_HOST']) ? '' : $_SERVER['HTTP_HOST'])
    )
  );
}

/**
* This extends the base_db class to allow separate databases for statistics
*
* @package Papaya
* @subpackage Statistic
*/
class base_db_statistic extends base_db {
  var $databaseURI = PAPAYA_DB_URI_STATISTIC;
  var $databaseURIWrite = PAPAYA_DB_URI_STATISTIC_WRITE;

  var $options = array();
  var $ignoredIps = array();
  var $ignoredExtensions = array();
  var $proxyRealIPHeader = array();

  /**
  * PHP5 constructor
  *
  * @access public
  */
  function __construct() {
    parent::__construct();
    $this->tableStatisticOptions = PAPAYA_DB_TABLEPREFIX.'_statistic_options';
    $this->tableStatisticRequests = PAPAYA_DB_TABLEPREFIX.'_statistic_requests';
    $this->tableStatisticOptions = PAPAYA_DB_TABLEPREFIX.'_statistic_options';
    $this->tableStatisticPages = PAPAYA_DB_TABLEPREFIX.'_statistic_pages';
    $this->tableStatisticEntries = PAPAYA_DB_TABLEPREFIX.'_statistic_entries';
    $this->tableStatisticExitPages = PAPAYA_DB_TABLEPREFIX.'_statistic_exitpages';
    $this->tableStatisticMediaRequests = PAPAYA_DB_TABLEPREFIX.'_statistic_media_requests';
    $this->tableUserAgentIdStrings = PAPAYA_DB_TABLEPREFIX.'_statistic_useragent_idstrings';
    $this->tableStatisticUserPermissions = PAPAYA_DB_TABLEPREFIX.'_statistic_user_permissions';
  }

  /**
  * Load configuration
  *
  * @access public
  * @param boolean $sort (optional, default FALSE)
  */
  function loadConfiguration($sort = FALSE) {
    $this->options = array();
    $this->ignoredIps = array();
    $this->ignoredExtensions = array();
    $this->proxyRealIPHeader = array();
    $params = array($this->tableStatisticOptions);
    $sql = "SELECT statistic_option_id, statistic_option_name,
                   statistic_option_value, statistic_option_title
              FROM %s";
    if ($sort === TRUE) {
      $sql .= " ORDER BY statistic_option_name";
    }
    if ($res = $this->databaseQueryFmt($sql, $params)) {
      while ($row = $res->fetchRow(DB_FETCHMODE_ASSOC)) {
        switch ($row['statistic_option_name']) {
        case 'ignore_ip':
          $this->ignoredIps[$row['statistic_option_value']] = 1;
          break;
        case 'ignore_media_extension':
          $this->ignoredExtensions[$row['statistic_option_value']] = 1;
          break;
        case 'proxy_real_ip_header':
          $this->proxyRealIPHeader[$row['statistic_option_value']] = $row['statistic_option_value'];
          break;
        default:
          break;
        }
        $this->options[$row['statistic_option_id']] = $row;
      }
    }
  }

}

