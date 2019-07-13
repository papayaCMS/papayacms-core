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
namespace Papaya\Administration\UI\Route {

  use Papaya\Administration\UI;
  use Papaya\Response;
  use Papaya\Router;

  abstract class Templated implements Router\Route, \Papaya\Application\Access {
    use \Papaya\Application\Access\Aggregation;

    /**
     * @var \Papaya\Template
     */
    private $_template;

    /**
     * @var \Papaya\Theme\Handler
     */
    private $_themeHandler;

    /**
     * @var $_showUserStatus
     */
    private $_showUserStatus;

    public function __construct(\Papaya\Template $template, $showUserStatus = TRUE) {
      $this->_template = $template;
      $this->_showUserStatus = (bool)$showUserStatus;
    }

    public function getTemplate() {
      return $this->_template;
    }

    public function getOutput() {
      $application = $this->papaya();
      $template = $this->getTemplate();
      $template->parameters()->assign(
        [
          'PAGE_PROJECT' => $application->options->get('PAPAYA_PROJECT_TITLE', 'CMS Project'),
          'PAGE_REVISION' => \trim(\constant('PAPAYA_WEBSITE_REVISION')),
          'PAPAYA_DBG_DEVMODE' => $application->options->get('PAPAYA_DBG_DEVMODE', FALSE),
          'PAPAYA_USER_AUTHORIZED' => $application->administrationUser->isValid,
          'PAPAYA_UI_LANGUAGE' => $application->administrationUser->options['PAPAYA_UI_LANGUAGE'],
          'PAPAYA_UI_THEME' => $application->options->get('PAPAYA_UI_THEME', 'green'),
          'PAPAYA_USE_RICHTEXT' => $application->administrationRichText->isActive(),
          'PAPAYA_RICHTEXT_CONTENT_CSS' =>
          $this->theme()->getURL(NULL, $application->options->get('PAPAYA_RICHTEXT_CONTENT_CSS')),
          'PAPAYA_RICHTEXT_TEMPLATES_FULL' =>
          $application->options->get('PAPAYA_RICHTEXT_TEMPLATES_FULL'),
          'PAPAYA_RICHTEXT_TEMPLATES_SIMPLE' =>
          $application->options->get('PAPAYA_RICHTEXT_TEMPLATES_SIMPLE'),
          'PAPAYA_RICHTEXT_LINK_TARGET' =>
          $application->options->get('PAPAYA_RICHTEXT_LINK_TARGET'),
          'PAPAYA_RICHTEXT_BROWSER_SPELLCHECK' =>
          $application->options->get('PAPAYA_RICHTEXT_BROWSER_SPELLCHECK'),
          'PAPAYA_MESSAGES_INBOX_NEW' => $this->getNewMessageCount()
        ]
      );
      if ($this->_showUserStatus && $application->administrationUser->isValid) {
        $template->parameters()->set('PAGE_USER', $application->administrationUser->user['fullname']);
        $template->add($application->administrationLanguage, 'title-menu');
        $template->add($application->administrationRichText, 'title-menu');
        $template->add(new UI\Navigation\Main(), 'menus');
      }
      $response = new Response();
      $response->content(new Response\Content\Text($template->getOutput()));
      if ($application->options->get('PAPAYA_LOG_RUNTIME_REQUEST', FALSE)) {
        \Papaya\Request\Log::getInstance()->emit();
        $application->database->close();
      }
      return $response;
    }

    /**
     * @param string $image
     * @param array|string $caption
     */
    public function setTitle($image, $caption) {
      $template = $this->getTemplate();
      $template->parameters()->set('PAGE_ICON', $image);
      if (\is_array($caption)) {
        $caption = \implode(
          ' - ',
          \array_map(
            function($captionPart) {
              return new \Papaya\UI\Text\Translated($captionPart);
            },
            $caption
          )
        );
      }
      $template->parameters()->set('PAGE_TITLE', $caption);
    }

    /**
     * Get count of new message for the current user
     * @return int|string
     */
    private function getNewMessageCount() {
      if ($this->papaya()->administrationUser->isValid) {
        $messages = new \base_messages();
        $counts = $messages->loadMessageCounts([0], TRUE);
        return empty($counts[0]) ? 0 : (int)$counts[0];
      }
      return '';
    }

    /**
     * @param \Papaya\Theme\Handler|null $themeHandler
     * @return \Papaya\Theme\Handler
     */
    public function theme(\Papaya\Theme\Handler $themeHandler = NULL) {
      if (NULL !== $themeHandler) {
        $this->_themeHandler = $themeHandler;
      } elseif (NULL === $this->_themeHandler) {
        $this->_themeHandler = new \Papaya\Theme\Handler();
        $this->_themeHandler->papaya($this->papaya());
      }
      return $this->_themeHandler;
    }
  }
}
