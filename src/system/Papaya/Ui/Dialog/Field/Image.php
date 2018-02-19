<?php

class PapayaUiDialogFieldImage extends PapayaUiDialogField {

  /**
   * Message image
   *
   * @var string
   */
  protected $_fileId = '';

  /**
   * Image width
   * @var int
   */
  protected $_width = 100;

  /**
   * Image height
   * @var int
   */
  protected $_height = 100;

  /**
   * Resize mode
   * @var string
   */
  protected $_mode = 'max';

  /**
   * @var base_thumbnail
   */
  protected $_thumbnail = NULL;

  /**
   * @var PapayaUiReferenceThumbnail
   */
  protected $_referenceThumbnail = NULL;

  /**
   * Create object and assign needed values
   *
   * @param string $fileId
   * * @param string $caption
   * @param int $width
   * @param int $height
   * @param string $mode
   */
  public function __construct(
    $fileId, $caption = NULL, $width = 100, $height = 100, $mode = 'max'
  ) {
    $this->_fileId = $fileId;
    if (!is_null($caption)) {
      $this->setCaption($caption);
    }
    $this->_width = $width;
    $this->_height = $height;
    $this->_mode = $mode;
  }

  /**
   * Append image field to dialog xml dom
   *
   * @param PapayaXmlElement $parent
   */
  public function appendTo(PapayaXmlElement $parent) {
    $field = $this->_appendFieldTo($parent);

    $thumbnail = $this->thumbnail()->getThumbnail(
        $this->_fileId,
        NULL,
        $this->_width,
        $this->_height,
        $this->_mode
    );


    $this->referenceThumbnail()->setThumbnailMode($this->_mode);
    $this->referenceThumbnail()->setThumbnailSize($this->_width.'x'.$this->_height);
    $this->referenceThumbnail()->setMediaUri($thumbnail);

    $image = $field->appendElement(
        'image', array(
            'src' => $this->referenceThumbnail()->get(),
            'mode' => $this->_mode
        )
    );
  }

  /**
   * @param base_thumbnail $object
   * @return base_thumbnail
   */
  public function thumbnail(base_thumbnail $object = NULL) {
    if (isset($object)) {
      $this->_thumbnail = $object;
    } else if (is_null($this->_thumbnail)) {
      $this->_thumbnail = new base_thumbnail;
    }
    return $this->_thumbnail;
  }

  /**
   * @param PapayaUiReferenceThumbnail $object
   * @return PapayaUiReferenceThumbnail
   */
  public function referenceThumbnail(PapayaUiReferenceThumbnail $object = NULL) {
    if (isset($object)) {
      $this->_referenceThumbnail = $object;
    } else if (is_null($this->_referenceThumbnail)) {

      $this->_referenceThumbnail = new PapayaUiReferenceThumbnail();
      $this->_referenceThumbnail->setThumbnailMode($this->_mode);
      $this->_referenceThumbnail->setThumbnailSize($this->_width.'x'.$this->_height);
      $this->_referenceThumbnail->setExtension('png');
      $this->_referenceThumbnail->setPreview(
          !(isset($GLOBALS['PAPAYA_PAGE']) && $GLOBALS['PAPAYA_PAGE']->public)
      );

      $this->_referenceThumbnail->load($this->papaya()->request);
    }
    return $this->_referenceThumbnail;
  }
}
