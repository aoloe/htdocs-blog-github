<?php
ini_set('display_errors', '1');
error_reporting(E_ALL);

function debug($label, $value) {
    echo("<p>$label<br /><pre>".htmlentities(print_r($value, 1))."</pre></p>");
}
// phpinfo();

define('BLOG_CONFIG_PATH', 'config.json');
define('BLOG_HTTP_URL', sprintf('http://%s%s', $_SERVER['SERVER_NAME'], pathinfo($_SERVER['SCRIPT_NAME'], PATHINFO_DIRNAME)));

if (is_file('config.json')) {
    $config = json_decode(file_get_contents(BLOG_CONFIG_PATH), true);
} elseif(is_file('install.php')) {
    header('Location: '.pathinfo($_SERVER['SCRIPT_NAME'], PATHINFO_DIRNAME).'/'.'install.php');
}

// debug('config', $config);

define('BLOG_CONTENT_PATH', $config['data_path'].'content.json');
define('BLOG_LIST_PATH', $config['data_path'].'list.json');
define('BLOG_CACHE_PATH', $config['data_path'].'cache/');
if (is_file(BLOG_CONTENT_PATH) && is_file(BLOG_LIST_PATH)) {
    $content = json_decode(file_get_contents(BLOG_CONTENT_PATH), true);
    $list = json_decode(file_get_contents(BLOG_LIST_PATH), true);
} elseif(is_file('install.php')) {
    header('Location: '.pathinfo($_SERVER['SCRIPT_NAME'], PATHINFO_DIRNAME).'/'.'install.php');
}
$now = time();
// debug('list', $list);
// debug('content', $content);
?>
<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01//EN"
  "http://www.w3.org/TR/html4/strict.dtd">
<html>
<head>
<meta http-equiv="Content-type" content="text/html; charset=utf-8">
<link href='//fonts.googleapis.com/css?family=Quattrocento+Sans:400,700' rel='stylesheet' type='text/css' />
<title><?= $config['title'] ?></title>
<link rel="alternate" type="application/rss+xml" title="Graphicslab" href="<?= BLOG_HTTP_URL ?>/data/blog.rss" />
<style>
    /* @group Normalize */
/*! normalize.css v1.0.1 | MIT License | git.io/normalize */article,aside,details,figcaption,figure,footer,header,intro,hgroup,nav,section,summary{display:block}audio,canvas,video{display:inline-block;*display:inline;*zoom:1}audio:not([controls]){display:none;height:0}[hidden]{display:none}html{font-size:100%;-webkit-text-size-adjust:100%;-ms-text-size-adjust:100%}html,button,input,select,textarea{font-family:sans-serif}body{margin:0}a:focus{outline:thin dotted}a:active,a:hover{outline:0}h1{font-size:2em;margin:.67em 0}h2{font-size:1.5em;margin:.83em 0}h3{font-size:1.17em;margin:1em 0}h4{font-size:1em;margin:1.33em 0}h5{font-size:.83em;margin:1.67em 0}h6{font-size:.75em;margin:2.33em 0}abbr[title]{border-bottom:1px dotted}b,strong{font-weight:bold}blockquote{margin:1em 40px}dfn{font-style:italic}mark{background:#ff0;color:#000}p,pre{margin:1em 0}code,kbd,pre,samp{font-family:monospace,serif;_font-family:'courier new',monospace;font-size:1em}pre{white-space:pre;white-space:pre-wrap;word-wrap:break-word}q{quotes:none}q:before,q:after{content:'';content:none}small{font-size:80%}sub,sup{font-size:75%;line-height:0;position:relative;vertical-align:baseline}sup{top:-0.5em}sub{bottom:-0.25em}dl,menu,ol,ul{margin:1em 0}dd{margin:0 0 0 40px}menu,ol,ul{padding:0 0 0 40px}nav ul,nav ol{list-style:none;list-style-image:none}img{border:0;-ms-interpolation-mode:bicubic}svg:not(:root){overflow:hidden}figure{margin:0}form{margin:0}fieldset{border:1px solid #c0c0c0;margin:0 2px;padding:.35em .625em .75em}legend{border:0;padding:0;white-space:normal;*margin-left:-7px}button,input,select,textarea{font-size:100%;margin:0;vertical-align:baseline;*vertical-align:middle}button,input{line-height:normal}button,html input[type="button"],input[type="reset"],input[type="submit"]{-webkit-appearance:button;cursor:pointer;*overflow:visible}button[disabled],input[disabled]{cursor:default}input[type="checkbox"],input[type="radio"]{box-sizing:border-box;padding:0;*height:13px;*width:13px}input[type="search"]{-webkit-appearance:textfield;-moz-box-sizing:content-box;-webkit-box-sizing:content-box;box-sizing:content-box}input[type="search"]::-webkit-search-cancel-button,input[type="search"]::-webkit-search-decoration{-webkit-appearance:none}button::-moz-focus-inner,input::-moz-focus-inner{border:0;padding:0}textarea{overflow:auto;vertical-align:top}table{border-collapse:collapse;border-spacing:0}
/* end of normalize.css */
/* @end */


/* Colors */

body,html {
    /*  font-family: "PT Serif";*/
    font-family: 'Quattrocento Sans', 'PT Sans', 'Helvetica Neue', Arial, sans-serif;
}

/* @group Basics */

img {
    display: block;
    max-width: 100%;
    height: auto;
}

/* @group Header */

.intro
{   
    margin-bottom: 0.7em;
}

.intro .inside {
}

/* @end */


.header .intro
{   
    box-shadow: 0 0 12px #b5b39c;
}

.item-inside {
    margin: 1em;
    box-shadow: 0 0 12px #b5b39c;
}

.header,
.intro,
.item-inside
 {
     background-color: #f7f8f4;
 }

/* replicate the simplecms header */

.header {
    background-color: #d9dcce;
    height:80px;
    border-bottom:#585750 1px solid;
}
header {
    background-color: #f7f8f4;
  padding-bottom:20px;
}

header #logo {
    position:absolute;
    top:20px;
    left:0;
    font-size:35px;
    white-space:nowrap;
    color:#585750;
    font-family: 'Yanone Kaffeesatz', arial, helvetica, sans-serif;
    text-transform:uppercase;
    text-shadow: 1px 1px 0px rgba(255,255,255, .4);
}
header #logo:link, 
header #logo:visited, 
header #logo:hover, 
header #logo:focus {
    text-decoration:none;
}
header nav {
    position:absolute;
    top:30px;
    right:0;
    text-shadow: 1px 1px 0px rgba(0,0,0, .3);
}
header nav ul {
    list-style:none;    
    float:right;
}   
header nav li {
    display:block;
    float:left;
    margin:0 0 0 10px;
}
header nav li a {
    display:block;
    font-size:13px;
    padding:5px 15px;
    text-transform:uppercase;
    font-weight:bold;
}
header nav li a:link, 
header nav li a:visited {
    color:#585750;
    color:#eee;
    text-decoration:none;
}   
header nav li a:hover, 
header nav li a:focus {
    color:#585750;
    color:#fff;
    text-decoration:none;
}
header nav li.current a {
    color:#585750;
    color:#fff;
    background:#7096B6;
    background:rgba(255,255,255,.2);
    text-decoration:none;
    border-radius:40px;
    -moz-border-radius:40px;
    -khtml-border-radius:40px;
    -webkit-border-radius:40px;
}


/* blue.css */
header,
.intro {
    background-color: #f7f8f4;
}

.item-inside
 {
    background-color: #f7f8f4;
    box-shadow: 0 0 21px #0c3c68;
}



/* blue.css */
header,
.intro {
    background-color: #f7f8f4;
}

.item-inside
 {
    background-color: #f7f8f4;
    box-shadow: 0 0 21px #0c3c68;
}

.header {
        background: -moz-linear-gradient(top, #6B94B4 0%, #316594 100%);
        background: -webkit-gradient(linear, left top, left bottom, color-stop(0%,#6B94B4), color-stop(100%,#316594)); 
        filter: progid:DXImageTransform.Microsoft.gradient( startColorstr='#6B94B4', endColorstr='#316594',GradientType=0 );
}

.header a {
    color: #eee;
}
</style>
</head>
<body>
<div class="container">  <header>
<div class="header">
<div class="wrapper">

<!-- logo/sitename -->
<a href="http://impagina.org/" id="logo" ><img src="http://impagina.org/theme/Innovation/assets/images/impagina_logo.png" /></a>

<!-- main navigation -->
<nav id="main-nav">

<ul>
<li><a href="http://impagina.org/" title="Resources for the Scribus contributors">Home</a></li>
<li class="current blog"><a href="http://impagina.org/blog/" title="Blog">Blog</a></li>
<li class="planet"><a href="http://impagina.org/planet/" title="Planet">Planet</a></li>
<li class="usability"><a href="http://impagina.org/usability/" title="Scribus UX / UI Design">Usability</a></li>
<li class="futuretools"><a href="http://impagina.org/future-tools/" title="Future tools">Future tools</a></li>
<li class="about"><a href="http://impagina.org/about/" title="About">About</a></li>
<li class="contact"><a href="http://impagina.org/contact/" title="Contact">Contact</a></li>
</ul>

</nav>
</div>
</div>

</header>     
<h1><?= $config['title'] ?></h1>
<?php if (!empty($content)) : ?>
<?php foreach($list as $key => $value) : ?>
<?php if (array_key_exists($key, $content) && ($value != '') && ($value <= $now)) : ?>
<?= file_get_contents(BLOG_CACHE_PATH.$content[$key]['html_name']); ?>
<?php endif; ?>
<?php endforeach; ?>
<?php endif; // content ?>
</div>
</body>
</html>
