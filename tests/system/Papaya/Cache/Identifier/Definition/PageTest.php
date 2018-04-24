<?php
require_once __DIR__.'/../../../../../bootstrap.php';

class PapayaCacheIdentifierDefinitionPageTest extends PapayaTestCase {

  /**
   * @covers PapayaCacheIdentifierDefinitionPage
   * @dataProvider provideParameterData
   */
  public function testGetStatus($expected, $parameters) {
    $definition = new PapayaCacheIdentifierDefinitionPage();
    $definition->papaya(
      $this->mockPapaya()->application(
        array(
          'request' => $this->mockPapaya()->request($parameters)
        )
      )
    );
    $this->assertEquals($expected, $definition->getStatus());
  }

  /**
   * @covers PapayaCacheIdentifierDefinitionPage
   */
  public function testGetStatusForPreviewExpectingFalse() {
    $definition = new PapayaCacheIdentifierDefinitionPage();
    $definition->papaya(
      $this->mockPapaya()->application(
        array(
          'request' => $this->mockPapaya()->request(array('preview' => TRUE))
        )
      )
    );
    $this->assertFalse($definition->getStatus());
  }

  /**
   * @covers PapayaCacheIdentifierDefinitionPage
   */
  public function testGetStatusWithDefinedHttpEnvironment() {
    $environment = $_SERVER;
    $_SERVER = array(
      'HTTPS' => 'on',
      'HTTP_HOST' => 'www.sample.tld',
      'SERVER_PORT' => 443
    );
    $definition = new PapayaCacheIdentifierDefinitionPage();
    $definition->papaya($this->mockPapaya()->application());
    $this->assertEquals(
      array(
        PapayaCacheIdentifierDefinitionPage::class => array(
          'scheme' => 'https',
          'host' => 'www.sample.tld',
          'port' => 443,
          'category_id' => 0,
          'page_id' => 0,
          'language' => '',
          'output_mode' => 'html'
        )
      ),
      $definition->getStatus()
     );
    $_SERVER = $environment;
  }

  /**
   * @covers PapayaCacheIdentifierDefinitionPage
   */
  public function testGetSources() {
    $definition = new PapayaCacheIdentifierDefinitionPage();
    $this->assertEquals(
      PapayaCacheIdentifierDefinition::SOURCE_URL,
      $definition->getSources()
    );
  }

  public static function provideParameterData() {
    return array(
      array(
        array(
          PapayaCacheIdentifierDefinitionPage::class => array(
            'scheme' => 'http',
            'host' => '',
            'port' => 80,
            'category_id' => 0,
            'page_id' => 0,
            'language' => '',
            'output_mode' => 'html'
          )
        ),
        array()
      ),
      array(
        array(
          PapayaCacheIdentifierDefinitionPage::class => array(
            'scheme' => 'http',
            'host' => '',
            'port' => 80,
            'category_id' => 0,
            'page_id' => 42,
            'language' => '',
            'output_mode' => 'html'
          )
        ),
        array(
          'page_id' => 42
        )
      ),
      array(
        array(
          PapayaCacheIdentifierDefinitionPage::class => array(
            'scheme' => 'http',
            'host' => '',
            'port' => 80,
            'page_id' => 0,
            'category_id' => 21,
            'language' => '',
            'output_mode' => 'html'
          )
        ),
        array(
          'category_id' => 21
        )
      ),
      array(
        array(
          PapayaCacheIdentifierDefinitionPage::class => array(
            'scheme' => 'http',
            'host' => '',
            'port' => 80,
            'page_id' => 42,
            'category_id' => 21,
            'language' => 'de',
            'output_mode' => 'xml'
          )
        ),
        array(
          'category_id' => 21,
          'page_id' => 42,
          'language' => 'de',
          'output_mode' => 'xml'
        )
      ),
      array(
        array(
          PapayaCacheIdentifierDefinitionPage::class => array(
            'scheme' => 'http',
            'host' => '',
            'port' => 80,
            'category_id' => 0,
            'page_id' => 42,
            'language' => '',
            'output_mode' => 'html'
          )
        ),
        array(
          'page_id' => 42,
          'foo' => 'bar'
        )
      ),
    );
  }
}
