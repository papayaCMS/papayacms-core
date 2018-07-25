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
* Papaya controller superclass with media database access
*
* @package Papaya-Library
* @subpackage Controller
*/
class PapayaControllerMedia extends \PapayaObject implements \PapayaController {

  private $_mediaDatabase = NULL;

    /**
  * Execute controller
   * @param \Papaya\Application $application
   * @param \PapayaRequest &$request
   * @param \PapayaResponse &$response
   * @return boolean|\PapayaController
   */
  public function execute(
    \Papaya\Application $application,
    PapayaRequest &$request,
    PapayaResponse &$response
  ) {
    $this->papaya($application);
    $request = $application->getObject('Request');
    $isPreview = $request->getParameter('preview', '', NULL, \PapayaRequest::SOURCE_PATH);
    $mediaId = $request->getParameter('media_id', '', NULL, \PapayaRequest::SOURCE_PATH);
    $mediaVersion = $request->getParameter(
      'media_version', 0, NULL, \PapayaRequest::SOURCE_PATH
    );
    if (!empty($mediaId)) {
      $file = $this->getMediaDatabase()->getFile($mediaId, $mediaVersion);
      if ($file) {
        if ($isPreview) {
          return $this->_outputPreviewFile($file);
        } else {
          return $this->_outputPublicFile($file);
        }
      } else {
        return \Papaya\Controller\Factory::createError(
          404, 'MEDIA_NO_RECORD', 'File record not found'
        );
      }
    } else {
      return \Papaya\Controller\Factory::createError(
        404, 'MEDIA_EMPTY_ID', 'Empty media id'
      );
    }
  }

  /**
  * Determine if the current surfer has the permission to retrieve the requested file.
  * @param array $file
  * @return boolean
  */
  protected function _outputPublicFile($file) {
    $folderPermissions = $this->getMediaDatabase()->getFolderPermissions($file['folder_id']);
    if (!isset($folderPermissions['surfer_view']) &&
        !isset($folderPermissions['surfer_edit'])) {
      //make public
      $this->_outputFile($file);
      return TRUE;
    } elseif (isset($folderPermissions['surfer_view'])) {
      $surfer = $this->papaya()->getObject('Surfer');
      // the surfer has one of the folder permissions
      if ($surfer->hasOnePermOf(array_keys($folderPermissions['surfer_view']))) {
        $this->_outputFile($file);
        return TRUE;
      }
    }
    return FALSE;
  }

  /**
   * Determine if current admin user is valid to send given file to client
   * @param array $file
   * @return bool
   */
  protected function _outputPreviewFile($file) {
    $user = $this->papaya()->getObject('AdministrationUser');
    if ($user->isLoggedIn()) {
      $this->_outputFile($file);
      return TRUE;
    } else {
      return FALSE;
    }
  }

  /**
  * Stop current session and send given file to client
  * @param array $file
  */
  protected function _outputFile($file) {
    // @codeCoverageIgnoreStart
    $session = $this->papaya()->getObject('Session');
    $session->close();
    \papaya_file_delivery::outputFile($file['fileName'], $file);
  }
  // @codeCoverageIgnoreEnd

  /**
  * Set media database object
  * @param base_mediadb $mediaDatabase
  * @return void
  */
  public function setMediaDatabase($mediaDatabase) {
    $this->_mediaDatabase = $mediaDatabase;
  }

  /**
  * Get media database object (implicit create)
  * @return base_mediadb
  */
  public function getMediaDatabase() {
    if (is_null($this->_mediaDatabase)) {
      $this->_mediaDatabase = new \base_mediadb();
    }
    return $this->_mediaDatabase;
  }
}
