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
* Test OK
*/
define('TESTRESULT_OK', 1);
/**
* Test failed
*/
define('TESTRESULT_FAILED', 2);
/**
* Can not test
*/
define('TESTRESULT_UNKNOWN', 3);
/**
* Test failed but optional element
*/
define('TESTRESULT_OPTIONAL', 4);
/**
* Test not implemented - for debugging
*/
define('TESTRESULT_NOT_IMPLEMENTED', 0);

/**
* system test for papaya
*
* @package Papaya
* @subpackage Core
* @version $Id: papaya_systemtest.php 39732 2014-04-08 15:34:45Z weinert $*
*/
class papaya_systemtest {

  /**
  * information functions
  *   caption => function name
  * @var array
  */
  var $information = array(
    'Installation URL' => 'infoInstallURL',
    'Installation Path' => 'infoInstallPath',
    'Library Path' => 'infoLibraryPath',
    'Document Root' => 'infoDocumentRoot',
    'Webserver' => 'infoWebserver',
    'Operating System' => 'infoOperatingSystem',
    'PHP Version' => 'infoPHPVersion',
    'Server API' => 'infoServerAPI',
    'Unicode Support' => 'infoUnicodeSupport',
    'PCRE Version' => 'infoPcreVersion',
    'MySQL Client' => 'infoMySQLVersion',
    'MySQLi Client' => 'infoMySQLiVersion',
    'SQlite Library' => 'infoSQLiteVersion',
    'GD Version' => 'infoGDVersion',
    'libXSLT Version' => 'infoLibXSLTVersion',
    'Memory limits' => 'infoMemoryLimits'
  );

  /**
  * test funtions
  *   caption => test function, context help url
  * @var array
  */
  var $tests = array(
    'mod_rewrite (Apache extension)' => array(
      'testModRewrite', 'http://www.papaya-cms.com/installhelp/modrewrite'
    ),
    'Database Extension' => array('testDatabase', 'http://www.papaya-cms.com/installhelp/dbext'),
    'Database Connection' => array(
      'testDatabaseConnection', 'http://www.papaya-cms.com/installhelp/dbcon', 1
    ),
    'Database Privileges' => array(
      'testDatabasePermissions', 'http://www.papaya-cms.com/installhelp/dbperm', 1
    ),
    'Crypt' => 'testCrypt',
    'Unicode Support' => 'testUnicodeSupport',
    'PCRE Unicode Support' => array('testPcreUnicode'),
    'XML Extension' => array('testXML', 'http://www.papaya-cms.com/installhelp/xml'),
    'XSLT Extension' => array('testXSLT', 'http://www.papaya-cms.com/installhelp/xslt'),
    'eXSLT Support' => array('testEXslt', NULL, 1),
    'GD Extension' => array('testGD', 'http://www.papaya-cms.com/installhelp/gd'),
    'GD GIF read' => array('testGDGIFRead', NULL, 1),
    'GD GIF write' => array('testGDGIFWrite', NULL, 1),
    'GD JPEG read/write' => array('testGDJPEG', NULL, 1),
    'GD PNG read/write' => array('testGDPNG', NULL, 1),
    'Xhprof' => array('testXhprof'),
    'Memory Limit Increase' => array('testMemoryLimitIncrease')
  );

  /**
  * results of info funtions
  * @var array
  */
  var $resultInfos = array();
  /**
  * results of test functions
  * @var array
  */
  var $resultTests = array();
  /**
  * test result summary (counts)
  * @var array
  */
  var $resultTestSummary = array();

  /**
  * test result message, possiblity for a test to store an additional detail message.
  * @var array
  */
  var $resultTestMessages = array();

  /**
   * @var \PapayaUiImages|array
   */
  public $images = array();

  /**
  * execute tests and gather infos
  *
  * @access public
  */
  function execute() {
    foreach ($this->information as $title => $method) {
      $this->resultInfos[$title] = $this->getInformation($method);
    }
    $this->resultTestSummary = array(
      TESTRESULT_OK => 0,
      TESTRESULT_FAILED => 0,
      TESTRESULT_UNKNOWN => 0,
      TESTRESULT_OPTIONAL => 0,
      TESTRESULT_NOT_IMPLEMENTED => 0
    );
    foreach ($this->tests as $title => $test) {
      if (is_array($test)) {
        $testResult = $this->runTest($test[0], $title);
      } else {
        $testResult = $this->runTest($test, $title);
      }
      $this->resultTests[$title] = $testResult;
      ++$this->resultTestSummary[$testResult];
    }
  }

  /**
  * get a info / execute an info function
  *
  * @param string $method
  * @access private
  * @return string
  */
  function getInformation($method) {
    if (method_exists($this, $method)) {
      return $this->$method();
    } else {
      return 'FAILED';
    }
  }

  /**
  * run a test
  *
  * @param string $method
  * @param string $title test title
  * @access private
  * @return integer test result
  */
  function runTest($method, $title) {
    if (method_exists($this, $method)) {
      return $this->$method($title);
    } else {
      return TESTRESULT_NOT_IMPLEMENTED;
    }
  }

  /**
  * get installation path including document root
  *
  * @access private
  * @return string
  */
  public static function infoInstallPath() {
    $path = strtr($_SERVER['DOCUMENT_ROOT'], '\\', '/');
    $subPath = dirname(dirname($_SERVER['PHP_SELF']));
    if (substr($subPath, 0, 1) == '/') {
      $subPath = substr($subPath, 1);
    }
    if (substr($path, -1) != '/') {
      $path .= '/'.$subPath;
    } else {
      $path .= $subPath;
    }
    return $path;
  }

  /**
  * get installation url includign domain
  *
  * @access private
  * @return string
  */
  function infoInstallURL() {
    $subPath = dirname(dirname($_SERVER['PHP_SELF']));
    if (substr($subPath, 0, 1) != '/') {
      $subPath = '/'.$subPath;
    }
    $protocol = PapayaUtilServerProtocol::get();
    $url = $protocol.'://'.$_SERVER['HTTP_HOST'].$subPath;
    return $url;
  }

  /**
  * get library path
  *
  * @access private
  * @return string
  */
  function infoLibraryPath() {
    if (defined('PAPAYA_INCLUDE_PATH')) {
      return PAPAYA_INCLUDE_PATH;
    } else {
      return '';
    }
  }

  /**
  * get php version
  *
  * @access private
  * @return string
  */
  function infoPHPVersion() {
    return PHP_VERSION;
  }

  /**
  * get operating system
  *
  * @access private
  * @return string
  */
  function infoOperatingSystem() {
    return PHP_OS;
  }

  /**
  * get webserver identifier string
  *
  * @access private
  * @return string
  */
  function infoWebserver() {
    if (isset($_SERVER) && is_array($_SERVER) && isset($_SERVER['SERVER_SOFTWARE'])) {
      return $_SERVER['SERVER_SOFTWARE'];
    }
    return '';
  }

  /**
  * get server php api
  *
  * @access private
  * @return string
  */
  function infoServerAPI() {
    return PHP_SAPI;
  }

  /**
  * get document root
  *
  * @access private
  * @return string
  */
  function infoDocumentRoot() {
    if (isset($_SERVER) && is_array($_SERVER) && isset($_SERVER['DOCUMENT_ROOT'])) {
      return $_SERVER['DOCUMENT_ROOT'];
    }
    return '';
  }

  /**
   * Get information about availabe unicode extensions
   */
  public function infoUnicodeSupport() {
    if (extension_loaded('intl')) {
      return 'ext/intl'.(class_exists('Transliterator', FALSE) ? '' : ' (Transliterator missing)');
    }
    if (extension_loaded('mb_string')) {
      return (extension_loaded('intl')  ? ', ' : '').'ext/mb_string';
    }
    return 'None';
  }

  /**
  * get pcre version string
  *
  * @access private
  * @return string
  */
  function infoPcreVersion() {
    if (defined('PCRE_VERSION')) {
      return PCRE_VERSION;
    }
    return '';
  }

  /**
  * get mysql client library version used in ext/mysql
  *
  * @access private
  * @return string
  */
  function infoMySQLVersion() {
    if (function_exists('mysql_get_client_info')) {
      return mysql_get_client_info();
    }
    return 'None';
  }

  /**
  * get mysql client library version used in ext/mysqli
  *
  * @access private
  * @return string
  */
  function infoMySQLiVersion() {
    if (function_exists('mysqli_get_client_info')) {
      return mysqli_get_client_info();
    }
    return 'None';
  }

  /**
  * get SQLite library version
  *
  * @access private
  * @return string
  */
  function infoSQLiteVersion() {
    if (function_exists('sqlite_libversion')) {
      $result = sqlite_libversion();
      return $result['client'];
    }
    return 'None';
  }

  /**
  * get gd version
  *
  * @access private
  * @return string
  */
  function infoGDVersion() {
    if (function_exists('gd_info')) {
      $gdInfo = gd_info();
      return $gdInfo['GD Version'];
    } elseif (function_exists('imagetypes')) {
      return 'Unknown';
    }
    return 'None';
  }

  /**
  * get libxslt/eXSLT version
  *
  * @access private
  * @return string
  */
  function infoLibXSLTVersion() {
    if (function_exists('domxml_xslt_version')) {
      return domxml_xslt_version();
    } elseif (defined('LIBXSLT_DOTTED_VERSION') && defined('LIBEXSLT_DOTTED_VERSION')) {
      return LIBXSLT_DOTTED_VERSION.' (eXSLT '.LIBEXSLT_DOTTED_VERSION.')';
    } elseif (defined('LIBXSLT_DOTTED_VERSION')) {
      return LIBXSLT_DOTTED_VERSION;
    }
    return 'None';
  }

  /**
  * get different memory limit configuration
  *
  * @return string
  */
  function infoMemoryLimits() {
    $memoryLimit = @ini_get('memory_limit');
    if (extension_loaded('suhosin')) {
      if ($suhosinMemoryLimit = @ini_get('suhosin.memory_limit')) {
        $suhosinMemoryLimit = PapayaUtilBytes::fromString($suhosinMemoryLimit);
      } else {
        $suhosinMemoryLimit = 0;
      }
      if ($suhosinMemoryLimit == 0) {
        $suhosinMemoryLimit = $memoryLimit;
      }
      if ($suhosinMemoryLimit > 0) {
        $memoryLimit .= ' (Suhoshin '.$suhosinMemoryLimit.')';
      }
    }
    return $memoryLimit;
  }

  /**
  * is selected db extension loaded?
  *
  * @access private
  * @return integer
  */
  function testDatabase() {
    try {
      $uriData = new PapayaDatabaseSourceName(PAPAYA_DB_URI);
      if (extension_loaded($uriData->api)) {
        return TESTRESULT_OK;
      }
    } catch (\Papaya\Database\Exception\Connect $e) {
    }
    return TESTRESULT_FAILED;
  }

  /**
   * can php connect to the database?
   *
   * @access public
   * @param string $title
   * @return integer
   */
  function testDatabaseConnection($title) {
    /** @var \Papaya\Application\Cms $application */
    $application = \Papaya\Application::getInstance();
    $database = $application->database->getConnector();
    try {
      if ($database->connect($this, TRUE) && $database->connect($this, FALSE)) {
        return TESTRESULT_OK;
      }
    } catch (\Papaya\Database\Exception\Connect $e) {
      $this->resultTestMessages[$title] = $e->getMessage();
    }
    return TESTRESULT_FAILED;
  }

  /**
  * check the connection permissions
  *
  * @access private
  * @return integer
  */
  function testDatabasePermissions() {
    try {
      /** @var \Papaya\Application\Cms $application */
      $application = \Papaya\Application::getInstance();
      $database = $application->database->getConnector();
      if ($database->connect($this, FALSE)) {
        $dbSyntax = $database->getProtocol();
        switch ($dbSyntax) {
        case 'mysql' :
        case 'mysqli' :
          $perms = array(
            'SELECT' => FALSE,
            'INSERT' => FALSE,
            'UPDATE' => FALSE,
            'DELETE' => FALSE,
            'CREATE' => FALSE,
            'DROP' => FALSE,
            'ALTER' => FALSE
          );
          if ($res = $database->query($this, 'SHOW GRANTS')) {
            $dbName = strtolower($database->databaseConfiguration['write']->database);
            $dbNameSlashed = strtr($dbName, array('_' => '\\_'));
            while ($row = $res->fetchRow()) {
              $sqlStr = strtolower($row[0]);
              if (FALSE !== strpos($sqlStr, 'on *.*') ||
                  FALSE !== strpos($sqlStr, 'on `'.$dbName.'`.*') ||
                  FALSE !== strpos($sqlStr, 'on `'.$dbNameSlashed.'`.*')) {
                if (FALSE !== strpos($sqlStr, 'grant all privileges')) {
                  foreach ($perms as $permKey => $permValue) {
                    $perms[$permKey] = TRUE;
                  }
                } elseif (preg_match('~GRANT((\s+[\w ]+,)+([\w ]+))ON~i', $row[0], $match)) {
                  $splitted = explode(',', $match[1]);
                  foreach ($splitted as $permNameStr) {
                    $permNameStr = trim($permNameStr);
                    if (isset($perms[$permNameStr])) {
                      $perms[$permNameStr] = TRUE;
                    }
                  }
                }
              }
            }
            foreach ($perms as $permValue) {
              if (!$permValue) {
                return TESTRESULT_UNKNOWN;
              }
            }
            return TESTRESULT_OK;
          }
          return TESTRESULT_UNKNOWN;
        default :
          return TESTRESULT_UNKNOWN;
        }
      }
    } catch (PapayaDatabaseException $e) {
    }
    return TESTRESULT_FAILED;
  }

  /**
   * Check for unicode extensions
   *
   * @return int
   */
  public function testUnicodeSupport() {
    if (extension_loaded('intl') && class_exists('Transliterator', FALSE)) {
      return TESTRESULT_OK;
    }
    if (extension_loaded('mb_string')) {
      return TESTRESULT_OK;
    }
    return TESTRESULT_FAILED;
  }

  /**
  * use a simple pattern to check for unicode property support in pcre
  *
  * @access private
  * @return integer
  */
  function testPcreUnicode() {
    if (preg_match('(^\p{Nd}$)u', '1')) {
      return TESTRESULT_OK;
    }
    return TESTRESULT_FAILED;
  }

  /**
  * xml extension loaded?
  *
  * @access private
  * @return integer
  */
  function testXML() {
    if (extension_loaded('xml')) {
      return TESTRESULT_OK;
    }
    return TESTRESULT_FAILED;
  }

  /**
  * is an xslt processor available?
  *
  * @access private
  * @return integer
  */
  function testXSLT() {
    if (extension_loaded('xsl')) {
      return TESTRESULT_OK;
    }
    return TESTRESULT_FAILED;
  }

  /**
  * is eXSLT support availiable?
  *
  * @access private
  * @return integer
  */
  function testExslt() {
    if (extension_loaded('xsl')) {
      $xsl = new XSLTProcessor;
      if ($xsl->hasExsltSupport()) {
        return TESTRESULT_OK;
      }
    }
    return TESTRESULT_FAILED;
  }

  /**
  * does the apache has mod_rewrite?
  *
  * @access private
  * @return integer
  */
  function testModRewrite() {
    if (function_exists('apache_get_modules')) {
      $extensions = apache_get_modules();
      if (in_array('mod_rewrite', $extensions)) {
        return TESTRESULT_OK;
      } else {
        return TESTRESULT_FAILED;
      }
    }
    return TESTRESULT_UNKNOWN;
  }

  /**
  * gd loaded?
  *
  * @access private
  * @return integer
  */
  function testGD() {
    if (extension_loaded('gd')) {
      return TESTRESULT_OK;
    }
    return TESTRESULT_FAILED;
  }

  /**
  * can php read gif?
  *
  * @access private
  * @return integer
  */
  function testGDGIFRead() {
    $result = FALSE;
    if (function_exists('gd_info')) {
      $gdInfo = gd_info();
      $result = $gdInfo['GIF Read Support'];
    } elseif (function_exists('imagetypes')) {
      $imageTypes = imagetypes();
      $result = (bool)($imageTypes & IMG_GIF);
    }
    if ($result) {
      return TESTRESULT_OK;
    }
    return TESTRESULT_FAILED;
  }

  /**
  * can php write gif?
  *
  * @access private
  * @return integer
  */
  function testGDGIFWrite() {
    $result = FALSE;
    if (function_exists('gd_info')) {
      $gdInfo = gd_info();
      $result = $gdInfo['GIF Create Support'];
    } elseif (function_exists('imagetypes')) {
      $imageTypes = imagetypes();
      $result = (bool)($imageTypes & IMG_GIF);
    }
    if ($result) {
      return TESTRESULT_OK;
    }
    return TESTRESULT_OPTIONAL;
  }

  /**
  * can php read/write jpeg?
  *
  * @access private
  * @return integer
  */
  function testGDJPEG() {
    $result = FALSE;
    if (function_exists('gd_info')) {
      $gdInfo = gd_info();
      if (isset($gdInfo['JPG Support'])) {
        $result = $gdInfo['JPG Support'];
      } elseif (isset($gdInfo['JPEG Support'])) {
        $result = $gdInfo['JPEG Support'];
      } else {
        $result = FALSE;
      }
    } elseif (function_exists('imagetypes')) {
      $imageTypes = imagetypes();
      $result = (bool)($imageTypes & IMG_JPG);
    }
    if ($result) {
      return TESTRESULT_OK;
    }
    return TESTRESULT_FAILED;
  }

  /**
  * can php read/write png?
  *
  * @access private
  * @return integer
  */
  function testGDPNG() {
    $result = FALSE;
    if (function_exists('gd_info')) {
      $gdInfo = gd_info();
      $result = $gdInfo['PNG Support'];
    } elseif (function_exists('imagetypes')) {
      $imageTypes = imagetypes();
      $result = (bool)($imageTypes & IMG_PNG);
    }
    if ($result) {
      return TESTRESULT_OK;
    }
    return TESTRESULT_FAILED;
  }

  /**
  * Check if crypt() is available
  *
  * @access private
  * @return integer
  */
  function testCrypt() {
    if (function_exists('crypt')) {
      return TESTRESULT_OK;
    }
    return TESTRESULT_FAILED;
  }

  /**
  * check if the xhprof extension is loaded
  *
  * @return integer
  */
  function testXhprof() {
    if (extension_loaded('xhprof')) {
      return TESTRESULT_OK;
    }
    return TESTRESULT_OPTIONAL;
  }

  /** check if it possible to increase the memory limit for image scaling
  *
  * @return integer
  */
  function testMemoryLimitIncrease() {
    if (is_callable('ini_get') && is_callable('ini_set')) {
      $limit = PapayaUtilBytes::fromString(@ini_get('memory_limit'));
      if ($limit > 0) {
        $limit += 1024;
        ini_set('memory_limit', $limit);
        if ($limit == PapayaUtilBytes::fromString(@ini_get('memory_limit'))) {
          return TESTRESULT_OK;
        }
      }
    }
    return TESTRESULT_OPTIONAL;
  }

  /**
  * show test results and informations
  *
  * @access public
  */
  function getXMLLists() {
    $result = '<listview title="Tests" width="100%">'.LF;
    $result .= '<items>';
    foreach ($this->resultTests as $title => $testResult) {
      $test = $this->tests[$title];
      if (is_array($test) && isset($test[2])) {
        $indent = (int)$test[2];
      } else {
        $indent = 0;
      }
      $result .= sprintf(
        '<listitem title="%s" indent="%d" subtitle="%s">'.LF,
        papaya_strings::escapeHTMLChars($title),
        $indent,
        !empty($this->resultTestMessages[$title])
          ? papaya_strings::escapeHTMLChars($this->resultTestMessages[$title])
          : ''
      );
      list($resultStr, $imgIndex) = $this->getResultOuput($testResult);
      $result .= sprintf(
        '<subitem><glyph src="%s" hint="%s"/></subitem>'.LF,
        papaya_strings::escapeHTMLChars($this->images[$imgIndex]),
        papaya_strings::escapeHTMLChars($resultStr)
      );
      $result .= sprintf(
        '<subitem>%s</subitem>'.LF,
        papaya_strings::escapeHTMLChars($resultStr)
      );
      if (is_array($test) && isset($test[1])) {
        $result .= sprintf(
          '<subitem align="right"><a href="%s" target="_blank">[...more information]'.
          '</a></subitem>'.LF,
          papaya_strings::escapeHTMLChars($test[1])
        );
      } else {
        $result .= '<subitem></subitem>'.LF;
      }
      $result .= '</listitem>'.LF;
    }
    $result .= '</items>'.LF;
    $result .= '</listview>'.LF;
    $result .= '<listview title="Information" width="100%">'.LF;
    $result .= '<items>'.LF;
    foreach ($this->resultInfos as $title => $info) {
      $result .= sprintf(
        '<listitem title="%s">'.LF,
        papaya_strings::escapeHTMLChars($title)
      );
      $result .= '<subitem>'.papaya_strings::escapeHTMLChars($info).'</subitem>'.LF;
      $result .= '</listitem>'.LF;
    }
    $result .= '</items>'.LF;
    $result .= '</listview>'.LF;
    return $result;
  }

  /**
  * Get icon and text for result output
  * @param string $testResult
  * @return array
  */
  function getResultOuput($testResult) {
    switch ($testResult) {
    case TESTRESULT_OK :
      $resultStr = 'OK';
      $imgIndex = 'status-sign-ok';
      break;
    case TESTRESULT_FAILED :
      $resultStr = 'FAILED';
      $imgIndex = 'status-sign-problem';
      break;
    case TESTRESULT_UNKNOWN :
      $resultStr = 'UNKNOWN';
      $imgIndex = 'status-sign-info';
      break;
    case TESTRESULT_OPTIONAL :
      $resultStr = 'OPTIONAL';
      $imgIndex = 'status-sign-warning';
      break;
    default :
    case TESTRESULT_NOT_IMPLEMENTED :
      $resultStr = 'NOT IMPLEMENTED';
      $imgIndex = 'status-sign-problem';
      break;
    }
    return array($resultStr, $imgIndex);
  }
}

