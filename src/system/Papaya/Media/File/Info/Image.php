<?php
class PapayaMediaFileInfoImage extends PapayaMediaFileInfo {

  protected function fetchProperties() {
    $properties = [
      'is_valid' => FALSE,
      'mimetype' => NULL,
      'imagetype' => 0,
      'width' => 0,
      'height' => 0,
      'bits' => 0,
      'channels' => 0,
      'file_created' => NULL,
    ];
    $data = getimagesize($this->getFileName());
    if (isset($data[2]) && ($data[2] > 0)) {
      $properties['is_valid'] = TRUE;
      $properties['mimetype'] = image_type_to_mime_type($data[2]);
      $properties['width'] = $data[0];
      $properties['height'] = $data[1];
      $properties['imagetype'] = $data[2];
      $properties['bits'] = empty($data['bits']) ? 0 : (int)$data['bits'];
      $properties['channels'] = empty($data['channels']) ? 0 : (int)$data['channels'];

      $exifFormats = [IMAGETYPE_JPEG, IMAGETYPE_TIFF_II, IMAGETYPE_TIFF_MM];
      if (
        \is_callable('exif_read_data') &&
        \in_array($properties['imagetype'], $exifFormats, TRUE)
      ) {
        $exifData = @exif_read_data($this->getFileName(), '', TRUE);
        if (is_array($exifData)) {
          $created = FALSE;
          if (isset($exifData['EXIF']['DateTimeOriginal'])) {
            $created = $exifData['EXIF']['DateTimeOriginal'];
          }
          if (!$created && isset($exifData['IFD0']['DateTime'])) {
            $created = $exifData['IFD0']['DateTime'];
          }
          if ($created) {
            $properties['file_created'] = implode('-', explode(':', $created, 3));
          }
        }
      }
      return $properties;
    }
  }

}