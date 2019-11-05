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
namespace Papaya\Application\Profile\Administration {

  use Papaya\Administration\RichText\Toggle as RichTextToggle;
  use Papaya\Application;

  /**
   * Application object profile for the phrase translations (used in Administration UI)
   *
   * @package Papaya-Library
   * @subpackage Application
   */
  class RichText implements Application\Profile {
    /**
     * Create the profile object and return it
     *
     * @param Application|Application\CMS $application
     *
     * @return RichTextToggle
     */
    public function createObject($application) {
      $toggle = new RichTextToggle();
      $toggle->papaya($application);
      return $toggle;
    }
  }
}
