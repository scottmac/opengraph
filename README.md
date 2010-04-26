# Open Graph Protocol helper for PHP

A small library for making accessing of Open Graph Protocol data easier

## Note
Keys with a dash (-) in the name are converted to _ for easy access as a property
in PHP

## Required Extensions
* DOM for parsing

## Usage
	require_once('OpenGraph.php');

	$graph = OpenGraph::fetch('http://www.rottentomatoes.com/m/10011268-oceans/');
	var_dump($graph->keys());
	var_dump($graph->schema);

	foreach ($graph as $key => $value) {
		echo "$key => $value";
	}