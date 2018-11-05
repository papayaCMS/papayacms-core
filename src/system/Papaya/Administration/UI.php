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

  use Papaya\Administration\UI\Route;
  use Papaya\Application;
  use Papaya\Response;
  use Papaya\Template;
  use Papaya\UI\Text\Translated;

  class UI implements Application\Access {
    use Application\Access\Aggregation;

    /**
     * @var Template
     */
    private $_template;

    /**
     * @var \Papaya\Theme\Handler
     */
    private $_themeHandler;

    /**
     * @var string
     */
    private $_path;

    /**
     * @var callable
     */
    private $_route;

    /**
     * UI constructor.
     *
     * @param string $path
     * @param \Papaya\Application $application
     */
    public function __construct($path, Application $application) {
      $this->_path = \str_replace(DIRECTORY_SEPARATOR, '/', $path);
      $this->papaya($application);
    }

    /**
     * @return string Local path to administration directory
     */
    public function getLocalPath() {
      return $this->_path;
    }

    /**
     * Initialize application and options and execute routes depending on the URL.
     *
     * Possible return values for routes:
     *   \Papaya\Response - returned from this method
     *   TRUE - request was handled, do not execute other routes
     *   NULL - not handled, continue route execution
     *   callable - new route, execute
     *
     * @return null|\Papaya\Response
     */
    public function execute() {
      $this->prepare();
      $application = $this->papaya();
      $address = new Route\Address($application->options->get('PAPAYA_PATH_ADMIN', ''));
      if (!$application->options->loadAndDefine() && Route::INSTALLER !== $address->getRoute(0)) {
        return new Response\Redirect(Route::INSTALLER);
      }
      $route = $this->route();
      do {
        $route = $route($this, $address);
        if ($route instanceof Response) {
          return $route;
        }
      } while (is_callable($route));
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
      if ($application->administrationUser->isValid) {
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
      $template = $this->template();
      $template->parameters()->set('PAGE_ICON', $image);
      if (\is_array($caption)) {
        $caption = \implode(
          ' - ',
          \array_map(
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

    private function prepare() {
      $application = $this->papaya();
      $application->messages->setUp($application->options, $this->template());
      if ($application->options->get('PAPAYA_LOG_RUNTIME_REQUEST', FALSE)) {
        \Papaya\Request\Log::getInstance();
      }
      $application->request->isAdministration = TRUE;
      $application->session->isAdministration(TRUE);
      if ($redirect = $application->session->activate(TRUE)) {
        $redirect->send(TRUE);
      }
      $application->pageReferences->setPreview(TRUE);
    }

    /**
     * @param callable|null $route
     * @return callable|Route
     */
    public function route(callable $route = NULL) {
      if (NULL !== $route) {
        $this->_route = $route;
      } elseif (NULL === $this->_themeHandler) {
        $images = $this->papaya()->images;
        $localPath = $this->getLocalPath();
        $cacheTime = $this->papaya()->options->get('PAPAYA_CACHE_THEMES', FALSE)
          ? $this->papaya()->options->get('PAPAYA_CACHE_TIME_THEMES', 0) : 0;
        $this->_route = new Route\Group(
          // enforce https (if configured)
          new Route\SecureProtocol(),
          // installer and logout need to work without login/authentication
          new Route\Choice(
            [
              Route::LOGOUT => new Route\LogOut(),
              Route::INSTALLER => new Route\Installer(),
              Route::STYLES => function(self $ui) use ($localPath, $cacheTime) {
                $stylePath = $localPath.'/styles';
                $themePath = $stylePath.'/themes';
                $themeName = empty($_GET['theme'])
                  ? $ui->papaya()->options->get('PAPAYA_UI_THEME', '')
                  : $_GET['theme'];
                return new Route\Gzip(
                  new Route\Cache(
                    new Route\Choice(
                      [
                        Route::STYLES_CSS => new Route\Choice(
                          [
                            Route::STYLES_CSS => new Route\CSS($stylePath.'/main.css', $themeName, $themePath),
                            Route::STYLES_CSS_POPUP => new Route\CSS($stylePath.'/popup.css', $themeName, $themePath),
                            Route::STYLES_CSS_RICHTEXT => new Route\CSS($stylePath.'/richtext.css', $themeName, $themePath)
                          ]
                        ),
                        Route::STYLES_JAVASCRIPT => new Route\JavaScript(
                          [$stylePath.'/functions.js', $stylePath.'/lightbox.js', $stylePath.'/richtext-toggle.js']
                        )
                      ],
                      NULL,
                      0,
                      1
                    ),
                    $themeName,
                    $cacheTime
                  )
                );
              },
              Route::SCRIPTS => new Route\Choice(
                [
                  Route::SCRIPTS => function() use ($localPath, $cacheTime) {
                    $files = isset($_GET['files']) ? \explode(',', $_GET['files']) : [];
                    $files = array_map(
                      function ($file) use ($localPath) {
                        return $localPath.'/script/'.$file;
                      },
                      array_filter(
                        $files,
                        function ($file) {
                          return preg_match('(^[\w.-]+(/[\w.-]+)*\.js$)', $file);
                        }
                      )
                    );
                    return new Route\Cache(
                      new Route\JavaScript($files),
                      $files,
                      $cacheTime
                    );
                  },
                  Route::SCRIPTS_TINYMCE => new Route\Choice(
                    [
                      Route::SCRIPTS_TINYMCE_FILES => new Route\TinyMCE()
                    ]
                  )
                ]
              )
            ]
          ),
          // Authentication needed
          new Route\Authenticated(
            new Route\Group(
              // validate options and add warnings
              new Route\ValidateOptions(),
              new Route\Choice(
                [
                  // General
                  Route::OVERVIEW => new Route\Page(
                    $images['places-home'], ['General', 'Overview'], \papaya_overview::class
                  ),
                  Route::MESSAGES => new UI\Route\Choice(
                    [
                      Route::MESSAGES => new Route\Page(
                        $images['status-mail-open'], ['General', 'Messages'], \papaya_messages::class
                      ),
                      Route::MESSAGES_TASKS => new Route\Page(
                        $images['items-task'], ['General', 'Messages', 'Tasks'], \papaya_todo::class
                      ),
                    ]
                  ),

                  // Pages
                  Route::PAGES => new UI\Route\Choice(
                    [
                      Route::PAGES_SITEMAP => new Route\Page(
                        $images['categories-sitemap'], ['Pages', 'Sitemap'], \papaya_topic_tree::class, Permissions::PAGE_MANAGE
                      ),
                      Route::PAGES_SEARCH => new Route\Page(
                        $images['actions-search'], ['Pages', 'Search'], \papaya_overview_search::class, Permissions::PAGE_SEARCH
                      ),
                      Route::PAGES_EDIT => new Route\Page(
                        $images['items-page'], 'Pages', \papaya_topic::class, Permissions::PAGE_MANAGE
                      )
                    ]
                  ),

                  // Additional Content
                  Route::CONTENT => new UI\Route\Choice(
                    [
                      Route::CONTENT_BOXES => new Route\Page(
                        $images['items-box'], ['Content', 'Boxes'], \papaya_boxes::class, Permissions::BOX_MANAGE
                      ),
                      Route::CONTENT_FILES => new UI\Route\Choice(
                        [
                          Route::CONTENT_FILES => new Route\Page(
                            $images['items-folder'], ['Content', 'Files'], \papaya_mediadb::class, Permissions::FILE_MANAGE
                          ),
                          Route::CONTENT_FILES_BROWSER => new Route\Page(
                            $images['items-folder'], ['Content', 'Files'], \papaya_mediadb_browser::class, Permissions::FILE_BROWSE
                          )
                        ]
                      ),
                      Route::CONTENT_IMAGES => new Route\Page(
                        $images['items-graphic'], ['Content', 'Dynamic Images'], \papaya_imagegenerator::class, Permissions::IMAGE_GENERATOR
                      ),
                      Route::CONTENT_ALIASES => new Route\Page(
                        $images['items-alias'], ['Content', 'Alias'], \papaya_alias_tree::class, Permissions::ALIAS_MANAGE
                      ),
                      Route::CONTENT_TAGS => new Route\Page(
                        $images['items-tag'], ['Content', 'Tags'], \papaya_tags::class, Permissions::TAG_MANAGE
                      )
                    ]
                  ),
                  // Extensions/Applications
                  Route::EXTENSIONS => new Route\Extensions(
                    $images['categories-applications'], 'Applications'
                  ),
                  // Administration
                  Route::ADMINISTRATION => new UI\Route\Choice(
                    [
                      Route::ADMINISTRATION_USERS => new Route\Page(
                        $images['items-user-group'], ['Administration', 'Users'], \papaya_user::class, Permissions::USER_MANAGE
                      ),
                      Route::ADMINISTRATION_VIEWS => new Route\Page(
                        $images['items-view'], ['Administration', 'Views'], \base_viewlist::class, Permissions::VIEW_MANAGE
                      ),
                      Route::ADMINISTRATION_PLUGINS => new Route\Page(
                        $images['items-plugin'], ['Administration', 'Plugins / Modules'], \papaya_modulemanager::class, Permissions::MODULE_MANAGE
                      ),
                      Route::ADMINISTRATION_THEMES => new Route\Page(
                        $images['items-theme'], ['Administration', 'Themes', 'Skins'], Theme\Editor::class, Permissions::SYSTEM_THEME_SKIN_MANAGE
                      ),
                      Route::ADMINISTRATION_PROTOCOL => new UI\Route\Choice(
                        [
                          Route::ADMINISTRATION_PROTOCOL => new Route\Page(
                            $images['categories-protocol'], ['Administration', 'Protocol'], \papaya_log::class, Permissions::SYSTEM_PROTOCOL
                          ),
                          Route::ADMINISTRATION_PROTOCOL_LOGIN => new Route\Page(
                            $images['categories-protocol'], ['Administration', 'Protocol', 'Login'], \papaya_auth_secure::class, Permissions::SYSTEM_PROTOCOL
                          )
                        ]
                      ),
                      Route::ADMINISTRATION_SETTINGS => new Route\Page(
                        $images['items-option'], ['Administration', 'Settings'], \papaya_options::class, Permissions::SYSTEM_SETTINGS
                      ),
                      Route::ADMINISTRATION_CRONJOBS => new Route\Page(
                        $images['items-cronjob'], ['Administration', 'Settings', 'Cronjobs'], \base_cronjobs::class, Permissions::SYSTEM_CRONJOBS
                      ),
                      Route::ADMINISTRATION_LINK_TYPES => new Route\Page(
                        $images['items-link'], ['Administration', 'Settings', 'Link types'], \papaya_linktypes::class, Permissions::SYSTEM_LINKTYPES_MANAGE
                      ),
                      Route::ADMINISTRATION_MIME_TYPES => new Route\Page(
                        $images['items-option'], ['Administration', 'Settings', 'Mime types'], \papaya_mediadb_mime::class, Permissions::SYSTEM_MIMETYPES_MANAGE
                      ),
                      Route::ADMINISTRATION_SPAM_FILTER => new Route\Page(
                        $images['items-option'], ['Administration', 'Settings', 'Spam filter'], \papaya_spamfilter::class, Permissions::SYSTEM_SETTINGS
                      ),
                      Route::ADMINISTRATION_ICONS => new Route\Page(
                        $images['items-option'], ['Administration', 'Settings', 'Icons'], Settings\Icons\Page::class, Permissions::SYSTEM_SETTINGS
                      ),
                      Route::ADMINISTRATION_PHRASES => new Route\Page(
                        $images['items-translation'], ['Administration', 'Translations'], \base_languages::class, Permissions::SYSTEM_TRANSLATE
                      ),
                    ]
                  ),
                  // Help
                  Route::HELP => new Route\Page(
                    $images['categories-help'], 'Help', \papaya_help::class
                  ),
                  // Popups
                  Route::POPUP => new Route\Cache(
                    new Route\Choice(
                      [
                        Route::POPUP_COLOR => new Route\Popup($localPath.'/popup/color.xsl'),
                        Route::POPUP_GOOGLE_MAPS => new Route\Popup($localPath.'/popup/googlemaps.xsl'),
                        Route::POPUP_IMAGE => new Route\Popup($localPath.'/popup/image.xsl'),
                        Route::POPUP_PAGE => new Route\Popup($localPath.'/popup/page.xsl'),
                        Route::POPUP_MEDIA_BROWSER_HEADER => new Route\Popup($localPath.'/popup/media-header.xsl'),
                        Route::POPUP_MEDIA_BROWSER_FOOTER => new Route\Popup($localPath.'/popup/media-footer.xsl'),
                        Route::POPUP_MEDIA_BROWSER_FILES => new Route\Popup($localPath.'/popup/media-files.xsl'),
                        Route::POPUP_MEDIA_BROWSER_IMAGES => new Route\Popup($localPath.'/popup/media-images.xsl')
                      ]
                    ),
                    $this->papaya()->administrationLanguage->code,
                    $cacheTime
                  ),
                  // XML
                  Route::XML_API => function() {
                    $rpcCall = new \papaya_rpc();
                    $rpcCall->initialize();
                    $rpcCall->execute();
                    $response = new Response();
                    $response->setContentType('application/xml');
                    $response->content(new Response\Content\Text($rpcCall->getXML()));
                    if ($this->papaya()->options->get('PAPAYA_LOG_RUNTIME_REQUEST', FALSE)) {
                      \Papaya\Request\Log::getInstance()->emit();
                      $this->papaya()->database->close();
                    }
                    return $response;
                  }
                ],
                UI\Route::OVERVIEW
              )
            )
          ),
          new Route\Error('Unknown route!', 404)
        );
      }
      return $this->_route;
    }

    /**
     * @param Template|null $template
     * @return Template
     */
    public function template(Template $template = NULL) {
      if (NULL !== $template) {
        $this->_template = $template;
      } elseif (NULL === $this->_template) {
        $this->_template = new Template\XSLT(
          $this->getLocalPath().'/template/style.xsl'
        );
      }
      return $this->_template;
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
