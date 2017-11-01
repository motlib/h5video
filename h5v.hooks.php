<?php

/**
 * The H5v (Html5Video) extension embeds a video file uploaded to the
 * wiki by creating a html 5 conform video element.
 *
 * @author Andreas Schroeder <andreas@a-netz.de>
 */
class H5vHooks {

    /**
     * Register parser hooks.
     * 
     * See http://www.mediawiki.org/wiki/Manual:Parser_functions
     */
    public static function onParserFirstCallInit(&$parser) {
        /* Register parser for video tag and corresponding function to
         * call. */
        $parser->setHook('video', 'H5vHooks::parserTagVideo');

        return true;
    }

    /**
     * Build html attribute string.
     *
     * Very important to escape user data with htmlspecialchars() to
     * prevent an XSS security vulnerability.
     */
    private static function getHtmlOpts($opts) { 
        $result = '';
            
        foreach($opts as $opt => $val) {
            $result .= $opt . '="' . htmlspecialchars($val) . '" ';
        }
        
        return $result;
    }

    
    /**
     * Return html code for HTML5 video player element.
     *
     * At the moment, only mp4 video format is supported.
     */
    private static function getHtml5VideoCode($src, $opts) {
        $html = '<video ' . H5vHooks::getHtmlOpts($opts) . '>'
            . '<source src="' . $src . '" type="video/mp4" />'
            . '</video>';

        return $html;
    }

    /**
     * Convert the source information passed in the video tag to a
     * meaningful URL or NULL.
     *
     * If $src starts with "File:", it tries to locate the file in
     * mediawiki. Else it handles it as a external URL without
     * modification.
     */
    private static function resolveUrl($src) {
        $url = NULL;
        
        if(strtolower(substr($src, 0, 5)) == 'file:') {
            $src = substr($src, 5);
            $file = wfFindFile($src);
            if($file !== false) {
                $url = $file->getFullUrl();
            }
        } else {
            $url = $src;
        }

        return $url;
    }
    
    
    /**
     * Parser hook handler for <video> tag.
     *
     * @param string $data: The content of the tag.
     * @param array $params: The attributes of the tag.
     * @param Parser $parser: Parser instance available to render
     *  wikitext into html, or parser methods.
     * @param PPFrame $frame: Can be used to see what template
     *  arguments ({{{1}}}) this hook was used with.
     *
     * @return string: HTML to insert in the page.
     */
    public static function parserTagVideo( $data, $attribs, $parser, $frame ) {
        
        /* Default (and only accepted) options. */
        $def_opts = array(
            'width' => '640',
            'height' => '360',
            'controls' => 'controls'
        );
        
        /* discard all options which are not in def_opts. */
        $opts = array_intersect_key($attribs, $def_opts);        
        $opts = array_merge($def_opts, $opts);

        /* Parse data to support videos like [[Media:File.mp4]] 
         * Currently not supported. Instead the file / media name is
         * resolved manually. */
        $data = $parser->recursiveTagParse( $data, $frame );

        $url = H5vHooks::resolveUrl($data);
        
        if($url !== NULL) {
            $html = H5vHooks::getHtml5VideoCode($url, $opts);
        } else {
            $html = "<p style=\"color:red;\"><b>ERROR:</b> Media file <tt>$data</tt> not found.</p>";            
        }

        return $html;
    }
};
