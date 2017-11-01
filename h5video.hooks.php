<?php

/**
 * The H5Video extension embeds a video file uploaded to the wiki by creating a
 * html 5 conform video (and nested source) element.
 *
 * The H5VideoHooks class implements the according parser extension. 
 *
 * @author Andreas Schroeder <andreas@a-netz.de>
 */
class H5VideoHooks {

    /**
     * Register the parser hooks.
     * 
     * See http://www.mediawiki.org/wiki/Manual:Parser_functions
     */
    public static function onParserFirstCallInit(&$parser) {
        /* Register parser for video tag and corresponding function to call. */
        $parser->setHook('video', 'H5VideoHooks::parserTagVideo');

        return true;
    }

    /**
     * Build html attribute string.
     *
     * Escape user data with htmlspecialchars() to prevent a XSS security
     * vulnerability.
     *
     * @param array $opts: A key value array with the attribute data.
     *
     * @return A string in the form key1="value1" key2="value2"... 
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
     * At the moment, only one mp4 video file is supported. Multiple files and
     * other formats are not supported.
     *
     * @param string $src: The path / URL to the video file.
     * @param array $opts: The options (HTML attributes and values) to use.
     *
     * @return The html code to embed in the page.
     */
    private static function getHtml5VideoCode($src, $opts) {
        $html = '<video ' . H5VideoHooks::getHtmlOpts($opts) . '>'
            . '<source src="' . $src . '" type="video/mp4" />'
            . '</video>';

        return $html;
    }

    
    /**
     * Convert the media source information passed in the video tag to a
     * meaningful URL.
     *
     * If $src starts with "File:", it tries to locate the file in
     * mediawiki. Else it handles it as a external URL without
     * modification.
     *
     * @param string $src: The media source, i.e. content of the video tag.
     * @param Parser $parser: The parser object, used to render wiki code.
     * @param PPFrame $frame: Used in conjunction with $parser to render wiki
     *   code.
     *
     * @return string: The URL or NULL, if there is no matching file in the
     *   wiki.
     */
    private static function resolveUrl($src, $parser, $frame) {
        $url = NULL;
        
        /*
         * Parse data, e.g. to support usign the video tag in templates
         * (using a template parameter like {{{1}}}).
         *
         * Special care for external URLs, as these should not be converted to
         * links by the wiki.
         */
        if( (strtolower(substr($src, 0, 7)) != 'http://')
            && (strtolower(substr($src, 0, 8)) != 'https://')) {
            $src = $parser->recursiveTagParse($src, $frame);
        }

        /* Check for file: prefix and resolve its file path. */
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

        $url = H5VideoHooks::resolveUrl($data, $parser, $frame);
        
        if($url !== NULL) {
            $html = H5VideoHooks::getHtml5VideoCode($url, $opts);
        } else {
            $html = '<p style="color:red;"><b>ERROR:</b> '
                . "Media file <tt>$data</tt> not found."
                . '</p>';
        }

        return $html;
    }
};
