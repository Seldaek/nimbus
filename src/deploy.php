<?php

/**
 * Deployment script
 */

$urls = [];
$controllers = [];
$edges = [];
$baseUrl = 'https://nimbusframework.herokuapp.com/?controller=';

// Build up dependencies
foreach (glob(__DIR__.'/../controllers/*.php') as $ctrl) {
    $controller = [
        'content' => file_get_contents($ctrl),
        'id' => basename($ctrl),
    ];
    preg_match_all('{href="\?controller=([a-z0-9_-]+\.php)"}i', $controller['content'], $matches);
    $matches = $matches[1];

    foreach (array_unique($matches) as $match) {
        $edges[] = [$controller['id'], $match];
    }

    $controllers[$controller['id']] = $controller;
}

$sorted = topological_sort(array_keys($controllers), $edges);
if (null === $sorted) {
    echo 'Invalid link graph, you have cyclic links which are illegal';
    exit(1);
}

echo 'Publishing' . PHP_EOL;

foreach (array_reverse($sorted) as $id) {
    $content = $controllers[$id]['content'];
    $content = preg_replace_callback('{href="\?controller=([a-z0-9_-]+\.php)"}i', function ($m) use ($urls) {
        return 'href="'.$urls[$m[1]].'"';
    }, $content);

    $data = json_encode([
        'longUrl' => $baseUrl . $content,
    ]);

    $context = stream_context_create(['http' =>
        [
            'method'  => 'POST',
            'header'  => "Content-type: application/json\r\nUser-Agent:Nimbus Framework",
            'content' => $data
        ]
    ]);

    $response = file_get_contents('https://www.googleapis.com/urlshortener/v1/url', false, $context);
    $response = json_decode($response, true);
    $urls[basename($id)] = $response['id'];
}

echo 'Published your site to '.$urls['default.php'].PHP_EOL;

// Snatched from http://blog.calcatraz.com/php-topological-sort-function-384
function topological_sort($nodeids, $edges) {
    // initialize variables
    $L = $S = $nodes = array();

    // remove duplicate nodes
    $nodeids = array_unique($nodeids);

    // remove duplicate edges
    $hashes = array();
    foreach($edges as $k=>$e) {
        $hash = md5(serialize($e));
        if (in_array($hash, $hashes)) { unset($edges[$k]); }
        else { $hashes[] = $hash; };
    }

    // Build a lookup table of each node's edges
    foreach($nodeids as $id) {
        $nodes[$id] = array('in'=>array(), 'out'=>array());
        foreach($edges as $e) {
            if ($id==$e[0]) { $nodes[$id]['out'][]=$e[1]; }
            if ($id==$e[1]) { $nodes[$id]['in'][]=$e[0]; }
        }
    }

    // While we have nodes left, we pick a node with no inbound edges,
    // remove it and its edges from the graph, and add it to the end
    // of the sorted list.
    foreach ($nodes as $id=>$n) { if (empty($n['in'])) $S[]=$id; }
    while (!empty($S)) {
        $L[] = $id = array_shift($S);
        foreach($nodes[$id]['out'] as $m) {
            $nodes[$m]['in'] = array_diff($nodes[$m]['in'], array($id));
            if (empty($nodes[$m]['in'])) { $S[] = $m; }
        }
        $nodes[$id]['out'] = array();
    }

    // Check if we have any edges left unprocessed
    foreach($nodes as $n) {
        if (!empty($n['in']) or !empty($n['out'])) {
            return null; // not sortable as graph is cyclic
        }
    }
    return $L;
}
