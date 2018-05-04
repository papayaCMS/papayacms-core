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

class PapayaPluginFilterContentRecords extends PapayaPluginFilterContentGroup {

  private $_viewConfigurations = NULL;
  private $_loaded = FALSE;

  public function records(PapayaContentViewConfigurations $configurations = NULL) {
    if (isset($configurations)) {
      $this->_viewConfigurations = $configurations;
    } elseif (NULL == $this->_viewConfigurations) {
      $this->_viewConfigurations = new \PapayaContentViewConfigurations();
      $this->_viewConfigurations->activateLazyLoad(
        array(
          'id' => $this->getPage()->getPageViewId(),
          'type' => 'datafilter'
        )
      );
    }
    return $this->_viewConfigurations;
  }

  public function getIterator() {
    if (!$this->_loaded) {
      foreach ($this->records() as $record) {
        $plugin = $this->papaya()->plugins->get(
          $record['module_guid'], $this->getPage(), $record['options']
        );
        if ($plugin) {
          $this->add($plugin);
        }
      }
      $this->_loaded = TRUE;
    }
    return parent::getIterator();
  }
}
