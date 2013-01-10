<?php

ini_set('display_errors', '1');
error_reporting(E_ALL);

function debug($label, $value) {
    echo("<p>$label<br /><pre>".htmlentities(print_r($value, 1))."</pre></p>");
}

$field = array(
    'github_user' => 'GitHub user',
    'github_repository' => 'GitHub repository',
    'github_path' => 'GitHub path',
    'title' => 'Title',
    'author' => 'Default author',
    'data_path' => 'Data path',
    'description' => 'Description',
    'contact' => 'Contact Email',
);

$config = array_fill_keys(array_keys($field), '');
$config['data_path'] = 'data/';

// debug('_REQUEST', $_REQUEST);
if (array_key_exists('install', $_REQUEST)) {

    $error = array();

    foreach ($config as $key => $value) {
        if (array_key_exists($key, $_REQUEST)) {
            $config[$key] = $_REQUEST[$key];
        }
    }
    if ($config['data_path'] == '') {
        $config['data_path'] = 'data/';
    }
    $config['data_path'] = rtrim($config['data_path'], '/').'/';
    $config['github_repository'] = rtrim($config['github_repository'], '/');
    $config['github_path'] = rtrim($config['github_path'], '/');
    // GET /repos/:owner/:repo/contents/:path
    // https://api.github.com/repos/aoloe/htdocs-blog-xox/contents/text
    $config['github_url'] = sprintf("https://api.github.com/repos/%s/%s/contents/%s", $config['github_user'], $config['github_repository'], $config['github_path']);
    $config['github_url_raw'] = sprintf("https://raw.github.com/%s/%s/master/", $config['github_user'], $config['github_repository']);

    // debug('config', $config);

    foreach (array('github_user', 'github_repository') as $item) {
        if ($config[$item] == '') {
            $error[] = $field[$item].' is mandatory';
        }
    }

    if (empty($error)) {
        define('BLOG_CONFIG_PATH', 'config.json');
        define('BLOG_CONTENT_PATH', $config['data_path'].'content.json');
        define('BLOG_LIST_PATH', $config['data_path'].'list.json');
        define('BLOG_RSS_PATH', $config['data_path'].'blog.rss');
        define('BLOG_CACHE_PATH', $config['data_path'].'cache/');

        if (!is_dir(BLOG_CACHE_PATH)) {
            if (!@mkdir(BLOG_CACHE_PATH, 0777)) {
                $error[] = "the ".BLOG_CACHE_PATH." directory does not exist and could not be created";
            }
        }
        foreach (array(BLOG_CONTENT_PATH, BLOG_LIST_PATH, BLOG_CONFIG_PATH, BLOG_RSS_PATH) as $item) {
            if (!is_file($item)) {
                if (!@touch($item)) {
                    $error[] = "the ".$item." file does not exist and could not be created";
                }
            } else if (!is_writable($item)) {
                $error[] = "the ".$item." file is not writable";
            }
        }
    }
    if (empty($error)) {
        file_put_contents("config.json", json_encode($config));
    }
}
// if a config file exists
if (empty($_REQUEST) && empty($error) && ($config['github_user'] == '') && file_exists("config.json")) {
    $config = json_decode(file_get_contents("config.json"), true);
}
// debug('config', $config);

?>
<html>
<head>
<title>Install</title>
<style>
body {font-family:sans;}
label {display:block;}
input.input {width:400px;}
.error {color:darkred;}
</style>
</head>
<body>
<h1>Install</h1>
<?php
if (!empty($error)) {
    foreach ($error as $item) {
        echo('<p class="error">'.$item."</p>\n");
    }
}
?>
<?php if (empty($error) && $config['github_user'] != '') : ?>
<p>When you're finished with the configuration, you can <a href="update.php">pull the blog articles from your repository</a></p>
<?php endif; ?>
<form method="post">
<?php foreach($field as $key => $value) : ?>
<p><label for="<?= $key ?>"><?= $value ?></label><input type="text" name="<?= $key ?>" <?= $config[$key] == '' ? '' : ' value="'.$config[$key].'"' ?> id="<?= $key ?>" class="input" /></p>
<?php endforeach; ?>
<p><input type="submit" name="install" value="install" /></p>
</form>
</body>
</html>
