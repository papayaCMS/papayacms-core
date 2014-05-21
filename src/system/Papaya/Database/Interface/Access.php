<?php

interface PapayaDatabaseInterfaceAccess {

  /**
   * @return PapayaDatabaseAccess
   */
  public function getDatabaseAccess();

  /**
   * @param PapayaDatabaseAccess $access
   */
  public function setDatabaseAccess(PapayaDatabaseAccess $access);
}