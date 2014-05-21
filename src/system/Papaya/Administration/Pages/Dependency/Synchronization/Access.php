<?php

class PapayaAdministrationPagesDependencySynchronizationAccess
  implements PapayaAdministrationPagesDependencySynchronization {

  /**
  * Page database record object
  *
  * @var PapayaContentPageWork
  */
  private $_page = NULL;

  /**
   * Synchronize a dependency
   *
   * @param array $targetIds
   * @param integer $originId
   * @param array|NULL $languages
   * @return bool
   */
  public function synchronize(array $targetIds, $originId, array $languages = NULL) {
    if ($this->page()->load($originId)) {
      return $this->updatePages($this->page(), $targetIds);
    }
    return FALSE;
  }

  /**
  * Getter/Setter for the content page object
  *
  * @param PapayaContentPageWork $page
  * @return PapayaContentPageWork
  */
  public function page(PapayaContentPageWork $page = NULL) {
    if (isset($page)) {
      $this->_page = $page;
    } elseif (is_null($this->_page)) {
      $this->_page = new PapayaContentPageWork();
    }
    return $this->_page;
  }

  /**
  * Update target page permissions
  *
  * @param PapayaContentPageWork $origin
  * @param array $targetIds
  * @return boolean
  */
  protected function updatePages(PapayaContentPageWork $origin, array $targetIds) {
    $databaseAccess = $origin->getDatabaseAccess();
    return FALSE !== $databaseAccess->updateRecord(
      $databaseAccess->getTableName(PapayaContentTables::PAGES),
      array(
        'topic_modified' => $databaseAccess->getTimestamp(),
        'surfer_useparent' => $origin->inheritVisitorPermissions,
        'surfer_permids' => $origin->visitorPermissions
      ),
      array(
        'topic_id' => $targetIds
      )
    );
  }
}