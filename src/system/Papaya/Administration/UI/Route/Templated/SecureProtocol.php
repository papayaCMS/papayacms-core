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
namespace Papaya\Administration\UI\Route\Templated {

  use Papaya\Administration\UI\Route\Templated;
  use Papaya\Response;
  use Papaya\Router;
  use Papaya\Utility;

  /**
   * Redirect to HTTPS if options is enabled, output an error message if not on localhost otherwise.
   */
  class SecureProtocol extends Templated {
    /**
     * @param Router $router
     * @param Router\Address $address
     * @param int $level
     * @return null|Response
     */
    public function __invoke(Router $router, Router\Address $address, $level = 0) {
      $application = $router->papaya();
      if (
        $application->options->get('PAPAYA_UI_SECURE', FALSE) &&
        !Utility\Server\Protocol::isSecure()
      ) {
        return new Response\Redirect\Secure();
      }
      if (
        $this->papaya()->options->get('PAPAYA_UI_SECURE_WARNING', TRUE) &&
        !(
          Utility\Server\Protocol::isSecure() ||
          \preg_match('(^localhost(:\d+)?$)i', Utility\Server\Name::get())
        )
      ) {
        $dialog = new \Papaya\UI\Dialog();
        $dialog->caption = new \Papaya\UI\Text\Translated('Warning');
        $url = new \Papaya\URL\Current();
        $url->setScheme('https');
        $dialog->action($url->getURL());
        $dialog->fields[] = new \Papaya\UI\Dialog\Field\Message(
          \Papaya\Message::SEVERITY_WARNING,
          new \Papaya\UI\Text\Translated(
            'If possible, please use https to access the administration interface.'
          )
        );
        $dialog->buttons[] = new \Papaya\UI\Dialog\Button\Submit(
          new \Papaya\UI\Text\Translated('Use https')
        );
        $this->getTemplate()->add($dialog);
      }
      return NULL;
    }
  }
}
