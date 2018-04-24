<?php
require_once __DIR__.'/../../../../bootstrap.php';

class PapayaDatabaseSourceNameTest extends PapayaTestCase {

  /**
  * @covers PapayaDatabaseSourceName::__construct
  * @covers PapayaDatabaseSourceName::setName
  */
  public function testConstructorWithValidDsn() {
    $dsn = new PapayaDatabaseSourceName('mysql://server/sample');
    $this->assertAttributeEquals(
      'mysql://server/sample',
      '_name',
      $dsn
    );
    $this->assertAttributeEquals(
      array(
        'api' => 'mysql',
        'platform' => 'mysql',
        'filename' => NULL,
        'username' => NULL,
        'password' => NULL,
        'host' => 'server',
        'port' => 0,
        'socket' => NULL,
        'database' => 'sample'
      ),
      '_properties',
      $dsn
    );
  }

  /**
  * @covers PapayaDatabaseSourceName::__construct
  * @covers PapayaDatabaseSourceName::setName
  */
  public function testConstructorWithEmptyDsnExpectingException() {
    $this->expectException(PapayaDatabaseExceptionConnect::class);
    $dsn = new PapayaDatabaseSourceName('');
  }

  /**
  * @covers PapayaDatabaseSourceName::__construct
  * @covers PapayaDatabaseSourceName::setName
  */
  public function testConstructorWithInvalidDsnExpectingException() {
    $this->expectException(PapayaDatabaseExceptionConnect::class);
    $dsn = new PapayaDatabaseSourceName('xxx');
  }

  /**
  * @covers PapayaDatabaseSourceName::setName
  * @covers PapayaDatabaseSourceName::_getMatchValue
  * @dataProvider provideValidDatabaseSourceNames
  */
  public function testSetName($name, $expected) {
    $dsn = new PapayaDatabaseSourceName($name);
    $this->assertAttributeEquals(
      $expected, '_properties', $dsn
    );
  }

  /**
  * @covers PapayaDatabaseSourceName::__isset
  */
  public function testMagicMethodIssetExpectingTrue() {
    $dsn = new PapayaDatabaseSourceName('mysql://server/database');
    $this->assertTrue(isset($dsn->api));
  }

  /**
  * @covers PapayaDatabaseSourceName::__isset
  */
  public function testMagicMethodIssetExpectingFalse() {
    $dsn = new PapayaDatabaseSourceName('mysql://server/database');
    $this->assertFalse(isset($dsn->port));
  }

  /**
  * @covers PapayaDatabaseSourceName::__get
  * @dataProvider provideValidPropertyNames
  */
  public function testMagicMethodGet($property, $expected) {
    $dsn = new PapayaDatabaseSourceName('mysqli(mysql)://user:pass@server:42/database');
    $this->assertEquals(
      $expected, $dsn->$property
    );
  }

  /**
  * @covers PapayaDatabaseSourceName::__get
  */
  public function testMagicMethodGetWithInvalidPropertyExpectingException() {
    $this->expectException(ErrorException::class);
    $dsn = new PapayaDatabaseSourceName('mysqli(mysql)://user:pass@server:42/database');
    $foo = $dsn->INVALID_ARGUMENT_NAME;
  }

  /**
  * @covers PapayaDatabaseSourceName::__set
  */
  public function testMagicMethodSetExpectingException() {
    $this->expectException(BadMethodCallException::class);
    $dsn = new PapayaDatabaseSourceName('mysqli(mysql)://user:pass@server:42/database');
    $dsn->api = 'FOO';
  }

  /**
  * @covers PapayaDatabaseSourceName::__get
  * @covers PapayaDatabaseSourceName::setName
  */
  public function testParameters() {
    $dsn = new PapayaDatabaseSourceName('mysql://server/database?foo=bar');
    $this->assertEquals(
       'bar',
       $dsn->parameters->get('foo')
    );
  }

  /**************************
  * Data Provider
  **************************/

  public static function provideValidDatabaseSourceNames() {
    return array(
      array(
        'mysql:server/database',
        array(
          'api' => 'mysql',
          'platform' => 'mysql',
          'filename' => NULL,
          'username' => NULL,
          'password' => NULL,
          'host' => 'server',
          'port' => 0,
          'socket' => NULL,
          'database' => 'database'
        ),
      ),
      array(
        'mysql://user:pass@server/database',
        array(
          'api' => 'mysql',
          'platform' => 'mysql',
          'filename' => NULL,
          'username' => 'user',
          'password' => 'pass',
          'host' => 'server',
          'port' => 0,
          'socket' => NULL,
          'database' => 'database'
        ),
      ),
      array(
        'mysql://user:pass@unix(/path/to/socket)/database',
        array(
          'api' => 'mysql',
          'platform' => 'mysql',
          'filename' => NULL,
          'username' => 'user',
          'password' => 'pass',
          'host' => NULL,
          'port' => 0,
          'socket' => '/path/to/socket',
          'database' => 'database'
        ),
      ),
      array(
        'mysqli(mysql)://user:pass@server:42/database',
        array(
          'api' => 'mysqli',
          'platform' => 'mysql',
          'filename' => NULL,
          'username' => 'user',
          'password' => 'pass',
          'host' => 'server',
          'port' => 42,
          'socket' => NULL,
          'database' => 'database'
        )
      ),
      array(
        'pgsql://127.0.0.1:42/local_database',
        array(
          'api' => 'pgsql',
          'platform' => 'pgsql',
          'filename' => NULL,
          'username' => NULL,
          'password' => NULL,
          'host' => '127.0.0.1',
          'port' => 42,
          'socket' => NULL,
          'database' => 'local_database'
        )
      ),
      array(
        'sqlite://c:/path/to/file.sqlite',
        array(
          'api' => 'sqlite',
          'platform' => 'sqlite',
          'filename' => 'c:/path/to/file.sqlite',
          'username' => NULL,
          'password' => NULL,
          'host' => NULL,
          'port' => 0,
          'socket' => NULL,
          'database' => NULL
        )
      ),
      array(
        'sqlite3(sqlite):///path/to/file.sqlite',
        array(
          'api' => 'sqlite3',
          'platform' => 'sqlite',
          'filename' => '/path/to/file.sqlite',
          'username' => NULL,
          'password' => NULL,
          'host' => NULL,
          'port' => 0,
          'socket' => NULL,
          'database' => NULL
        )
      ),
      array(
        'sqlite3(sqlite):c:\\path\\to\\file.sqlite',
        array(
          'api' => 'sqlite3',
          'platform' => 'sqlite',
          'filename' => 'c:\\path\\to\\file.sqlite',
          'username' => NULL,
          'password' => NULL,
          'host' => NULL,
          'port' => 0,
          'socket' => NULL,
          'database' => NULL
        )
      ),
      array(
        'sqlite3:./file.sqlite',
        array(
          'api' => 'sqlite3',
          'platform' => 'sqlite3',
          'filename' => './file.sqlite',
          'username' => NULL,
          'password' => NULL,
          'host' => NULL,
          'port' => 0,
          'socket' => NULL,
          'database' => NULL
        )
      ),
      array(
        'sqlite3://./file.sqlite',
        array(
          'api' => 'sqlite3',
          'platform' => 'sqlite3',
          'filename' => './file.sqlite',
          'username' => NULL,
          'password' => NULL,
          'host' => NULL,
          'port' => 0,
          'socket' => NULL,
          'database' => NULL
        )
      )
    );
  }

  public static function provideValidPropertyNames() {
    return array(
      array('api', 'mysqli'),
      array('platform', 'mysql'),
      array('filename', NULL),
      array('username', 'user'),
      array('password', 'pass'),
      array('host', 'server'),
      array('port', 42),
      array('socket', NULL),
      array('database', 'database')
    );
  }
}
