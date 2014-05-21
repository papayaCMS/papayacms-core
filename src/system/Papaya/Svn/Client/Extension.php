<?php
/**
* Papaya SVN client (implemented by using the pecl extension svn)
*
* @copyright 2013 by papaya Software GmbH - All rights reserved.
* @link http://www.papaya-cms.com/
* @license http://www.gnu.org/licenses/old-licenses/gpl-2.0.html GNU General Public License, version 2
*
* You can redistribute and/or modify this script under the terms of the GNU General Public
* License (GPL) version 2, provided that the copyright and license notes, including these
* lines, remain unmodified. papaya is distributed in the hope that it will be useful, but
* WITHOUT ANY WARRANTY; without even the implied warranty of MERCHANTABILITY or FITNESS
* FOR A PARTICULAR PURPOSE.
*
* @package Papaya-Library
* @subpackage Svn
* @version $Id: Extension.php 39176 2014-02-10 18:52:34Z weinert $
*/

class PapayaSvnClientExtension implements PapayaSvnClient {

  /**
  * Lists entries in an SVN repository at $url .
  * @link http://php.net/manual/en/function.svn-ls.php
  * @codeCoverageIgnore
  * @param string $url
  * @return array name => array with keys created_rev, last_author,
  *   size, time, time_t, name, type
  */
  function ls($url) {
    return svn_ls($url);
  }
}