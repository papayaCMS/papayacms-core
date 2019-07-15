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

  use Papaya\Administration\UI as AdministrationUI;
  use Papaya\Administration\UI\Route\Templated;
  use Papaya\Database\Exception\ConnectionFailed;
  use Papaya\Message;
  use Papaya\Response;
  use Papaya\Router;
  use Papaya\UI\Dialog;
  use Papaya\UI\Dialog\Button\Submit as SubmitButton;
  use Papaya\URL\Current as CurrentURL;
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
        try {
          $fetchPhrases = (
            $address->getRouteString($level) !== AdministrationUI::INSTALLER &&
            $this->papaya()->database->getConnector()->connect()
          );
        } catch (ConnectionFailed $exception) {
          $fetchPhrases = FALSE;
        }

        $dialog = new Dialog();
        $url = new CurrentURL();
        $url->setScheme('https');
        $dialog->action($url->getURL());
        $texts = [
          'Warning',
          'If possible, please use https to access the administration interface.',
          'Use https'
        ];
        if ($fetchPhrases) {
          $texts = iterator_to_array(
            $this->papaya()->administrationPhrases->getList($texts)
          );
        }
        $dialog->caption = $texts[0];
        $dialog->fields[] = new \Papaya\UI\Dialog\Field\Message(
          Message::SEVERITY_WARNING, $texts[1]
        );
        $dialog->buttons[] = new SubmitButton($texts[2]);
        $this->getTemplate()->add($dialog);
      }
      return NULL;
    }
  }
}
