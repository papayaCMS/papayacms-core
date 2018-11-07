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

  use Papaya\Administration\UI\Route;
  use Papaya\Filter;
  use Papaya\Response;

  /**
   * Output one or more files
   */
  class TinyMCE extends \Papaya\BaseObject\Interactive implements Route {
    public function __invoke(\Papaya\Administration\Router $router, Route\Address $address, $level = 0) {
      $isJS = $this->parameters()->get('js', TRUE);
      $core = $this->parameters()->get('core', TRUE);
      $filter = new Filter\Text\Explode(',', new Filter\NotEmpty());
      $plugins = $this->parameters()->get('plugins', [], $filter);
      $languages = $this->parameters()->get('languages', [], $filter);
      $themes = $this->parameters()->get('themes', [], $filter);
      $suffix = '_src' === $this->parameters()->get('suffix', '_src') ? '_src' : '';

      if (!$isJS) {
        return new JavaScript(
          $router->getLocalPath().'/script/tiny_mce3/tiny_mce_gzip.js',
          '',
          "\ntinyMCE_GZ.init({});\n"
        );
      }
      $path = $router->getLocalPath().'/script/tiny_mce3/';
      $cacheTime = $this->papaya()->options->get('PAPAYA_CACHE_THEMES', FALSE)
        ? $this->papaya()->options->get('PAPAYA_CACHE_TIME_THEMES', 0) : 0;

      $content = '';

      // Add core
      if ($core) {
        $content .= $this->getFile($path.'/tiny_mce'.$suffix.'.js');
        // Patch loading functions
        $content .= 'tinyMCE_GZ.start(); ';
      }

      //Add custom files
      $custom = [
        \dirname($path).'/xmlrpc.js',
        $path.'/plugins/papaya/js/jsonclass.js',
        $path.'/plugins/papaya/js/papayaparser.js',
        $path.'/plugins/papaya/js/papayatag.js',
        $path.'/plugins/papaya/js/papayautils.js'
      ];
      foreach ($custom as $file) {
        $content .= $this->getFile($file);
      }
      // Add core languages
      $content .= $this->getLanguageFiles($path, $languages);
      // Add themes
      $content .= $this->getFiles($path.'/themes', $themes, 'editor_template'.$suffix.'.js', $languages);
      // Add plugins
      $content .= $this->getFiles($path.'/plugins', $plugins, 'editor_plugin'.$suffix.'.js', $languages);

      //Restore loading functions
      if ('true' === $core) {
        $content .= "\ntinyMCE_GZ.end();";
      }

      $response = new Response();
      $response->setContentType('text/javascript');
      $response->content(new Response\Content\Text($content));
      return new Gzip(
        new Cache(
          function() use ($response) {
            return $response;
          },
          [$plugins, $languages, $themes],
          $cacheTime
        )
      );
    }

    private function getFile($path) {
      $path = \realpath($path);
      if (!empty($path) && \is_file($path) && \is_readable($path)) {
        return "\n".\file_get_contents($path)."\n\n".$this->getLoadingMarker($path);
      }
      return '';
    }

    private function getFiles($basePath, $paths, $fileName, $languages) {
      $result = '';
      foreach ($paths as $path) {
        $result .= $this->getFile($basePath.'/'.$path.'/'.$fileName);
        $result .= $this->getLanguageFiles($basePath.'/'.$path, $languages);
      }
      return $result;
    }

    private function getLanguageFiles($basePath, array $languages) {
      $result = '';
      foreach ($languages as $language) {
        $result .= $this->getFile($basePath.'/langs/'.$language.'.js');
      }
      return $result;
    }

    private function getLoadingMarker($fileName) {
      $protocol = \Papaya\Utility\Server\Protocol::get();
      $systemURL = $protocol.'://'.\strtolower($_SERVER['HTTP_HOST']);
      $file = \substr($fileName, \strlen($_SERVER['DOCUMENT_ROOT']));
      $file = \preg_replace('(^[/\\\'"\r\n]+)', '', $file);
      $file = '/'.\str_replace('\\', '/', $file);
      return "tinymce.ScriptLoader.markDone('".$systemURL.$file."');\n\n";
    }
  }
}
