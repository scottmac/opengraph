<?php
namespace OpenGraph;

/*
  Copyright 2010 Scott MacVicar

   Licensed under the Apache License, Version 2.0 (the "License");
   you may not use this file except in compliance with the License.
   You may obtain a copy of the License at

       http://www.apache.org/licenses/LICENSE-2.0

   Unless required by applicable law or agreed to in writing, software
   distributed under the License is distributed on an "AS IS" BASIS,
   WITHOUT WARRANTIES OR CONDITIONS OF ANY KIND, either express or implied.
   See the License for the specific language governing permissions and
   limitations under the License.
*/

class OpenGraph implements \Iterator {

    /**
     * There are base schema's based on type, this is just
     * a map so that the schema can be obtained

     */
    public static $TYPES = array(
        'activity' => array('activity', 'sport'),
        'business' => array('bar', 'company', 'cafe', 'hotel', 'restaurant'),
        'group' => array('cause', 'sports_league', 'sports_team'),
        'organization' => array('band', 'government', 'non_profit', 'school', 'university'),
        'person' => array('actor', 'athlete', 'author', 'director', 'musician', 'politician', 'public_figure'),
        'place' => array('city', 'country', 'landmark', 'state_province'),
        'product' => array('album', 'book', 'drink', 'food', 'game', 'movie', 'product', 'song', 'tv_show'),
        'website' => array('blog', 'website'),
    );

    /**
     * Holds all the Open Graph values we've parsed from a page
     * @var array
     */
    private $_values = array();

    /**
     * Fetches a URI and parses it for Open Graph data, returns
     * false on error.
     *
     * @param string $URI    URI to page to parse for Open Graph data
     * @param \Buzz\Browser|null $browser
     * @return \OpenGraph\OpenGraph
     */
    static public function fetch($URI, \Buzz\Browser $browser = null) {
        if ($browser === null) {
            $browser = new \Buzz\Browser();
        }
        /** @var \Buzz\Message\Response $response  */
        $response = $browser->get($URI);
        $responseHeaders = $response->getHeaders();

        $charset = null;

        foreach ($responseHeaders as $responseHeader) {
            if (strpos($responseHeader, 'Content-Type') !== 0) {
                continue;
            }
            $matches = array();
            preg_match('/charset=([a-zA-Z0-9\-\_]+)/', $responseHeader, $matches);

            if (isset($matches[1])) {
                $charset = $matches[1];
            }
        }

        return self::_parse($response, $charset);
    }

    /**
     * Parses HTML and extracts Open Graph data, this assumes
     * the document is at least well formed.
     *
     * @param string $HTML    HTML to parse
     * @param string $charset encoding of html page
     * @return \OpenGraph\OpenGraph
     */
    static private function _parse($HTML, $charset) {
        if (!empty($HTML)) {
            if (empty($charset)) {
                $charset = mb_detect_encoding($HTML);
            }
            $headpos = mb_strpos($HTML, '<head>');
            if (false === $headpos) {
                $headpos = mb_strpos($HTML, '<HEAD>');
            }
            if (false !== $headpos) {
                $headpos += 6;
                $HTML = mb_substr($HTML, 0, $headpos) . '<meta http-equiv="Content-Type" content="text/html; charset=' . $charset . '">' . mb_substr($HTML, $headpos);
            }
            $HTML = mb_convert_encoding($HTML, 'HTML-ENTITIES', $charset);
        }

        $old_libxml_error = libxml_use_internal_errors(true);

        $doc = new \DOMDocument();
        $doc->loadHTML($HTML);

        libxml_use_internal_errors($old_libxml_error);

        $tags = $doc->getElementsByTagName('meta');
        if (!$tags || $tags->length === 0) {
            return false;
        }

        $page = new self();

        $nonOgDescription = null;

        foreach ($tags AS $tag) {
            /** @var \DOMElement $tag */
            if ($tag->hasAttribute('property') &&
                strpos($tag->getAttribute('property'), 'og:') === 0
            ) {
                $key = strtr(substr($tag->getAttribute('property'), 3), '-', '_');
                $page->_values[$key] = $tag->getAttribute('content');
            }
            if ($tag->hasAttribute('name') && $tag->getAttribute('name') === 'description') {
                $nonOgDescription = $tag->getAttribute('content');
            }
        }

        if (!isset($page->_values['title'])) {
            $titles = $doc->getElementsByTagName('title');
            if ($titles->length > 0) {
                $page->_values['title'] = $titles->item(0)->textContent;
            }
        }
        if (!isset($page->_values['description']) && $nonOgDescription) {
            $page->_values['description'] = $nonOgDescription;
        }

        if (empty($page->_values)) {
            return false;
        }

        return $page;
    }

    /**
     * Helper method to access attributes directly
     * Example:
     * $graph->title
     *
     * @param string $key    Key to fetch from the lookup
     * @return string|null
     */
    public function __get($key) {
        if (array_key_exists($key, $this->_values)) {
            return $this->_values[$key];
        }

        if ($key === 'schema') {
            foreach (self::$TYPES AS $schema => $types) {
                if (array_search($this->_values['type'], $types)) {
                    return $schema;
                }
            }
        }
    }

    /**
     * Return all the keys found on the page
     *
     * @return array
     */
    public function keys() {
        return array_keys($this->_values);
    }

    /**
     * Helper method to check an attribute exists
     *
     * @param string $key
     * @return bool
     */
    public function __isset($key) {
        return array_key_exists($key, $this->_values);
    }

    /**
     * Will return true if the page has location data embedded
     *
     * @return boolean Check if the page has location data
     */
    public function hasLocation() {
        if (array_key_exists('latitude', $this->_values) && array_key_exists('longitude', $this->_values)) {
            return true;
        }

        $address_keys = array('street_address', 'locality', 'region', 'postal_code', 'country_name');
        $valid_address = true;
        foreach ($address_keys AS $key) {
            $valid_address = ($valid_address && array_key_exists($key, $this->_values));
        }
        return $valid_address;
    }

    /**
     * Iterator code
     * @var int
     */
    private $_position = 0;

    public function rewind() {
        reset($this->_values);
        $this->_position = 0;
    }

    /**
     * @return string
     */
    public function current() {
        return current($this->_values);
    }

    /**
     * @return string
     */
    public function key() {
        return key($this->_values);
    }

    public function next() {
        next($this->_values);
        ++$this->_position;
    }

    /**
     * @return bool
     */
    public function valid() {
        return $this->_position < sizeof($this->_values);
    }
}
