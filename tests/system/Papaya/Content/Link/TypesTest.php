<?php
require_once(dirname(__FILE__).'/../../../../bootstrap.php');

class PapayaContentLinkTypesTest extends PapayaTestCase {

  /**
  * @covers PapayaContentLinkTypes::getResultIterator
  */
  public function testLoad() {
    $databaseResult = $this->getMock('PapayaDatabaseResult');
    $databaseResult
      ->expects($this->any())
      ->method('fetchRow')
      ->will(
        $this->onConsecutiveCalls(
          array(
            'linktype_id' => 3,
            'linktype_name' => 'external',
            'linktype_is_visisble' => TRUE,
            'linktype_class' => 'externalLink',
            'linktype_target' => '_blank',
            'linktype_popup' => FALSE,
            'linktype_popup_config' => ''
          ),
          FALSE
        )
      );
    $databaseAccess = $this
      ->getMockBuilder('PapayaDatabaseAccess')
      ->disableOriginalConstructor()
      ->setMethods(array('queryFmt'))
      ->getMock();
    $databaseAccess
      ->expects($this->once())
      ->method('queryFmt')
      ->withAnyParameters()
      ->will($this->returnValue($databaseResult));
    $linkTypes = new PapayaContentLinkTypes();
    $linkTypes->setDatabaseAccess($databaseAccess);
    $linkTypes->load();
    $this->assertEquals(
      array(
        1 => array(
          'id' => 1,
          'name' => 'visible',
          'is_visisble' => TRUE,
          'class' => '',
          'target' => '_self',
          'is_popup' => FALSE,
          'popup_options' => array()
        ),
        2 => array(
          'id' => 2,
          'name' => 'hidden',
          'is_visisble' => FALSE,
          'class' => '',
          'target' => '_self',
          'is_popup' => FALSE,
          'popup_options' => array()
        ),
        3 => array(
          'id' => 3,
          'name' => 'external',
          'is_visisble' => TRUE,
          'class' => 'externalLink',
          'target' => '_blank',
          'is_popup' => FALSE,
          'popup_options' => array()
        )
      ),
      $linkTypes->toArray()
    );
  }

  /**
  * @covers PapayaContentLinkTypes::_createMapping
  */
  public function testCreateMapping() {
    $linkTypes = new PapayaContentLinkTypes();
    $this->assertInstanceOf(
      'PapayaDatabaseInterfaceMapping',
      $mapping = $linkTypes->mapping()
    );
    $this->assertTrue(isset($mapping->callbacks()->onMapValueFromFieldToProperty));
    $this->assertTrue(isset($mapping->callbacks()->onMapValueFromPropertyToField));
  }

  /**
  * @covers PapayaContentLinkTypes::mapFieldToProperty
  */
  public function testMapFieldToPropertyPassthru() {
    $linkTypes = new PapayaContentLinkTypes();
    $this->assertEquals(
      'success',
      $linkTypes->mapping()->callbacks()->onMapValueFromFieldToProperty(
        'name', 'linktype_name', 'success'
      )
    );
  }

  /**
  * @covers PapayaContentLinkTypes::mapFieldToProperty
  */
  public function testMapFieldToPropertyUnserialize() {
    $linkTypes = new PapayaContentLinkTypes();
    $this->assertEquals(
      array(
        'foo' => 'bar'
      ),
      $linkTypes->mapping()->callbacks()->onMapValueFromFieldToProperty(
        'popup_options',
        'linktype_popup_config',
        '<data version="2"><data-element name="foo">bar</data-element></data>'
      )
    );
  }

  /**
  * @covers PapayaContentLinkTypes::mapPropertyToField
  */
  public function testMapPropertyToFieldPassthru() {
    $linkTypes = new PapayaContentLinkTypes();
    $this->assertEquals(
      'success',
      $linkTypes->mapping()->callbacks()->onMapValueFromPropertyToField(
        'name', 'linktype_name', 'success'
      )
    );
  }

  /**
  * @covers PapayaContentLinkTypes::mapPropertyToField
  */
  public function testMapPropertyToFieldSerialize() {
    $linkTypes = new PapayaContentLinkTypes();
    $this->assertEquals(
      '<data version="2"><data-element name="foo">bar</data-element></data>',
      $linkTypes->mapping()->callbacks()->onMapValueFromPropertyToField(
        'popup_options', 'linktype_popup_config', array('foo' => 'bar')
      )
    );
  }
}