<?php

ini_set('display_errors', '1');
error_reporting(E_ALL);

// phpinfo();
function debug($label, $value) {
    echo("<p>$label<br /><pre>".htmlentities(print_r($value, 1))."</pre></p>");
}
function get_content_from_github($url) {
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
    curl_setopt($ch, CURLOPT_TIMEOUT, 30);
    // curl_setopt($ch, CURLOPT_HEADER, true);
    // curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 1);
    // curl_setopt($ch, CURLOPT_VERBOSE, true);
    $content = curl_exec($ch);
    // debug('curl getinfo', curl_getinfo($ch));
    curl_close($ch);
    return $content;
}

$url = "https://raw.github.com/aoloe/htdocs-xox/master/test.html";
if (false) {
    // $url = "http://tagesanzeiger.ch";
    $url = "https://api.github.com/users/aoloe"; // does not work
    $infos = get_content_from_github($url);
    debug('get_content_from_github', $infos);
}
if (false) {
    // $url = "http://tagesanzeiger.ch";
    $url = "https://api.github.com/users/aoloe"; // does not work
    $infos = get_content_from_github($url);
    debug('get_content_from_github', $infos);
}
if (false) {
    $url = "https://api.github.com/users/aoloe/repos"; // list of repos
    $repos = json_decode(get_content_from_github($url));
    debug('get_content_from_github', $repos[2]);
    $url = "https://api.github.com/users/aoloe/htdocs-xox/contents/";
    debug('get_content_from_github', json_decode(get_content_from_github($url)));
}
if (false) {
    $url = "https://api.github.com/users/aoloe?rate_limit"; // general infomrmation about the account (nothing really interesting for now)
    $repos = json_decode(get_content_from_github($url));
    debug('get_content_from_github', json_decode(get_content_from_github($url)));
}
if (false) {
    $url= "https://api.github.com/repos/aoloe/htdocs-xox/git/trees/0f9429f9868ea4b0d3ee674bc8b6a6a83ab90ea0";
    // GET /repos/:owner/:repo/git/trees/:sha
    // $url = "https://api.github.com/users/aoloe/git/trees/";
    $tree = json_decode(get_content_from_github($url));
    debug('tree', $tree);
}
if (true) {
    $url= "https://api.github.com/repos/aoloe/htdocs-xox/contents/"; // gets the list of files in the directory
    // GET /repos/:owner/:repo/contents/:path
    $contents = json_decode(get_content_from_github($url));
    debug('contents', $contents);
}



/* gets the contents of a file if it exists, otherwise grabs and caches */
function get_repo_json($file,$plugin) {
  //vars
  $current_time = time(); $expire_time = 24 * 60 * 60; $file_time = filemtime($file);
  //decisions, decisions
  if(file_exists($file) && ($current_time - $expire_time < $file_time)) {
    //echo 'returning from cached file';
    return json_decode(file_get_contents($file));
  }
  else {
    $json = array();
    $json['repo'] = json_decode(get_content_from_github('http://github.com/api/v2/json/repos/show/darkwing/'.$plugin),true);
    $json['commit'] = json_decode(get_content_from_github('http://github.com/api/v2/json/commits/list/darkwing/'.$plugin.'/master'),true);
    $json['readme'] = json_decode(get_content_from_github('http://github.com/api/v2/json/blob/show/darkwing/'.$plugin.'/'.$json['commit']['commits'][0]['parents'][0]['id'].'/Docs/'.$plugin.'.md'),true);
    $json['js'] = json_decode(get_content_from_github('http://github.com/api/v2/json/blob/show/darkwing/'.$plugin.'/'.$json['commit']['commits'][0]['parents'][0]['id'].'/Source/'.$plugin.'.js'),true);
    file_put_contents($file,json_encode($json));
    return $json;
  }
}
