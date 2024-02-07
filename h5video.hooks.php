<?php

use MediaWiki\MediaWikiServices;

/**
 * The H5Video extension embeds a video file uploaded to the wiki by creating a
 * html 5 conform video (and nested source) element.
 *
 * The H5VideoHooks class implements the parser extension for the 'video' tag.
 *
 * @author Andreas Schroeder <andreas@a-netz.de>
 */
class H5VideoHooks {

    /** Default (and only accepted) tag attributes / options. */
    private static $def_opts = array(
        'width' => '640',
        'height' => '360',
        'controls' => 'controls',
    );


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
     * Generate markup for HTML5 video player element.
     *
     * At the moment, only one mp4 video file is supported. Multiple files and
     * other formats are not supported.
     *
     * @param string $src: The path / URL to the video file.
     * @param array $opts: The options (HTML attributes and values) to use.
     *
     * @return The html code to embed in the page.
     */
    private static function getHtml5VideoMarkup($src, $attribs) {
        $opts = self::getTagOptions($attribs);

        $html = '<video ' . self::getHtmlOpts($opts) . '>'
            . '<source src="' . $src . '" type="video/mp4" />'
            . '</video>';

        return $html;
    }


    /**
     * Generate markup for error message.
     */
    private static function getErrorMarkup($error) {
        $html = '<p style="color:red;">'
            . '<b>ERROR:</b> '
            . $error
            . '</p>';

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
        $src = trim($src);

        /* Special care for external URLs (starting with http / https), as these
         * should not be converted to html <a href="..."> style links. */
        $res = preg_match('/^https?:\/\/.*$/', $src, $match);
        if($res === 1) {
            $parser->getOutput()->addExternalLink($src);

            return $src;
        }

        /* Parse data, e.g. to support using the video tag in templates (using a
         * template parameter like {{{1}}}). */
        $src = $parser->recursiveTagParse($src, $frame);

        /* Check for file: prefix and resolve its file path. */
        $res = preg_match('/^file:(.*)$/i', $src, $match);
        if($res === 1) {
            $name = $match[1];

            $repogroup = MediaWikiServices::getInstance()->getRepoGroup();
            $file = $repogroup->findFile($name);

            if($file !== false) {
                /* Register file use. */
                $parser->getOutput()->addImage(
                    $file->getTitle()->getDBkey());

                return $file->getFullUrl();
            }
        }

        return NULL;
    }


    /**
     * Calculate application options from tag attributes and default
     * values. Invalid options are filtered out and all missing options are set
     * from default values.
     *
     * @param string $attribs: The tag attributes of the video tag.
     * @return array: All applicable options.
     */
    private static function getTagOptions($attribs) {
        /* Discard all keys which are not in def_opts. Then set all missing
         * keys / values from the default values. */
        $opts = array_intersect_key($attribs, self::$def_opts);
        $opts = array_merge(self::$def_opts, $opts);

        return $opts;
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
    public static function parserTagVideo($data, $attribs, $parser, $frame) {
        $url = self::resolveUrl($data, $parser, $frame);

        if($url == NULL) {
            $msg = wfMessage('invalid-source-error')->plain();
            return self::getErrorMarkup($msg);
        }

        return self::getHtml5VideoMarkup($url, $attribs);
    }
};
