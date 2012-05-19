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

class OpenGraphTest extends \PHPUnit_Framework_TestCase {

    public function testFetch() {
        $o = OpenGraph::fetch(
            'http://www.rottentomatoes.com/m/10011268-oceans/'
        );

        $this->assertInstanceOf('OpenGraph\OpenGraph', $o);

        $this->assertAttributeEquals(
            array(
                'title' => 'Oceans (Disneynature\'s Oceans)',
                'type' => 'video.movie',
                'image' => 'http://content9.flixster.com/movie/11/04/79/11047971_pro.jpg',
                'url' => 'http://www.rottentomatoes.com/m/10011268-oceans/',
                'description' => '<em>Oceans</em> adds another visually stunning chapter to the Disney Nature library.'
            ),
            '_values',
            $o
        );
    }

    public function testFetchParsesFallbacksForWebsiteWithNoOpenGraphMetadata() {
        $o = OpenGraph::fetch(
            'http://www.example.org/'
        );

        $this->assertInstanceOf('OpenGraph\OpenGraph', $o);

        $this->assertAttributeEquals(
            array(
                'title' => 'IANA — Example domains',
            ),
            '_values',
            $o
        );
    }
}
