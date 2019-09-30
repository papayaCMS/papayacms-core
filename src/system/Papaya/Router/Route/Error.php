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
namespace Papaya\Router\Route {

  use Papaya\Response;
  use Papaya\Router;
  use Papaya\Utility;

  /**
   * Return error document
   */
  class Error implements Router\Route {
    /**
     * HTTP response status
     *
     * @var int
     */
    private $_status;

    /**
     * Error message
     *
     * @var string
     */
    private $_errorMessage;

    /**
     * Error identifier
     *
     * @var string|null
     */
    private $_errorIdentifier;

    /**
     * Error template
     *
     * @var string
     */
    private $_template =
      '<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.0 Transitional//EN">
     <html>
       <head>
         <title>{%status%} - {%message%}</title>
         <style type="text/css">
         <!--
         body {
           background-color: #FFF;
           font-family: Verdana, Geneva, Arial, Helvetica, sans-serif;
         }
         th, td {
           font-family: Verdana, Geneva, Arial, Helvetica, sans-serif;
         }
         pre {
           color: gray;
           font-weight: bold;
           font-size: 1.4em;
         }
         a {
           color: #95A41A;
           white-space: nowrap;
           font-size: 0.8em;
         }
         //-->
         </style>
       </head>
       <body>
         <div align="center">
         <br />
         <br />
         <br />
         <table cellpadding="4" border="0" width="400">
           <tr>
             <th colspan="2">Error in page request!</th>
           </tr>
           <tr valign="top">
             <td align="center"><pre>{%artwork%}</pre></td>
           </tr>
           <tr>
             <th><h3>{%message%}</h3></th>
           </tr>
           <tr>
             <th>{%status%} {%identifier%}</th>
           </tr>
           <tr valign="top">
             <th>
               <hr style="border: none; border-bottom: 1px solid black;">
               <h4><a href="http://{%host%}/">http://{%host%}/</a></h4>
             </th>
           </tr>
         </table>
       </div>
       </body>
     </html>';

    /**
     * @param string $message
     * @param int $status
     * @param null|int|string $identifier
     */
    public function __construct($message, $status, $identifier = NULL) {
      $this->_status = (int)$status;
      $this->_errorMessage = $message;
      $this->_errorIdentifier = $identifier;
    }

    /**
     * @param Router $router
     * @param NULL|object $context
     * @return null|Response
     */
    public function __invoke(Router $router, $context = NULL) {
      $response = new Response();
      $response->setStatus($this->_status);
      $response->setContentType('text/html');
      $response->content(
        new Response\Content\Text($this->_getOutput())
      );
      return $response;
    }

    /**
     * Generate error output
     *
     * @return string
     */
    private function _getOutput() {
      $replace = [
        '{%status%}' => Utility\Text\XML::escape($this->_status),
        '{%artwork%}' => Utility\Text\ASCII\Artwork::get($this->_status),
        '{%identifier%}' => Utility\Text\XML::escape($this->_errorIdentifier),
        '{%message%}' => Utility\Text\XML::escape($this->_errorMessage),
        '{%host%}' => Utility\Text\XML::escape(Utility\Server\Name::get()),
      ];
      return \str_replace(
        \array_keys($replace),
        \array_values($replace),
        $this->_template
      );
    }
  }
}
