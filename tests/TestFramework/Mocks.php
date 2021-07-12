<?php
/*
 * papaya CMS
 *
 * @copyright 2000-2021 by papayaCMS project - All rights reserved.
 * @link http://www.papaya-cms.com/
 * @license http://www.gnu.org/licenses/old-licenses/gpl-2.0.html GNU General Public License, version 2
 *
 *  You can redistribute and/or modify this script under the terms of the GNU General Public
 *  License (GPL) version 2, provided that the copyright and license notes, including these
 *  lines, remain unmodified. papaya is distributed in the hope that it will be useful, but
 *  WITHOUT ANY WARRANTY; without even the implied warranty of MERCHANTABILITY or FITNESS
 *  FOR A PARTICULAR PURPOSE.
 */

namespace Papaya\TestFramework {

  TestCase::defineConstantDefaults(
    'PAPAYA_DB_TBL_AUTHOPTIONS',
    'PAPAYA_DB_TBL_AUTHUSER',
    'PAPAYA_DB_TBL_AUTHGROUPS',
    'PAPAYA_DB_TBL_AUTHLINK',
    'PAPAYA_DB_TBL_AUTHPERM',
    'PAPAYA_DB_TBL_AUTHMODPERMS',
    'PAPAYA_DB_TBL_AUTHMODPERMLINKS',
    'PAPAYA_DB_TBL_SURFER'
  );


  class Mocks {

    /**
     * @var TestCase
     */
    private $_testCase;

    public function __construct(TestCase $testCase) {
      $this->_testCase = $testCase;
    }


    /*********************
     * $papaya
     ********************/

    /**
     * @param array $objects
     * @return \PHPUnit_Framework_MockObject_MockObject|\PapayaApplication|\Papaya\Application
     */
    public function application(array $objects = []) {
      $testCase = $this->_testCase;
      $values = [];
      foreach ($objects as $identifier => $object) {
        $name = strToLower($identifier);
        $values[$name] = $object;
      }
      if (empty($values['options'])) {
        $values['options'] = $this->options();
      }
      if (empty($values['request'])) {
        $values['request'] = $this->request();
      }
      if (empty($values['references'])) {
        $values['references'] = $this->references();
      }
      $testCase->{'context_application_objects'.spl_object_hash($this)} = $values;

      $application = $testCase->getMockBuilder(\Papaya\Application::class)->getMock();
      $application
        ->expects($testCase->any())
        ->method('__isset')
        ->will($testCase->returnCallback([$this, 'callbackApplicationHasObject']));
      $application
        ->expects($testCase->any())
        ->method('__get')
        ->will($testCase->returnCallback([$this, 'callbackApplicationGetObject']));
      $application
        ->expects($testCase->any())
        ->method('__call')
        ->will($testCase->returnCallback([$this, 'callbackApplicationGetObject']));
      $application
        ->expects($testCase->any())
        ->method('hasObject')
        ->will($testCase->returnCallback([$this, 'callbackApplicationHasObject']));
      $application
        ->expects($testCase->any())
        ->method('getObject')
        ->will($testCase->returnCallback([$this, 'callbackApplicationGetObject']));
      return $application;
    }

    public function callbackApplicationHasObject($name, $className = '') {
      $testCase = $this->_testCase;
      $values = $testCase->{'context_application_objects'.spl_object_hash($this)};
      $name = strToLower($name);
      if (isset($values[$name])) {
        return TRUE;
      } else {
        return FALSE;
      }
    }

    /**
     * @param $name
     * @return NULL|object
     */
    public function callbackApplicationGetObject($name) {
      $testCase = $this->_testCase;
      $values = $testCase->{'context_application_objects'.spl_object_hash($this)};
      $name = strToLower($name);
      if (isset($values[$name])) {
        return $values[$name];
      }
      return NULL;
    }

    /*********************
     * $papaya->options
     ********************/

    /**
     * @param array $values
     * @param array $tables
     * @return \PHPUnit_Framework_MockObject_MockObject|\PapayaConfigurationCms|\Papaya\CMS\CMSConfiguration
     */
    public function options(array $values = [], array $tables = []) {
      $testCase = $this->_testCase;
      $testCase->{'context_options_'.spl_object_hash($this)} = $values;
      $testCase->{'context_tables_'.spl_object_hash($this)} = $tables;

      $options = $testCase
        ->getMockBuilder(\Papaya\Configuration::class)
        ->disableOriginalConstructor()
        ->getMock();
      $options
        ->expects($testCase->any())
        ->method('offsetGet')
        ->will($testCase->returnCallback([$this, 'callbackOptionsGet']));
      $options
        ->expects($testCase->any())
        ->method('get')
        ->will($testCase->returnCallback([$this, 'callbackOptionsGet']));
      $options
        ->expects($testCase->any())
        ->method('getOption')
        ->will($testCase->returnCallback([$this, 'callbackOptionsGet']));
      $options
        ->expects($testCase->any())
        ->method('getPath')
        ->willReturnArgument(1);
      return $options;
    }

    public function callbackOptionsGet($name, $default = NULL) {
      $property = 'context_options_'.spl_object_hash($this);
      if (
        isset($this->_testCase->$property) &&
        is_array($this->_testCase->$property)
      ) {
        $values = $this->_testCase->$property;
        if (isset($values[$name])) {
          return $values[$name];
        }
      }
      return $default;
    }

    public function callbackOptionsGetTableName($name, $usePrefix = TRUE) {
      $property = 'context_options_tables_'.spl_object_hash($this);
      $values = $this->_testCase->$property;
      if ($usePrefix && isset($values['papaya_'.$name])) {
        return $values['papaya_'.$name];
      } elseif (!$usePrefix && isset($values[$name])) {
        return $values[$name];
      } elseif ($usePrefix) {
        return 'papaya_'.$name;
      } else {
        return $name;
      }
    }


    /*********************
     * $papaya->request
     ********************/

    /**
     * @param array $parameters
     * @param string $url
     * @param string $separator
     * @return \PHPUnit_Framework_MockObject_MockObject|\PapayaRequest|\Papaya\Request
     */
    public function request(
      array $parameters = [], $url = 'http://www.test.tld/test.html', $separator = '[]'
    ) {
      $testCase = $this->_testCase;
      $property = 'context_request_parameters_'.spl_object_hash($this);

      $testCase->$property = new \Papaya\Request\Parameters();
      $testCase->$property->merge($parameters);
      $request = $testCase->getMock(\Papaya\Request::class);
      $request
        ->expects($testCase->any())
        ->method('getUrl')
        ->will($testCase->returnValue(new \PapayaUrl($url)));
      $request
        ->expects($testCase->any())
        ->method('getParameterGroupSeparator')
        ->will($testCase->returnValue($separator));
      $request
        ->expects($testCase->any())
        ->method('getBasePath')
        ->will($testCase->returnValue('/'));
      $request
        ->expects($testCase->any())
        ->method('setParameterGroupSeparator')
        ->will($testCase->returnValue($request));
      $request
        ->expects($testCase->any())
        ->method('getParameter')
        ->will($testCase->returnCallback([$this, 'callbackRequestGetParameter']));
      $request
        ->expects($testCase->any())
        ->method('getParameters')
        ->will($testCase->returnCallback([$this, 'callbackRequestGetParameters']));
      $request
        ->expects($testCase->any())
        ->method('getParameterGroup')
        ->will($testCase->returnCallback([$this, 'callbackRequestGetParameterGroup']));
      $request
        ->expects($testCase->any())
        ->method('getMethod')
        ->will($testCase->returnValue('get'));
      return $request;
    }

    public function callbackRequestGetParameter($name, $default = '') {
      $property = 'context_request_parameters_'.spl_object_hash($this);
      return $this->_testCase->$property->get($name, $default);
    }

    public function callbackRequestGetParameters() {
      $property = 'context_request_parameters_'.spl_object_hash($this);
      return $this->_testCase->$property;
    }

    public function callbackRequestGetParameterGroup($name) {
      $property = 'context_request_parameters_'.spl_object_hash($this);
      return $this->_testCase->$property->getGroup($name);
    }

    /*********************
     * $papaya->response
     ********************/

    /**
     * @return \PHPUnit_Framework_MockObject_MockObject|\PapayaResponse|\Papaya\Response
     */
    public function response() {
      return $this->_testCase->getMockBuilder(\Papaya\Response::class)->getMock();
    }

    /*****************************
     * $papaya->administrationUser
     ****************************/

    /**
     * @param $isLoggedIn
     * @return \PHPUnit_Framework_MockObject_MockObject|base_auth
     */
    public function user($isLoggedIn) {
      $user = $this->_testCase->getMockBuilder(\base_auth::class)->getMock();
      $user
        ->expects($this->_testCase->any())
        ->method('isLoggedIn')
        ->will($this->_testCase->returnValue($isLoggedIn));
      return $user;
    }

    /*****************************
     * $papaya->administrationLanguage
     ****************************/

    /**
     * @param \PapayaContentLanguage|\Papaya\CMS\Content\Language|null $language
     * @return \PHPUnit_Framework_MockObject_MockObject|\PapayaAdministrationLanguagesSwitch|\Papaya\CMS\Administration\Languages\Selector
     */
    public function administrationLanguage(\PapayaContentLanguage $language = NULL) {
      if (!isset($language)) {
        $language = new \PapayaContentLanguage();
        $language->assign(
          [
            'id' => '1',
            'identifier' => 'en',
            'code' => 'en-US',
            'title' => 'English',
            'image' => 'en.png',
            'is_interface' => TRUE,
            'is_content' => TRUE
          ]
        );
      }
      $switch = $this->_testCase->getMockBuilder(
        \Papaya\CMS\Administration\Languages\Selector::class
      )->getMock();
      $switch
        ->expects($this->_testCase->any())
        ->method('getCurrent')
        ->will($this->_testCase->returnValue($language));
      return $switch;
    }

    /**
     * Create a mock of PapayaDatabaseAccess usable for normal data operations (not schema manipulation)
     *
     * @return \PHPUnit_Framework_MockObject_MockObject|\PapayaDatabaseAccess|\Papaya\Database\Access
     */
    public function databaseAccess() {
      $methods = [
        'getTableName',
        'getTimestamp',
        'deleteRecord',
        'enableAbsoluteCount',
        'escapeString',
        'quoteString',
        'getSqlCondition',
        'getSqlSource',
        'insertRecord',
        'insertRecords',
        'lastInsertId',
        'query',
        'queryFmt',
        'queryFmtWrite',
        'queryWrite',
        'loadRecord',
        'updateRecord'
      ];
      $databaseAccess = $this
        ->_testCase
        ->getMockBuilder(\Papaya\Database\Access::class)
        ->setMethods($methods)
        ->setConstructorArgs([new \stdClass()])
        ->getMock();
      $databaseAccess
        ->expects($this->_testCase->any())
        ->method('getTableName')
        ->withAnyParameters()
        ->willReturnCallback(
          function ($tableName, $usePrefix = TRUE) {
            return ($usePrefix && 0 !== strpos($tableName, 'table_') ? 'table_' : '').$tableName;
          }
        );
      return $databaseAccess;
    }

    /*********************
     * PapayaDatabaseRecord
     ********************/

    /**
     * @param array $data
     * @param string $className
     * @return \PHPUnit_Framework_MockObject_MockObject|\PapayaDatabaseInterfaceRecord|\Papaya\Database\Interfaces\Record
     */
    public function record(
      array $data = [],
      $className = \Papaya\Database\Interfaces\Record::class
    ) {
      $valueMapExists = [];
      $valueMapIsset = [];
      $valueMapGet = [];
      foreach ($data as $name => $value) {
        $lowerCase = \Papaya\Utility\Text\Identifier::toUnderscoreLower($name);
        $upperCase = \Papaya\Utility\Text\Identifier::toUnderscoreUpper($name);
        $camelCase = \Papaya\Utility\Text\Identifier::toCamelCase($name);
        $valueMapExists[] = [$lowerCase, TRUE];
        $valueMapIsset[] = [$lowerCase, isset($value)];
        $valueMapGet[] = [$lowerCase, $value];
        $valueMapExists[] = [$upperCase, TRUE];
        $valueMapIsset[] = [$upperCase, isset($value)];
        $valueMapGet[] = [$upperCase, $value];
        if ($lowerCase != $camelCase) {
          $valueMapExists[] = [$camelCase, TRUE];
          $valueMapIsset[] = [$camelCase, isset($value)];
          $valueMapGet[] = [$camelCase, $value];
        }
      }
      $record = $this->_testCase->getMock($className);
      $record
        ->expects($this->_testCase->any())
        ->method('offsetExists')
        ->will($this->_testCase->returnValueMap($valueMapExists));
      $record
        ->expects($this->_testCase->any())
        ->method('__isset')
        ->will($this->_testCase->returnValueMap($valueMapIsset));
      $record
        ->expects($this->_testCase->any())
        ->method('offsetGet')
        ->will($this->_testCase->returnValueMap($valueMapGet));
      $record
        ->expects($this->_testCase->any())
        ->method('__get')
        ->will($this->_testCase->returnValueMap($valueMapGet));
      return $record;
    }

    /*********************
     * PapayaUiReference
     ********************/

    /**
     * @param string $url
     * @param null|PapayaUiReference|string|\Papaya\UI\Reference $reference or reference class name
     * @return null|\PHPUnit_Framework_MockObject_MockObject|\PapayaUiReference|\Papaya\UI\Reference
     */
    public function reference($url = 'http://www.example.html', $reference = NULL) {
      if (!isset($reference)) {
        $reference = $this->_testCase->getMock(\Papaya\UI\Reference::class);
      } elseif (is_string($reference)) {
        $reference = $this->_testCase->getMock($reference);
      }
      $reference
        ->expects($this->_testCase->any())
        ->method('__toString')
        ->will($this->_testCase->returnValue($url));
      $reference
        ->expects($this->_testCase->any())
        ->method('get')
        ->will($this->_testCase->returnValue($url));
      $reference
        ->expects($this->_testCase->any())
        ->method('getRelative')
        ->will($this->_testCase->returnValue($url));
      return $reference;
    }

    /**************************
     * PapayaUiReferenceFactory
     *************************/

    /**
     * @param array $links
     * @return \PHPUnit_Framework_MockObject_MockObject|\PapayaUiReferenceFactory|\Papaya\CMS\Reference\Factory
     */
    public function references(array $links = []) {
      $this->_testCase->{'context_references_factory_mapping'.spl_object_hash($this)} = $links;
      $references = $this->_testCase->getMock(\Papaya\CMS\Reference\Factory::class);
      $references
        ->expects($this->_testCase->any())
        ->method('byString')
        ->will(
          $this->_testCase->returnCallback([$this, 'callbackGetReferenceForString'])
        );
      return $references;
    }

    public function callbackGetReferenceForString($index) {
      $links = $this->_testCase->{'context_references_factory_mapping'.spl_object_hash($this)};
      if (isset($links[$index])) {
        return $this->reference($links[$index]);
      } else {
        return $this->reference('link:'.$index);
      }
    }

    /**
     * Administration UI with Template object
     * @return \PHPUnit_Framework_MockObject_MockObject|\Papaya\CMS\Administration\UI
     */
    public function administrationUI() {
      $template = $this
        ->_testCase
        ->getMockBuilder(\Papaya\Template::class)
        ->setMethods(
          ['add', 'addNavigation', 'addInformation', 'addContent', 'addMenu', 'addScript', 'parse']
        )
        ->disableOriginalConstructor()
        ->getMock();
      $ui = $this->_testCase->getMockBuilder(
        \Papaya\CMS\Administration\UI::class
      )->disableOriginalConstructor()
        ->getMock();
      $ui
        ->expects($this->_testCase->any())
        ->method('template')
        ->willReturn($template);
      return $ui;
    }
  }
}
