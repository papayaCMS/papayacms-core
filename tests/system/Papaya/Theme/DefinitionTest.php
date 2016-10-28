<?php
require_once(dirname(__FILE__).'/../../../bootstrap.php');

class PapayaThemeDefinitionTest extends PapayaTestCase {

  /**
   * @covers PapayaThemeDefinition::load
   */
  public function testLoad() {
    $pages = $this->getMock('PapayaContentStructurePages');
    $pages
      ->expects($this->once())
      ->method('load')
      ->with($this->isInstanceOf('PapayaXmlElement'));

    $definition = new PapayaThemeDefinition();
    $definition->pages($pages);
    $definition->load(__DIR__.'/TestData/theme.xml');
    $this->assertAttributeEquals(
      array(
        'name' => 'TestData',
        'title' => 'Sample Papaya Theme',
        'version' => '1.0',
        'version_date' => '2012-07-23',
        'author' => 'papaya Software GmbH',
        'description' => 'Sample description',
        'template_path' => 'template-path'
      ),
      '_properties',
      $definition
    );
    $this->assertAttributeEquals(
      array(
        'medium' => 'default48.jpg',
        'large' => 'default100.jpg'
      ),
      '_thumbnails',
      $definition
    );
  }

  /**
   * @covers PapayaThemeDefinition::__get
   */
  public function testMagicMethodGet() {
    $definition = new PapayaThemeDefinition();
    $definition->load(__DIR__.'/TestData/theme.xml');
    $this->assertEquals(
      'Sample Papaya Theme', $definition->title
    );
    $this->assertEquals(
      array(
        'medium' => 'default48.jpg',
        'large' => 'default100.jpg'
      ),
      $definition->thumbnails
    );
  }

  /**
   * @covers PapayaThemeDefinition::__get
   */
  public function testMagicMethodGetExpectingException() {
    $definition = new PapayaThemeDefinition();
    $this->setExpectedException('UnexpectedValueException');
    $definition->invalidProperty;
  }
}