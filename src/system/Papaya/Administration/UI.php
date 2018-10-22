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
namespace Papaya\Administration {

  use Papaya\Administration;
  use Papaya\Application;
  use Papaya\Template;
  use Papaya\UI\Text\Translated;
  use Papaya\Utility;

  class UI implements Application\Access {
    use Application\Access\Aggregation;

    private $_template;

    private $_themeHandler;

    /**
     * @var string
     */
    private $_path;

    private $_route;

    public function __construct($path, Application $application) {
      $this->_path = \str_replace(DIRECTORY_SEPARATOR, '/', $path);
      $this->papaya($application);
    }

    /**
     * @return null|\Papaya\Response
     */
    public function execute() {
      $this->prepare();
      $application = $this->papaya();
      if (!$application->options->loadAndDefine()) {
        return new \Papaya\Response\Redirect('install.php');
      }
      if (
        $application->options->get('PAPAYA_UI_SECURE', FALSE) &&
        !Utility\Server\Protocol::isSecure()
      ) {
        return new \Papaya\Response\Redirect\Secure();
      }
      $route = $this->route();
      $route($this, new UI\Route\Address());
      return NULL;
    }

    public function getOutput() {
      $application = $this->papaya();
      $template = $this->template();
      $template->parameters()->assign(
        [
          'PAGE_PROJECT' => $application->options->get('PAPAYA_PROJECT_TITLE', 'CMS Project'),
          'PAGE_REVISION' => \trim(\constant('PAPAYA_WEBSITE_REVISION')),
          'PAPAYA_DBG_DEVMODE' => $application->options->get('PAPAYA_DBG_DEVMODE', FALSE),
          'PAPAYA_LOGINPAGE' => !$application->administrationUser->isValid,
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
      if ($application->administrationUser->isValid) {
        $template->parameters()->set('PAGE_USER', $application->administrationUser->user['fullname']);
        $template->add($application->administrationLanguage->getXML(), 'title-menu');
        $template->add($application->administrationRichText->getXML(), 'title-menu');
        $template->add((new UI\Navigation\Main())->getXML(), 'menus');
      }
      return $template->getOutput();
    }

    /**
     * @param string $image
     * @param array|string $caption
     */
    public function setTitle($image, $caption) {
      $template = $this->template();
      $template->parameters()->set('PAGE_ICON', $image);
      if (is_array($caption)) {
        $caption = implode(
          ' - ',
          array_map(
            function($captionPart) {
              return new Translated($captionPart);
            },
            $caption
          )
        );
      }
      $template->parameters()->set('PAGE_TITLE', $caption);
    }

    /**
     * Get count of new message for the current user
     */
    private function getNewMessageCount() {
      $messages = new \base_messages();
      $counts = $messages->loadMessageCounts([0], TRUE);
      return empty($counts[0]) ? 0 : (int)$counts[0];
    }

    private function prepare() {
      $application = $this->papaya();
      $application->messages->setUp($application->options);
      if ($application->options->get('PAPAYA_LOG_RUNTIME_REQUEST', FALSE)) {
        \Papaya\Request\Log::getInstance();
      }
      $application->request->isAdministration = TRUE;
      $application->session->isAdministration(TRUE);
      if ($redirect = $application->session->activate(TRUE)) {
        $redirect->send(TRUE);
      }
      $application->pageReferences->setPreview(TRUE);

      // validate options and show warnings
      if (
        '' !== ($path = $application->options->get('PAPAYA_PATH_DATA')) &&
        FALSE !== \strpos($path, $_SERVER['DOCUMENT_ROOT']) &&
        \file_exists($path) && (!\file_exists($path.'.htaccess'))
      ) {
        $application->messages->displayWarning(
          'The file ".htaccess" in the directory "papaya-data/" '.
          'is missing or not accessible. Please secure the directory.'
        );
      }
      if (!$application->options->get('PAPAYA_PASSWORD_REHASH', FALSE)) {
        $application->messages->displayWarning(
          'The password rehashing is not active. Please activate PAPAYA_PASSWORD_REHASH.'.
          ' Make sure the authentication tables are up to date before activating'.
          ' this option, otherwise the logins can become locked.'
        );
      }
    }

    public function route(callable $route = NULL) {
      if (NULL !== $route) {
        $this->_route = $route;
      } elseif (NULL === $this->_themeHandler) {
        $images = $this->papaya()->images;
        $this->_route = new UI\Route\Group(
            new UI\Route\Choice(
            [
              // General

              // Pages

              // Additional Content
              Administration\UI\Route::CONTENT => new UI\Route\Choice(
                [
                  Administration\UI\Route::CONTENT_BOXES => new Administration\UI\Route\Page(
                    $images['items-box'],
                    ['Content', 'Boxes'],
                    \papaya_boxes::class,
                    Administration\Permissions::BOX_MANAGE
                  ),
                  Administration\UI\Route::CONTENT_FILES => new UI\Route\Choice(
                    [
                      Administration\UI\Route::CONTENT_FILES => new Administration\UI\Route\Page(
                        $images['items-folder'],
                        ['Content', 'Files'],
                        \papaya_mediadb::class,
                        Administration\Permissions::FILE_MANAGE
                      ),
                      Administration\UI\Route::CONTENT_FILES_BROWSER => new Administration\UI\Route\Page(
                        $images['items-folder'],
                        ['Content', 'Files'],
                        \papaya_mediadb_browser::class,
                        Administration\Permissions::FILE_BROWSE
                      )
                    ]
                  ),
                  Administration\UI\Route::CONTENT_IMAGES => new Administration\UI\Route\Page(
                    $images['items-graphic'],
                    ['Content', 'Dynamic Images'],
                    \papaya_imagegenerator::class,
                    Administration\Permissions::IMAGE_GENERATOR
                  ),
                  Administration\UI\Route::CONTENT_ALIASES => new Administration\UI\Route\Page(
                    $images['items-alias'],
                    ['Content', 'Alias'],
                    \papaya_alias_tree::class,
                    Administration\Permissions::ALIAS_MANAGE
                  )
                ]
              ),
              // Extensions/Applications
              Administration\UI\Route::EXTENSIONS => new Administration\UI\Route\Extensions(
                $images['categories-applications'],
                'Applications'
              ),
              // Administration
              Administration\UI\Route::ADMINISTRATION => new UI\Route\Choice(
                [
                  Administration\UI\Route::ADMINISTRATION_USERS => new Administration\UI\Route\Page(
                    $images['items-user-group'],
                    ['Administration', 'Users'],
                    \papaya_user::class,
                    Administration\Permissions::USER_MANAGE
                  ),
                  Administration\UI\Route::ADMINISTRATION_PROTOCOL => new UI\Route\Choice(
                    [
                      Administration\UI\Route::ADMINISTRATION_PROTOCOL => new Administration\UI\Route\Page(
                        $images['categories-protocol'],
                        ['Administration', 'Protocol'],
                        \papaya_log::class,
                        Administration\Permissions::SYSTEM_PROTOCOL
                      ),
                      Administration\UI\Route::ADMINISTRATION_PROTOCOL_LOGIN => new Administration\UI\Route\Page(
                        $images['categories-protocol'],
                        ['Administration', 'Protocol', 'Login'],
                        \papaya_auth_secure::class,
                        Administration\Permissions::SYSTEM_PROTOCOL
                      )
                    ]
                  ),
                  Administration\UI\Route::ADMINISTRATION_CRONJOBS => new Administration\UI\Route\Page(
                    $images['items-cronjob'],
                    ['Administration', 'Settings', 'Cronjobs'],
                    \base_cronjobs::class,
                    Administration\Permissions::SYSTEM_CRONJOBS
                  ),
                  Administration\UI\Route::ADMINISTRATION_LINK_TYPES => new Administration\UI\Route\Page(
                    $images['items-link'],
                    ['Administration', 'Settings', 'Link types'],
                    \papaya_linktypes::class,
                    Administration\Permissions::SYSTEM_LINKTYPES_MANAGE
                  )
                ]
              ),
              // Others
              Administration\UI\Route::HELP => new Administration\UI\Route\Page(
                $images['categories-help'],
                'Help',
                \papaya_help::class
              )
            ]
          )
        );
        $this->_route->before(
          function() {
            $application = $this->papaya();
            $user = $application->administrationUser;
            $user->layout = $this->template();
            $user->initialize();
            $user->execLogin();
            $application->administrationPhrases->setLanguage(
              $application->languages->getLanguage(
                $application->administrationUser->options->get('PAPAYA_UI_LANGUAGE')
              )
            );
            return $this->papaya()->administrationUser->isValid;
          }
        );
      }
      return $this->_route;
    }

    public function template(Template $template = NULL) {
      if (NULL !== $template) {
        $this->_template = $template;
      } elseif (NULL === $this->_template) {
        $application = $this->papaya();
        $administrationPath = Utility\File\Path::cleanup(
          Utility\File\Path::getDocumentRoot(
            $application->options
          ).$application->options->get('PAPAYA_PATH_ADMIN')
        );
        $this->_template = new Template\XSLT(
          $administrationPath.'skins/'.$application->options->get('PAPAYA_UI_SKIN').'/style.xsl'
        );
      }
      return $this->_template;
    }

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
