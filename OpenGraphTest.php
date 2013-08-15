<?php
require 'OpenGraph.php';

class OpenGraphTest extends PHPUnit_Framework_TestCase
{
    public function testFetch()
    {
        $o = OpenGraph::fetch(
          'http://www.rottentomatoes.com/m/10011268-oceans/'
        );

        $this->assertEquals('OpenGraph', get_class($o));

        $this->assertAttributeEquals(
          array(
            'title'     => 'Oceans',
            'type'      => 'movie',
            'image'     => 'http://images.rottentomatoes.com/images/movie/custom/68/10011268.jpg',
            'url'       => 'http://www.rottentomatoes.com/m/10011268-oceans/',
            'site_name' => 'Rotten Tomatoes',
          ),
          '_values',
          $o
        );
    }

    public function testFetchReturnsFalseForWebsiteWithNoOpenGraphMetadata()
    {
        $this->assertEquals(FALSE, OpenGraph::fetch('http://www.example.org/'));
    }

    /**
     * Tests that the public method parse method can work for a url with og data present
     */
    public function testParsePublicMethodSuccess() 
    {
        $curl = curl_init('http://www.rottentomatoes.com/m/10011268-oceans/');

        curl_setopt($curl, CURLOPT_FAILONERROR, true);
        curl_setopt($curl, CURLOPT_FOLLOWLOCATION, true);
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($curl, CURLOPT_TIMEOUT, 15);
        curl_setopt($curl, CURLOPT_SSL_VERIFYHOST, false);
        curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, false);
        //curl_setopt($curl, CURLOPT_USERAGENT, $_SERVER['HTTP_USER_AGENT']);

        $response = curl_exec($curl);

        curl_close($curl);

        $o = OpenGraph::parse($response);
        $this->assertAttributeEquals(
          array(
            'title'     => "Oceans (Disneynature's Oceans)",
            'type'      => 'video.movie',
            'image'     => 'http://content9.flixster.com/movie/11/04/79/11047971_800.jpg',
            'url'       => 'http://www.rottentomatoes.com/m/10011268-oceans/',
            'image:width' => '800',
            'image:height' => '1200',
            'description' => 'Oceans adds another visually stunning chapter to the Disney Nature library.'

          ),
          '_values',
          $o
        );
    }

}
