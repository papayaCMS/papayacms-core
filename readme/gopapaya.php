<?php
/**
* Test your server
*/


/**
* Test result - success
*/
define('TESTRESULT_OK', 1);
/**
* Test result - failed
*/
define('TESTRESULT_FAILED', 2);
/**
* Test result - unknown (can not test this feature)
*/
define('TESTRESULT_UNKNOWN', 3);
/**
* Test result - optional (test failed but feature is optional)
*/
define('TESTRESULT_OPTIONAL', 4);
/**
* Test result - test not implemented
*/
define('TESTRESULT_NOT_IMPLEMENTED', 0);

class papaya_testsystem {

  /**
  * informations about the server - functions name list
  * @var array $informationen
  */
  var $information = array(
    'Test File' => 'infoTestFileName',
    // running on *nix variant(inc. Mac X?) or Windows?
    'Operating System' => 'infoOperatingSystem',
    // version of php supported.
    'PHP Version' => 'infoPHPVersion',
    // find out where server docs are
    'Document Root' => 'infoDocumentRoot',
    // Either - Mysql database
    'MySQL Client' => 'infoMySQLVersion',
    // or - Mysql database / advanced interface
    'MySQLi Client' => 'infoMySQLiVersion',
    // or - SQLlite database
    'SQlite Library' => 'infoSQLiteVersion',
    // Graphic manipulation library usually installed as part of php
    'GD Version' => 'infoGDVersion',
    // XSLT library version
    'libXSLT Version' => 'infoLibXSLTVersion',
  );

  /**
  * tests - functions name list
  * @var array $tests
  */
  var $tests = array(
    // PHP versions
    'PHP version' => 'testPHPVersion',
    // mandatory webserver extension : support rewrite rules
    'mod_rewrite (Apache extension)' => 'testModRewrite',
    // exercise the db with this
    'Database Extension' => 'testDatabase',
    // exercise XML parsing with this
    'XML Extension' => 'testXML',
    // exercise XSLT transformation with this
    'XSLT Extension' => 'testXSLT',
    // exercise the Graphic library
    'GD Extension' => 'testGD',
    // check graphic library is post 2004 - gif support enabled
    'GD GIF read' => 'testGDGIFRead',
    'GD GIF write' => 'testGDGIFWrite',
    // check graphic library supports jpeg format.
    'GD JPEG read/write' => 'testGDJPEG',
    'GD PNG read/write' => 'testGDPNG',
  );

  /**
  * information data storage
  * @var array
  */
  var $resultInfos = array();
  /**
  * test results
  * @var array
  */
  var $resultTests = array();

  /**
  * extract information and run tests on server
  * environment.
  * @access public
  */
  function execute() {
    foreach ($this->information as $title => $method) {
      $this->resultInfos[$title] = $this->getInformation($method);
    }
    foreach ($this->tests as $title => $method) {
      $this->resultTests[$title] = $this->runTest($method);
    }
  }

  /**
  * Check if certain PHP class methods exist, then
  * use them to obtain information about the php environment
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
  * Check if certain PHP class methods exist, then
  * use them to test if conditions for papaya are fulfilled
  * @return integer
  */
  function runTest($method) {
    if (method_exists($this, $method)) {
      return $this->$method();
    } else {
      return TESTRESULT_NOT_IMPLEMENTED;
    }
  }

  /**
  * Get name of this file
  * @return string
  */
  function infoTestFileName() {
    return __FILE__;
  }

  /**
  * returns the predefined version constant
  * for php
  *
  * @access private
  * @return string
  */
  function infoPHPVersion() {
    return PHP_VERSION;
  }

  /**
  * returns the predefined constant
  * the operating system detected
  * by PHP
  *
  * @access private
  * @return string
  */
  function infoOperatingSystem() {
    return PHP_OS;
  }

  /**
  * checks validity of php superglobal $_SERVER
  * and then returns the path to the web server document root
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
  * check that the php interface to mysql is installed
  * then use it to get info about the database.
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
  * check that the advanced php interface to mysql is installed
  * then use it to get info about the database.
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
  * check that the php interface to SQLite is installed
  * then use it to get info about the database.
  * @access private
  * @return string
  */
  function infoSQLiteVersion() {
    if (extension_loaded('sqlite3')) {
      $result = sqlite3::version();
      return $result['versionString'];
    }
    return 'None';
  }

  /**
  * check that the php interface to the GD is installed
  * then use it to get info about the graphic formats supported.
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
  * check that the php interface to the domxml parser is installed
  * then return the version.
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
  * check to see if php libraries for each database are loaded.
  * This doesn't necessarily mean that the database is there and working
  * just that php has been configured with the correct libraries.
  *
  * @access private
  * @return integer
  */
  function testDatabase() {
    $extensions = array('mysql', 'mysqli', 'pgsql', 'sqlite3');
    foreach ($extensions as $ext) {
      if (extension_loaded($ext)) {
        return TESTRESULT_OK;
      }
    }
    return TESTRESULT_FAILED;
  }

  /**
  * test if the xml php extension is loaded.
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
  * test if a XSL php library is loaded
  * @access private
  * @return integer
  */
  function testXSLT() {
    if (extension_loaded('xslt')) {
      return TESTRESULT_OK;
    }
    if (extension_loaded('domxml') && function_exists('domxml_xslt_stylesheet_file')) {
      return TESTRESULT_OK;
    }
    if (extension_loaded('xsl')) {
      return TESTRESULT_OK;
    }
    return TESTRESULT_FAILED;
  }

  /**
  * check if mod rewrite module is installed
  * on apache web server
  * this depends on the apache_get_modules function of php
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
  * check which php version is installed
  *
  * @access private
  * @return integer
  */
  function testPHPVersion() {
    if (PHP_VERSION_ID >= 70000) {
    return TESTRESULT_OK;
    }
    if (PHP_VERSION_ID >= 50600) {
    return TESTRESULT_OPTIONAL;
    }
    return TESTRESULT_FAILED;
  }

  /**
  * check if the GD module is loaded
   *
   */
  function testGD() {
    if (extension_loaded('gd')) {
      return TESTRESULT_OK;
    }
    return TESTRESULT_FAILED;
  }


  /**
  * Check if version of GD supports reading GIF format.
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
  * private method testGDGIFRead
  * Check if version of GD supports writing GIF format.
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
  * Check if version of GD supports writing JPEG format.
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
  * Check if version of GD supports writing PNG graphic format.
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
}

$test = new papaya_testsystem();
$test->execute();

?>
<html>
<head>
  <title>papaya CMS - System Test</title>
  <style type="text/css">
    body {
      font-size: 0.8em;
      font-family: sans-serif;
      background-color: white;
      color: black;
    }
    h1 {
      font-size: 1.3em;
    }
    h2 {
      font-size: 1.2em;
    }
    table {
      font-size: 1em;
      border: 1px solid black;
      border-collapse: collapse;
      width: 50em;
    }
    table th, table td {
      text-align: left;
      border: 1px solid black;
      padding: 2px;
    }

    table.testResults td {
      text-align: center;
      font-weight: bold;
    }
    .testOK {
      color: green;
    }
    .testFAILED {
      color: red;
    }
    .testNOTIMPLEMENTED {
      color: red;
    }
    .testUNKNOWN {
      color: blue;
    }
    .testOPTIONAL {
      color: black;
    }

  </style>
</head>
<body>
  <h1>papaya CMS - System Test</h1>
  <h2>Tests</h2>
  <table class="legend">
    <tr>
      <th class="testOK">OK</th>
      <td>Test passed</td>
    </tr>
    <tr>
      <th class="testFAILED">FAILED</th>
      <td>Test failed - Here is a problem</td>
    </tr>
    <tr>
      <th class="testUNKNOWN">UNKNOWN</th>
      <td>Test failed - Please check manually</td>
    </tr>
    <tr>
      <th class="testOPTIONAL">OPTIONAL</th>
      <td>Test failed - But this feature is optional</td>
    </tr>
  </table>
  <br />
  <table class="testResults">
    <?php
foreach ($test->resultTests as $title => $testResult) {
  echo '<tr>';
  echo '<th>'.htmlspecialchars($title).'</th>';
  switch ($testResult) {
  case TESTRESULT_OK :
    echo '<td class="testOK">OK</td>';
    break;
  case TESTRESULT_FAILED :
    echo '<td class="testFAILED">FAILED</td>';
    break;
  case TESTRESULT_UNKNOWN :
    echo '<td class="testUNKNOWN">UNKNOWN</td>';
    break;
  case TESTRESULT_OPTIONAL :
    echo '<td class="testOPTIONAL">OPTIONAL</td>';
    break;
  default :
  case TESTRESULT_NOT_IMPLEMENTED :
    echo '<td class="testNOTIMPLEMENTED">NOT IMPLEMENTED</td>';
    break;
  }
  echo '</tr>';
}
    ?>
  </table>
  <h2>Information</h2>
  <table class="infos">
    <?php
foreach ($test->resultInfos as $title => $info) {
  echo '<tr>';
  echo '<th>'.htmlspecialchars($title).'</th>';
  echo '<td>'.htmlspecialchars($info).'</td>';
  echo '</tr>';
}
    ?>
  </table>
</body>
</html>
