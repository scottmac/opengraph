<?php
require 'OpenGraph.php';

class OpenGraphTest extends PHPUnit_Framework_TestCase
{
    public function testFetch()
    {
        $o = OpenGraph::fetch(
          'http://www.rottentomatoes.com/m/10011268-oceans/'
        );

        $this->assertType('OpenGraph', $o);

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
}
