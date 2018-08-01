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
* Basic statistic object
* @package Papaya
* @subpackage Statistic
 * @deprecated
*/
class base_statistic_logging extends base_db_statistic {

  /**
  * Options
  * @var array $options
  */
  var $options = NULL;

  /**
  * Log exit page
  *
  * @param integer $requestId
  * @param string $url
  * @access public
  */
  function logExitPage($requestId, $url) {
    if (isset($requestId) && $url != '' && $this->loggable()) {
      $params = array(
        'statistic_server_id' => PAPAYA_WEBSERVER_IDENT,
        'statistic_request_id' => $requestId,
        'statistic_exitpage_url' => $url
      );
      $this->databaseInsertRecord($this->tableStatisticExitPages, NULL, $params);
    }
  }

  /**
  * logs page request
  *
  * @param $requestData
  * @param integer $lngId optional, default value 0
  * @param boolean $cachedPage optional, default value FALSE
  * @access public
  * @return integer $requestId papaya_statistic_requests.statistic_request_id
  *         for use of reference with moduleactions
  */
  function logRequest($requestData, $lngId = 0, $cachedPage = FALSE) {
    $application = $this->papaya();
    $request = $application->getObject('Request');

    $filename = $request->getParameter(
      'page_title', 'index', NULL, \Papaya\Request::SOURCE_PATH
    );
    $preview = $request->getParameter(
      'preview', FALSE, NULL, \Papaya\Request::SOURCE_PATH
    );

    $requestId = NULL;
    if ($this->loggable($preview, $filename)) {
      $protocol = \Papaya\Utility\Server\Protocol::get();

      if (defined("PAPAYA_STATISTIC_PRESERVE_IP") && PAPAYA_STATISTIC_PRESERVE_IP) {
        $remoteAddress = $this->getRealIP();
      } else {
        // this is a simple obfuscation of the ip address, it should take some time
        // to trace this back to an ip. If need arises, this can be make completely
        // irreversible
        $remoteAddress = md5($this->getRealIP());
      }
      // 2011-09 Always empty due to privacy concerns. Not removed due to backwards compatibility.
      $userId = '';

      $session = $this->papaya()->session;
      $sid = (isset($session->sessionId)) ? $session->sessionId : '';

      $dataRequest['statistic_ip'] = $remoteAddress;
      $dataRequest['statistic_country'] =
        $this->getCountryByIP($this->getRealIP());
      $dataRequest['statistic_sid'] = $sid;
      $dataRequest['statistic_useragent'] =
        empty($_SERVER['HTTP_USER_AGENT']) ? '' : (string)$_SERVER['HTTP_USER_AGENT'];
      $dataRequest['statistic_time'] = time();
      $dataRequest['statistic_uri'] =
        $protocol.'://'.$_SERVER['HTTP_HOST'].$_SERVER['REQUEST_URI'];
      $dataRequest['statistic_referer'] =
        empty($_SERVER['HTTP_REFERER']) ? '' : (string)$_SERVER['HTTP_REFERER'];
      $dataRequest['user_id'] = $userId;

      $mode = $request->getParameter('mode', '', NULL, \Papaya\Request::SOURCE_PATH);
      switch ($mode) {
      case 'image':
      case 'media':
      case 'thumb':
      case 'thumbnail':
      case 'download':
        $dataRequest['statistic_server_id'] = PAPAYA_WEBSERVER_IDENT;
        $dataRequest['statistic_media_mode'] = $request->getParameter(
          'mode', '', NULL, \Papaya\Request::SOURCE_PATH
        );
        $dataRequest['statistic_media_id'] = $request->getParameter(
          'media_id', '', NULL, \Papaya\Request::SOURCE_PATH
        );

        $this->databaseInsertRecord($this->tableStatisticMediaRequests, NULL, $dataRequest);
        return 0;
        break;
      case 'page':
      default:
        $dataPage['topic_id'] = $request->getParameter(
          'page_id',
          $this->papaya()->options->get('PAPAYA_PAGEID_DEFAULT', 0),
          NULL,
          \Papaya\Request::SOURCE_PATH
        );

        $dataPage['lng_id'] = ((int)$lngId > 0)
          ? (int)$lngId
          : $this->papaya()->options->get('PAPAYA_CONTENT_LANGUAGE', 0);
        $dataPage['categ_id'] = $request->getParameter(
          'category_id', 0, NULL, \Papaya\Request::SOURCE_PATH
        );
        $dataPage['statistic_mode'] = $request->getParameter(
          'output_mode',
          $this->papaya()->options->get('PAPAYA_URL_EXTENSION', 'html'),
          NULL,
          \Papaya\Request::SOURCE_PATH
        );

        $dataPage['statistic_filename'] = $request->getParameter(
          'page_title', '', NULL, \Papaya\Request::SOURCE_PATH
        );
        $dataPage['statistic_server_id'] = PAPAYA_WEBSERVER_IDENT;

        if ($statisticPageId = $this->getStatisticPageId($dataPage)) {
          $dataRequest['statistic_page_id'] = $statisticPageId;
        } else {
          base_object::logMsg(
            MSG_ERROR,
            PAPAYA_LOGTYPE_SYSTEM,
            'statistic: could not find nor create page record',
            'Probably papaya_statistic_pages is not there or has a wrong structure. '.
            'Please update in the modules section.'
          );
        }
        $dataRequest['statistic_server_id'] = PAPAYA_WEBSERVER_IDENT;
        if ($cachedPage) {
          // if the page was cached, its module will not be executed, so no
          // further use of the request id is possible or necessary
          $this->databaseInsertRecord(
            $this->tableStatisticRequests, NULL, $dataRequest
          );
          return 0;
        } else {
          $requestId = $this->databaseInsertRecord(
            $this->tableStatisticRequests, 'statistic_request_id', $dataRequest
          );
          $this->writeOtherLogs($requestId);
        }
        break;
      }
    }
    return $requestId;
  }

  /**
  * This method tries to find out the real ip of a request. I.e. proxy headers
  * are evaluated.
  *
  * @TODO FIXME check use of $HTTP_X_FORWARDED, $HTTP_FORWARDED_FOR, $HTTP_FORWARDED,
  *   $HTTP_VIA, $HTTP_X_COMING_FROM, $HTTP_COMING_FROM
  *
  * See {@link http://www.antispam.de/forum/showthread.php?t=9113}
  * See {@link http://bytes.com/groups/php/5938-checking-private-ip-ranges}
  *
  * @return string $result first non-private ip of forwarded-for header or remote_addr
  */
  function getRealIP() {
    if (!is_array($this->proxyRealIPHeader)) {
      $this->proxyRealIPHeader = array();
    }
    $this->proxyRealIPHeader['HTTP_X_FORWARDED_FOR'] = 'HTTP_X_FORWARDED_FOR';
    foreach ($this->proxyRealIPHeader as $ipHeader) {
      $ipHeader = 'HTTP_'.strtr($ipHeader, '-', '_');
      if (isset($_SERVER[$ipHeader])) {
        $privateIPRegexes = array(
          "/^0\./",                                   // current network
          "/^127\.0\.0\.1/",                          // localhost
          "/^10\..*/",                                // class A private
          "/^172\.((1[6-9])|(2[0-9])|(3[0-1]))\..*/", // class B private
          "/^192\.168\..*/",                          // class C private
          // "/^224\..*/",                               // multicast - not sure
          // "/^240\..*/",                               // reserved - not sure
        );
        $ips = explode(',', $_SERVER[$ipHeader]);
        foreach ($ips as $ip) {
          $ip = trim($ip);
          if ($ip != '') {
            $private = 0;
            foreach ($privateIPRegexes as $regex) {
              if (preg_match($regex, trim($ip))) {
                // ip is private don't use it
                $private ++;
              }
            }
            if ($private == 0) {
              // return the first non-private IP
              return $ip;
            }
          }
        }
      }
    }
    // if no forward header was found or all were private, return the remote address
    return $_SERVER['REMOTE_ADDR'];
  }

  /**
  * This method retrieves the matching page_id or creates it if not there
  *
  * @param array $dataPage the page data to check/insert
  * @return boolean The page_id on success, otherwise false
  */
  function getStatisticPageId($dataPage) {
    $result = FALSE;
    $sql = "SELECT statistic_page_id
              FROM %s
              WHERE topic_id = '%d'
                AND lng_id = '%d'
                AND categ_id = '%d'
                AND statistic_mode = '%s'
                AND statistic_filename = '%s'
                AND statistic_server_id = '%s'
           ";
    $params = array($this->tableStatisticPages,
      $dataPage['topic_id'], $dataPage['lng_id'], $dataPage['categ_id'],
      $dataPage['statistic_mode'], $dataPage['statistic_filename'],
      $dataPage['statistic_server_id']);
    if ($res = $this->databaseQueryFmt($sql, $params)) {
      if ($row = $res->fetchRow()) {
        $result = $row[0];
      } else {
        $result = $this->databaseInsertRecord(
          $this->tableStatisticPages, 'statistic_page_id', $dataPage
        );
      }
    }
    return $result;
  }

  /**
  * hook for additional logging facilities like statistic actions (deprecated)
  * and statistic entries
  *
  * @param integer $requestId the page requests ID to be passed to flushLog calls
  */
  function writeOtherLogs($requestId) {
    $trackingObj = base_statistic_entries_tracking::getInstance();
    $trackingObj->flushLog($requestId);
  }

  /**
  * Get country by ip
  *
  * @param string $ip
  * @access public
  * @return mixed string $country code or boolean FALSE
  */
  function getCountryByIP($ip) {
    if (isset($ip) && $ip != '') {
      if (extension_loaded('geoip')) {
        if (geoip_db_avail(GEOIP_COUNTRY_EDITION)) {
          return @geoip_country_code_by_name($ip);
        }
      } else {
        $path = dirname(__FILE__).'/../../external/geoip';
        if (include_once($path.'/geoip.inc')) {
          $gi = geoip_open($path.'/geoip.dat', GEOIP_STANDARD);
          $country = geoip_country_code_by_addr($gi, $ip);
          geoip_close($gi);
          return $country;
        }
      }
    }
    return FALSE;
  }

  /**
   * checks whether request is valid for logging (e.g. not favicon, no preview,
   * not in list of ignored ips/hosts)
   *
   * @access public
   * @param bool $preview
   * @param string $filename
   * @return boolean TRUE if loggable, FALSE if should be ignored
   */
  function loggable($preview = FALSE, $filename = '') {
    if (defined('PAPAYA_ADMIN_PAGE') && PAPAYA_ADMIN_PAGE) {
      return FALSE;
    } elseif ($preview) {
      return FALSE;
    } elseif ($filename == 'favicon') {
      return FALSE;
    } else {
      if (empty($this->options)) {
        $this->loadConfiguration();
      }
      if (isset($this->ignoredIps) && is_array($this->ignoredIps) &&
          isset($this->ignoredIps[$this->getRealIP()])) {
        return FALSE;
      }
    }
    return TRUE;
  }
}

