<?php 
    
# ***** BEGIN LICENSE BLOCK *****
# This file is part of the Typo3 Extension dr_wiki.
# Inital Copyright (c) 2005-2007 Frederic Minne <zefredz@gmail.com>.
/***************************************************************
*  Copyright notice
*
* (c) 2005-2007 Frederic Minne <zefredz@gmail.com>. 
* (c) 2008 Denis Royer <info@indigi.de> --> modification
*  All rights reserved
*
*  This script is part of the Typo3 project. The Typo3 project is
*  free software; you can redistribute it and/or modify
*  it under the terms of the GNU General Public License as published by
*  the Free Software Foundation; either version 2 of the License, or
*  (at your option) any later version.
*
*  The GNU General Public License can be found at
*  http://www.gnu.org/copyleft/gpl.html.
*
*  This script is distributed in the hope that it will be useful,
*  but WITHOUT ANY WARRANTY; without even the implied warranty of
*  MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
*  GNU General Public License for more details.
*
*  This copyright notice MUST APPEAR in all copies of the script!
***************************************************************/
    
/**
 * @author  Denis Royer <info@indigi.de>
 * @copyright Copyright &copy; 2005-2007 Frederic Minne <zefredz@gmail.com>; 2008, Denis Royer
 * @license http://www.gnu.org/copyleft/gpl.html GNU General Public License
 * @version 1.0
 * @package HTML
 */

/**
 * Sanitize HTML body content
 * Remove dangerous tags and attributes that can lead to security issues like
 * XSS or HTTP response splitting
 */
class html_sanitizer
{
    // Private fields
    var $_allowedTags;
    var $_allowJavascriptEvents;
    var $_allowJavascriptInUrls;
    var $_allowObjects;
    var $_allowScript;
    var $_allowStyle;
    var $_additionalTags;     
    var $_disallowedAttributes =  array( 
		'onabort', 
		'onblue', 
		'onchange', 
		'onclick', 
		'ondblclick', 
		'onerror', 
		'onfocus', 
		'onkeydown', 
		'onkeyup', 
		'onload', 
		'onmousedown', 
		'onmousemove', 
		'onmouseover', 
		'onmouseup', 
		'onreset', 
		'onresize', 
		'onselect', 
		'onsubmit', 
		'onunload'
	);
       
    
    /**
     * Constructor
     */
    function html_sanitizer()
    {
        $this->resetAll();
    }
    
    /**
     * (re)set all options to default value
     */
    function resetAll()
    {
        $this->_allowDOMEvents = false;
        $this->_allowJavascriptInUrls = false;
        $this->_allowStyle = false;
        $this->_allowScript = false;
        $this->_allowObjects = false;
        $this->_allowStyle = false;

        $this->_allowedTags = '';
            
        $this->_additionalTags = '';
    }
    
    /**
     * Add allowed tags
     * @param string
     * @access public
     */
    function addAllowedTags( $tags )
    {
        $this->_allowedTags .= $tags;
    }

    /**
     * Add additional tags to allowed tags
     * @param string
     * @access public
     */
    function addAdditionalTags( $tags )
    {
        $this->_additionalTags .= $tags;
    }

    /**
     * Allow object, embed, applet and param tags in html
     * @access public
     */
    function allowObjects()
    {
        $this->_allowObjects = true;
    }
    
    /**
     * Allow DOM event on DOM elements
     * @access public
     */
    function allowDOMEvents()
    {
        $this->_allowDOMEvents = true;
    }
    
    /**
     * Allow script tags
     * @access public
     */
    function allowScript()
    {
        $this->_allowScript = true;
    }
    
    /**
     * Allow the use of javascript: in urls
     * @access public
     */
    function allowJavascriptInUrls()
    {
        $this->_allowJavascriptInUrls = true;
    }
    
    /**
     * Allow style tags and attributes
     * @access public
     */
    function allowStyle()
    {
        $this->_allowStyle = true;
    }
    
    /**
     * Helper to allow all javascript related tags and attributes
     * @access public
     */
    function allowAllJavascript()
    {
        $this->allowDOMEvents();
        $this->allowScript();
        $this->allowJavascriptInUrls();
    }
    
    /**
     * Allow all tags and attributes
     * @access public
     */
    function allowAll()
    {
        $this->allowAllJavascript();
        $this->allowObjects();
        $this->allowStyle();
    }
    
    /**
     * Filter URLs to avoid HTTP response splitting attacks
     * @access  public
     * @param   string url
     * @return  string filtered url
     */
    function filterHTTPResponseSplitting( $url )
    {
        $dangerousCharactersPattern = '~(\r\n|\r|\n|%0a|%0d|%0D|%0A)~';
        return preg_replace( $dangerousCharactersPattern, '', $url );
    }
    
    /**
     * Remove potential javascript in urls
     * @access  public
     * @param   string url
     * @return  string filtered url
     */
    function removeJavascriptURL( $str )
    {
        $HTML_Sanitizer_stripJavascriptURL = 'javascript:[^"]+';

        $str = preg_replace("/$HTML_Sanitizer_stripJavascriptURL/i"
            , ''
            , $str );

        return $str;
    }
    
    /**
     * Remove potential flaws in urls
     * @access  private
     * @param   string url
     * @return  string filtered url
     */
    function sanitizeURL( $url )
    {
        if ( ! $this->_allowJavascriptInUrls )
        {
            $url = $this->removeJavascriptURL( $url );
        }
        
        $url = $this->filterHTTPResponseSplitting( $url );

        return $url;
    }
    
    /**
     * Callback for PCRE
     * @access private
     * @param matches array
     * @return string
     * @see sanitizeURL
     */
    function _sanitizeURLCallback( $matches )
    {
        return 'href="'.$this->sanitizeURL( $matches[1] ).'"';
    }
    
    /**
     * Remove potential flaws in href attributes
     * @access  private
     * @param   string html tag
     * @return  string filtered html tag
     */
    function sanitizeHref( $str )
    {
        $HTML_Sanitizer_URL = 'href="([^"]+)"';

        return preg_replace_callback("/$HTML_Sanitizer_URL/i"
            , array( &$this, '_sanitizeURLCallback' )
            , $str );
    }
    
    /**
     * Callback for PCRE
     * @access private
     * @param matches array
     * @return string
     * @see sanitizeURL
     */
    function _sanitizeSrcCallback( $matches )
    {
        return 'src="'.$this->sanitizeURL( $matches[1] ).'"';
    }
    
    /**
     * Remove potential flaws in href attributes
     * @access  private
     * @param   string html tag
     * @return  string filtered html tag
     */
    function sanitizeSrc( $str )
    {
        $HTML_Sanitizer_URL = 'src="([^"]+)"';

        return preg_replace_callback("/$HTML_Sanitizer_URL/i"
            , array( &$this, '_sanitizeSrcCallback' )
            , $str );
    }
    
    /**
     * Remove dangerous attributes from html tags
     * @access  private
     * @param   string html tag
     * @return  string filtered html tag
     */
    function removeEvilAttributes( $str )
    {
        if ( ! $this->_allowDOMEvents )
        {
            $str = preg_replace_callback('/<(.*?)>/i'
                , array( &$this, '_removeDOMEventsCallback' )
                , $str );
        }
        
        if ( ! $this->_allowStyle )
        {
            $str = preg_replace_callback('/<(.*?)>/i'
                , array( &$this, '_removeStyleCallback' )
                , $str );
        }
            
        return $str;
    }
    
    /**
     * Remove DOM events attributes from html tags
     * @access  private
     * @param   string html tag
     * @return  string filtered html tag
     */
    function removeDOMEvents( $str )
    {
        $str = preg_replace ( '/\s*=\s*/', '=', $str );

        $HTML_Sanitizer_stripAttrib = '('.implode('|',$this->_disallowedAttributes).')';
        
        $str = stripslashes( preg_replace("/$HTML_Sanitizer_stripAttrib/i"
            , 'forbidden'
            , $str ) );

        return $str;
    }
    
    /**
     * Callback for PCRE
     * @access private
     * @param matches array
     * @return string
     * @see removeDOMEvents
     */
    function _removeDOMEventsCallback( $matches )
    {
        return '<' . $this->removeDOMEvents( $matches[1] ) . '>';
    }
    
    /**
     * Remove style attributes from html tags
     * @access  private
     * @param   string html tag
     * @return  string filtered html tag
     */
    function removeStyle( $str )
    {
        $str = preg_replace ( '/\s*=\s*/', '=', $str );

        $HTML_Sanitizer_stripAttrib = '(style)'
            ;

        $str = stripslashes( preg_replace("/$HTML_Sanitizer_stripAttrib/i"
            , 'forbidden'
            , $str ) );

        return $str;
    }
    
    /**
     * Callback for PCRE
     * @access private
     * @param matches array
     * @return string
     * @see removeStyle
     */
    function _removeStyleCallback( $matches )
    {
        return '<' . $this->removeStyle( $matches[1] ) . '>';
    }
    
    /**
     * Remove dangerous HTML tags
     * @access  private
     * @param   string html code
     * @return  string filtered url
     */
    function removeEvilTags( $str )
    {
        $allowedTags = $this->_allowedTags;
        
        if ( $this->_allowScript )
        {
            $allowedTags .= '<script>';
        }
        
        if ( $this->_allowStyle )
        {
            $allowedTags .= '<style>';
        }
        
        if ( $this->_allowObjects )
        {
            $allowedTags .= '<object><embed><applet><param>';
        }
        
        $allowedTags .= $this->_additionalTags;
        
        $str = strip_tags($str, $allowedTags );

        return $str;
    }
    
    /**
     * Sanitize HTML
     *  remove dangerous tags and attributes
     *  clean urls
     * @access  public
     * @param   string html code
     * @return  string sanitized html code
     */
    function sanitize( $html )
    {
        //preserve html comments
        $unique = '23133214jlsdflj235l23j4l-comment-';
        $references = '23133214jlsdflj235l23j4l-ref-';
        $references2 = '23133214jlsdflj235l23j4l-ref2-';
        
        $numMatches = preg_match_all( '/<!.*>/Us', $html, $matches );
        
        $html = str_replace('<references/>', $references, $html);
        $html = str_replace('<references-2col/>', $references2, $html);
        
        for($i=0; $i<$numMatches; $i++){
            $html = str_replace($matches[0][$i], $unique . $i, $html);
        }
        
        $html = $this->removeEvilTags( $html );
        $html = $this->removeEvilAttributes( $html );
        $html = $this->sanitizeHref( $html );
        $html = $this->sanitizeSrc( $html );

		//re-insert html comments
		for($i=0; $i<$numMatches; $i++){
		    $html = str_replace($unique . $i, $matches[0][$i], $html);
		}
        $html = str_replace($references, '<references/>', $html);
        $html = str_replace($references2,'<references-2col/>',  $html);             
        return $html;
    }
}

?>