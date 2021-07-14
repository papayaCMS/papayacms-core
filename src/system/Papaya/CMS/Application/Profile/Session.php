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
namespace Papaya\CMS\Application\Profile;

use Papaya\Application;
use Papaya\Filter;
use Papaya\CMS\CMSConfiguration;
use Papaya\Session\Options as SessionOptions;

/**
 * Application object profile for default session object
 *
 * @package Papaya-Library
 * @subpackage Application
 */
class Session implements Application\Profile {
  /**
   * Create the profile object and return it
   *
   * @param \Papaya\CMS\CMSApplication $application
   *
   * @return \Papaya\Session
   */
  public function createObject($application) {
    $session = new \Papaya\Session();
    $session->papaya($application);
    $options = $application->options;
    $session->options()->assign(
      [
        SessionOptions::SECURE_SESSION => $options->get(
          CMSConfiguration::SESSION_SECURE, FALSE
        ),
        SessionOptions::SECURE_EDITOR_SESSION => $options->get(
          CMSConfiguration::UI_SECURE, FALSE
        ),
        SessionOptions::ID_FALLBACK => $options->get(
          CMSConfiguration::SESSION_ID_FALLBACK,
          SessionOptions::FALLBACK_REWRITE
        ),
        SessionOptions::NAME => $options->get(
          CMSConfiguration::SESSION_NAME, ''
        ),
        SessionOptions::PATH =>  $options->get(
          CMSConfiguration::SESSION_PATH, '/', new Filter\NotEmpty()
        ),
        SessionOptions::DOMAIN =>  $options->get(
          CMSConfiguration::SESSION_DOMAIN, ''
        ),
        SessionOptions::HTTP_ONLY =>  $options->get(
          CMSConfiguration::SESSION_HTTP_ONLY, FALSE
        )
      ]
    );
    if ( $application->request->isAdministration) {
      $session->isAdministration(TRUE);
    }
    return $session;
  }
}
