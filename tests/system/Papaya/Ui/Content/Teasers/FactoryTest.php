<?php
require_once(dirname(__FILE__).'/../../../../../bootstrap.php');

class PapayaUiContentTeasersFactoryTest extends PapayaTestCase {

  /**
   * @covers PapayaUiContentTeasersFactory
   */
  public function testByFilterWithParentIdAndViewId() {
    $orderBy = $this->getMock('PapayaDatabaseInterfaceOrder');

    $factory = new PapayaUiContentTeasersFactory();
    $factory->papaya($this->mockPapaya()->application());

    $teasers = $factory->byFilter(
      array('parent' => 21, 'view_id' => 42, 'language_id' => 1), $orderBy
    );
    $this->assertInstanceOf('PapayaUiContentTeasers', $teasers);
    $this->assertInstanceOf('PapayaContentPagesPublications', $teasers->pages());

  }

  /**
   * @covers PapayaUiContentTeasersFactory
   */
  public function testByParentWithOnePageIdInPreviewMode() {
    $request = $this->mockPapaya()->request();
    $request
      ->expects($this->any())
      ->method('__get')
      ->will(
        $this->returnValueMap(
          array(
            array('isPreview', true),
            array('languageId', 9)
          )
        )
      );

    $factory = new PapayaUiContentTeasersFactory();
    $factory->papaya(
      $this->mockPapaya()->application(
        array('request' => $request)
      )
    );

    $teasers = $factory->byParent(42);
    $this->assertInstanceOf('PapayaUiContentTeasers', $teasers);
    $this->assertInstanceOf('PapayaContentPages', $teasers->pages());
    $this->assertNotInstanceOf('PapayaContentPagesPublications', $teasers->pages());
  }

  /**
   * @covers PapayaUiContentTeasersFactory
   */
  public function testByParentWithTwoPageIdsWithIndividualOrderBy() {
    $orderBy = $this->getMock('PapayaDatabaseInterfaceOrder');

    $factory = new PapayaUiContentTeasersFactory();
    $factory->papaya($this->mockPapaya()->application());

    $teasers = $factory->byParent(array(21, 42), $orderBy);
    $this->assertInstanceOf('PapayaUiContentTeasers', $teasers);
    $this->assertInstanceOf('PapayaContentPagesPublications', $teasers->pages());
  }

  /**
   * @covers PapayaUiContentTeasersFactory
   */
  public function testByParentWithTwoPageIdsWithInvalidOrderBy() {
    $factory = new PapayaUiContentTeasersFactory();
    $factory->papaya($this->mockPapaya()->application());

    $teasers = $factory->byParent(array(21, 42), 'invalid');
    $this->assertInstanceOf('PapayaUiContentTeasers', $teasers);
    $this->assertInstanceOf('PapayaContentPagesPublications', $teasers->pages());
  }

  /**
   * @covers PapayaUiContentTeasersFactory
   */
  public function testByPageIdWithOnePageId() {
    $factory = new PapayaUiContentTeasersFactory();
    $factory->papaya($this->mockPapaya()->application());

    $teasers = $factory->byPageId(42);
    $this->assertInstanceOf('PapayaUiContentTeasers', $teasers);
    $this->assertInstanceOf('PapayaContentPagesPublications', $teasers->pages());
  }

}