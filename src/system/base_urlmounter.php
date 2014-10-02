<?php
/**
* Edit URL basic class
*
* @copyright 2002-2009 by papaya Software GmbH - All rights reserved.
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
* @subpackage Core
* @version $Id: base_urlmounter.php 39637 2014-03-19 18:31:17Z weinert $
*/

/**
* Edit URL basic class
*
* @package Papaya
* @subpackage Core
*/
class base_urlmounter extends base_db {
  /**
  * Papaya database table urls
  * @var string $tableAliases
  */
  var $tableAliases = PAPAYA_DB_TBL_URLS;
  /**
  * Papaya database table languages
  * @var string $tableLanguages
  */
  var $tableLanguages = PAPAYA_DB_TBL_LNG;
  /**
  * Papaya database table output filter / view modes
  * @var string $tableImportFilter
  */
  var $tableViewModes = PAPAYA_DB_TBL_VIEWMODES;
  /**
  * table with public page translations
  * @var string $tableTopicTrans
  */
  var $tableTopicTrans = PAPAYA_DB_TBL_TOPICS_PUBLIC_TRANS;

  /**
  * Base link
  * @var string $baseLink
  */
  var $baseLink;
  /**
  * Error
  * @var mixed
  */
  var $aError;

  /**
  * list of alias records
  * @var array
  */
  var $_aliases = array();

  /**
  * path from the request uri
  * @var string
  */
  var $path;
  /**
  * used alias
  * @var string
  */
  var $pathAlias;

  /**
  * allowed url level separators
  * @access private
  * @var array
  */
  var $urlAliasSeparators = array(',', ':', '*', '!', "'");

  /**
   * Load
   *
   * @param array|string $paths
   * @internal param string $path
   * @access public
   * @return array|FALSE
   */
  function load($paths) {
    $this->_aliases = array();
    $filter = str_replace('%', '%%', $this->databaseGetSQLCondition('a.path', $paths));
    $sql = "SELECT a.path, a.path_pattern, a.topic_id,
                   a.url_domain, a.url_params, a.url_redirectmode, a.target_url,
                   l.lng_ident, f.viewmode_ext,
                   tt.topic_title, tt.meta_title, tt.meta_keywords, tt.meta_descr,
                   a.module_params, a.module_guid
              FROM %s a
              LEFT OUTER JOIN %s l ON (l.lng_id = a.lng_id)
              LEFT OUTER JOIN %s f ON (f.viewmode_id = a.viewmode_id)
              LEFT OUTER JOIN %s tt ON (tt.topic_id = a.topic_id and tt.lng_id = a.lng_id)
             WHERE $filter
               AND a.url_domain IN ('%s', '', '*')";
    $hostName = (empty($_SERVER['HTTP_HOST']) ? '' : $_SERVER['HTTP_HOST']);
    $params = array(
      $this->tableAliases,
      $this->tableLanguages,
      $this->tableViewModes,
      $this->tableTopicTrans,
      $hostName
    );
    if ($res = $this->databaseQueryFmt($sql, $params)) {
      while ($row = $res->fetchRow(DB_FETCHMODE_ASSOC)) {
        $this->_aliases[$row['url_domain'].$row['path']] = $row;
      }
    }
    return FALSE;
  }

  /**
  * Check path slashes
  *
  * @param string $path
  * @access public
  * @return string
  */
  function checkPathSlashes($path) {
    $result = trim($path);
    $separator = $this->getSeparator();
    $encodedSeparator = str_pad(dechex(ord($separator)), 2, '0', STR_PAD_LEFT);
    $result = str_replace(
      array(
        $separator,
        '%'.strtoupper($encodedSeparator),
        '%'.strtolower($encodedSeparator)
      ),
      '/',
      $result
    );
    if (substr($result, 0, 1) != '/') {
      $result = '/'.$result;
    }
    if (substr($result, -1) != '/') {
      $result .= '/';
    }
    return preg_replace('#//+#', '/', $result);
  }

  /**
  * get the defined separator
  *
  * @access public
  * @return string
  */
  function getSeparator() {
    if (defined('PAPAYA_URL_ALIAS_SEPARATOR') &&
        in_array(PAPAYA_URL_ALIAS_SEPARATOR, $this->urlAliasSeparators)) {
      return PAPAYA_URL_ALIAS_SEPARATOR;
    } else {
      return ',';
    }
  }

  /**
  * Locate
  *
  * @access public
  * @return array|FALSE  array($url, $mode) or boolean $result FALSE
  */
  function locate() {
    $result = FALSE;
    $sidPattern = '#^/(sid\w*[\da-fA-F]{32}/)?([^\?]+)(.*)$#';
    if (preg_match($sidPattern, $_SERVER['REQUEST_URI'], $regs)) {
      list(, , $path, $addString) = $regs;
      if ($path != '') {
        $this->path = $this->checkPathSlashes($path);
        $data = $this->loadAlias($this->path);
        if (isset($data) && is_array($data)) {
          if ($data['url_redirectmode'] == 3) {
            $url = $data['target_url'];
          } else {
            $queryParams = array_merge(
              $this->parseQueryString($addString),
              $this->parseQueryString($data['url_params'])
            );
            $url = $this->getAbsoluteURL(
              $this->getWebLink(
                (int)$data['topic_id'],
                $data['lng_ident'],
                $data['viewmode_ext'],
                NULL,
                NULL,
                $this->path
              )
            );
            $url .= $this->getQueryString($queryParams);
          }
          return array($url, (int)$data['url_redirectmode'], $data);
        }
      }
    }
    return $result;
  }

  /**
  * get alias target url
  * @param $targetUrl
  * @return string|FALSE
  */
  function getAliasURL($targetUrl) {
    $target = new PapayaUrl($targetUrl);
    $application = $this->papaya();
    $request = $application->getObject('Request');
    $transformer = new PapayaUrlTransformerRelative();
    $relative = $transformer->transform(
      $request->getUrl(),
      $target
    );
    if (is_null($relative)) {
      //different servers
      return $targetUrl;
    } elseif (FALSE !== strpos($relative, '../')) {
      //path is a subpath of papaya root
      $offset = 0;
      $difference = 0;
      while (substr($relative, $offset, 3) == '../') {
        $offset += 3;
        $difference++;
      }
      $path = substr($request->getUrl()->getPath(), 1);
      $parts = explode('/', $path);
      $pathLevels = count($parts) - $difference;
      $path = '';
      $i = 0;
      $separator = $this->getSeparator();
      foreach ($parts as $part) {
        if ($i == 0 || $i < $pathLevels) {
          $path .= '/'.$part;
        } else {
          $path .= $separator.$part;
        }
        ++$i;
      }
      $path = preg_replace('('.preg_quote($separator).'+$)', '', $path);
      $protocol = PapayaUtilServerProtocol::get();
      $result = $protocol.'://'.$_SERVER['HTTP_HOST'].$path;
      if (!empty($requestData['query'])) {
        $result .= $this->recodeQueryString($requestData['query']);
      }
      return $result;
    } elseif (FALSE === strpos($relative, '/')) {
      //not redirect needed to fix path data
      return FALSE;
    } else {
      return $targetUrl;
    }
  }

  /**
  * load alias data
  * @param string $path
  * @return array
  */
  function loadAlias($path) {
    $paths = array('/*');
    $strippedAliasPath = NULL;
    $pathWeb = $this->papaya()->options->get(
      'PAPAYA_PATH_WEB',
      '/',
      new PapayaFilterNotEmpty()
    );
    if ($pathWeb != '/' && strpos($path, $pathWeb) === 0) {
      $strippedAliasPath = substr($path, strlen($pathWeb) - 1);
      $pathParts = explode('/', substr($strippedAliasPath, 1, -1));
      $numPathParts = count($pathParts) - 1;
      if (is_array($pathParts) && $numPathParts > 0) {
        $buffer = '/';
        for ($i = 0; $i < $numPathParts; $i++) {
          $buffer .= $pathParts[$i].'/';
          $paths[] .= $buffer.'*';
        }
      }
    }
    $pathParts = explode('/', substr($path, 1, -1));
    $numPathParts = count($pathParts) - 1;
    if (is_array($pathParts) && $numPathParts > 0) {
      $buffer = '/';
      for ($i = 0; $i < $numPathParts; $i++) {
        $buffer .= $pathParts[$i].'/';
        $paths[] .= $buffer.'*';
      }
    }
    if (isset($strippedAliasPath)) {
      $paths[] = $strippedAliasPath;
    }
    $paths[] = $path;
    $paths = array_reverse($paths);
    $this->load($paths);
    $result = FALSE;
    if (is_array($this->_aliases) && count($this->_aliases) > 0) {
      $hostName = (empty($_SERVER['HTTP_HOST']) ? '' : $_SERVER['HTTP_HOST']);
      foreach ($paths as $aliasPath) {
        if (isset($this->_aliases[$hostName.$aliasPath])) {
          $this->pathAlias = $aliasPath;
          $result = $this->_aliases[$hostName.$aliasPath];
          break;
        } elseif (isset($this->_aliases['*'.$aliasPath])) {
          $this->pathAlias = $aliasPath;
          $result = $this->_aliases['*'.$aliasPath];
          break;
        } elseif (isset($this->_aliases[$aliasPath])) {
          $this->pathAlias = $aliasPath;
          $result = $this->_aliases[$aliasPath];
          break;
        }
      }
      if ($result && substr($result['path'], -2) == '/*') {
        $result['path_data'] = array();
        $data = explode('/', substr($path, strlen($result['path']) - 2));
        if ($patterns = explode('/', $result['path_pattern'])) {
          foreach ($patterns as $index => $pattern) {
            if (preg_match('(^\\{[^/}]+\\}$)', $pattern) &&
                isset($data[$index])) {
              $result['path_data'][$pattern] = $data[$index];
            }
          }
          $keys = array_keys($result['path_data']);
          $values = array_values($result['path_data']);
          $result['url_params'] = str_replace($keys, $values, $result['url_params']);
          $result['target_url'] = str_replace($keys, $values, $result['target_url']);
        }
      }
    }
    return $result;
  }

  /**
  * Get frameset page to hide alias target url
  * @param string $url
  * @param array $aliasData
  * @return string
  */
  function getOutput($url, $aliasData) {
    $result = '<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01 Frameset//EN">';
    $result .= '<html>';
    $result .= '<head>';
    $result .= '<meta http-equiv="content-type" content="text/html; charset=utf-8">';
    if ((!empty($aliasData['meta_title'])) &&
         $aliasData['meta_title'] != $aliasData['topic_title']) {
      $pageTitle = $aliasData['meta_title'].' - '.$aliasData['topic_title'];
    } else {
      $pageTitle = $aliasData['topic_title'];
    }
    $result .= sprintf('<title>%s</title>', papaya_strings::escapeHTMLChars($pageTitle));
    if (!empty($aliasData['meta_keywords'])) {
      $result .= sprintf(
        '<meta name="keywords" content="%s">',
        papaya_strings::escapeHTMLChars($aliasData['meta_keywords'])
      );
    }
    if (!empty($aliasData['meta_descr'])) {
      $result .= sprintf(
        '<meta name="description" content="%s">',
        papaya_strings::escapeHTMLChars($aliasData['meta_descr'])
      );
    }
    $result .= '</head>';
    $result .= '<frameset rows="*">';
    $result .= sprintf('<frame src="%s">', papaya_strings::escapeHTMLChars($url));
    $result .= '</frameset>';
    $result .= '</html>';
    return $result;
  }

  /**
  * execute an alias plugin - dynamic aliases
  * @param array $aliasData
  * @return array|FALSE
  */
  function executeAliasPlugin($aliasData) {
    if (!empty($aliasData['module_guid'])) {
      $aliasPlugin = $this->papaya()->plugins->get($aliasData['module_guid'], $this);
      if (isset($aliasPlugin)) {
        if (!empty($aliasData['module_params'])) {
          $aliasPlugin->setData((string)$aliasData['module_params']);
        }
        if (substr($this->pathAlias, -1) == '*') {
          $subPath = substr($this->path, strlen($this->pathAlias) - 1, -1);
        } else {
          $subPath = substr($this->path, strlen($this->pathAlias), -1);
        }
        if ($result = $aliasPlugin->redirect($subPath)) {
          return $result;
        }
      }
    }
    return FALSE;
  }

  /**
  * Parse query string
  *
  * @param string $queryString
  * @access public
  * @return array $result
  */
  function parseQueryString($queryString) {
    $result = array();
    $queryPattern = '~(^|[?&])(([^=&?]+)=([^&]+))|([^&]+)~';
    if (preg_match_all($queryPattern, $queryString, $matches, PREG_SET_ORDER)) {
      foreach ($matches as $match) {
        if (isset($match[3]) && $match[3] != '') {
          $result[$match[3].'='] = $match[4];
        } elseif (isset($match[5])) {
          $result[$match[5]] = TRUE;
        }
      }
    }
    return $result;
  }

  /**
  * Get query string
  *
  * @param array $queryParams
  * @access public
  * @return string
  */
  function getQueryString($queryParams) {
    if (isset($queryParams) && is_array($queryParams) && count($queryParams) > 0) {
      $result = '';
      foreach ($queryParams as $paramName => $paramValue) {
        if (substr($paramName, -1) == '=') {
          $result .= '&'.urlencode(substr($paramName, 0, -1)).'='.urlencode($paramValue);
        } else {
          $result .= '&'.urlencode($paramName);
        }
      }
      return '?'.substr($result, 1);
    }
    return '';
  }
}


