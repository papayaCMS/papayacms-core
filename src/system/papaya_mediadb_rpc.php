<?php
/**
* class for media db xmlrpc requests in papaya backend
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
* @subpackage Media-Database
* @version $Id: papaya_mediadb_rpc.php 39260 2014-02-18 17:13:06Z weinert $
*/

/**
* class for media db xmlrpc requests in papaya backend
*
* @package Papaya
* @subpackage Media-Database
*/
class papaya_mediadb_rpc extends base_mediadb_rpc {

  /**
  * output a response xml that can be used to update the status dialog
  * For the backend this is a function callback in the javascript
  *
  * @access private
  * @return void
  */
  function outputXML() {
    $info = reset($this->info);
    $barPosition = (int)$info['percent'];
    $message = $this->_gt($info['message']);
    $status = $this->_gt($info['status']);
    if ($info['eta'] != '??') {
      $message .= "\n ".sprintf(
        $this->_gt('%s of %s (%s/s)'),
        $info['upl'],
        $info['total'],
        $info['speed']
      );
      $message .= "\n ".sprintf(
        $this->_gt('Time left: %s min'),
        $info['eta']
      );
    }
    // prevent caching by browser
    header('Cache-Control: no-store, no-cache, must-revalidate');
    header('Cache-Control: post-check=0, pre-check=0', FALSE);
    header('Pragma: no-cache');
    header('Content-Type: text/xml; charset=utf-8');
    // XML headers
    echo '<?xml version="1.0" encoding="UTF-8"?>'.LF;
    echo '<response>';
    echo '<method>rpcFileUploadProgress</method>';
    echo sprintf(
      '<param name="progress" value="%d" />',
      $barPosition
    );
    echo sprintf(
      '<param name="message" value="%s" />',
      papaya_strings::escapeHTMLChars($message)
    );
    echo sprintf(
      '<param name="status" value="%s" />',
      papaya_strings::escapeHTMLChars($status)
    );
    echo '<data></data>';
    echo '</response>';
    exit;
  }
}
