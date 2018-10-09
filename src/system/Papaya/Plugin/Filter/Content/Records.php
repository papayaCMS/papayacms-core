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
namespace Papaya\Plugin\Filter\Content;

use Papaya\Content;
use Papaya\Plugin;

/**
 * Class Papaya\Plugin\Filter\Content\Records
 */
class Records extends Group {
  /**
   * @var Content\View\Configurations
   */
  private $_viewConfigurations;

  /**
   * @var bool
   */
  private $_loaded = FALSE;

  /**
   * @param \Papaya\Content\View\Configurations|null $configurations
   * @return \Papaya\Content\View\Configurations
   */
  public function records(Content\View\Configurations $configurations = NULL) {
    if (NULL !== $configurations) {
      $this->_viewConfigurations = $configurations;
    } elseif (NULL === $this->_viewConfigurations) {
      $this->_viewConfigurations = new Content\View\Configurations();
      $this->_viewConfigurations->activateLazyLoad(
        [
          'id' => $this->getPage()->getPageViewId(),
          'type' => 'datafilter'
        ]
      );
    }
    return $this->_viewConfigurations;
  }

  /**
   * @return \Traversable
   */
  public function getIterator() {
    if (!$this->_loaded) {
      foreach ($this->records() as $record) {
        /** @var Plugin\Filter\Content|\base_plugin $plugin */
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
