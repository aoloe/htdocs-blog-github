#htdocs-blog-github

A set of three simple PHP script for publishing articles stored in a GitHub repository.

You create and edit your article in GitHub's web interface or push them from your computer to your GitHub account.
The PHP script will get the articles from your GitHub account without using git.

##Features

- Get the list of the articles from your GitHub repository.
- Get the articles formatted with markdown, convert the to HTML and cache them on your webserver.
- Update the cache on demand.
- Only the pages that have been changed are updated (the SHA hash gets checked).
- When updating the cache, a RSS feed is generated.
- It only works with GitHub (for now) since it uses GitHub's API.
- You don't need git to be installed on your webserver.

##Installing and Configuring

- Download the blog PHP scripts from https://github.com/aoloe/htdocs-blog-github .
- Unpack / upload them to your server.
- Create a "data/" directory and make it writable by your webserver.
- Run the install.php script and fill the form with the values corresponding to your GitHub repository and your ­ future ­ blog (you can rerung the install script at any time and modify the values or directly edit the config.json file).
- If you want to ­ and you probably should ­ customize the look and feel, you can add the following templates to the "view/ direcotry:
  - view/template_header.html (supports the variables $title, $blog_http_url),
  - view/template_item.html (supports the variables $title, $author, $date, $tags, $content),
  - view/template_article.html (supports the variable $content),
  - view/template_footer.html.

##Writing and publishing articles

- Add the markdown files to your repository.
- You can edit them on your computer and push them to GitHub or edit them directly in the GitHub's web interface.
- Metadata is written in yaml (compatibility with jekyll):
  - Files without metadata or without a date are not published.
  - Metadata must start with "---" at the beginning of the file and end with a "---".
  - If you want the fields to be shown correctly on github markdown preview, you have to indent the metadata fields by four spaces (preformatted text).
  - The following fields are recognized:
    - date ([y]y.[m]m.dddd[ hh:mm]),
    - author,
    - tags (tag[,tag[,...]]).
- Each time you want to update the content of your blog, simply run the "update.php" script.
- Articles are only shown if their date is in the past.

##Copyright and Credits

copyright (c) 2013, Ale Rimoldi, except where otherwise mentioned.
All rights reserved.

Redistribution and use in source and binary forms, with or without modification, are permitted provided that the following conditions are met:

1. Redistributions of source code must retain the above copyright notice, this list of conditions and the following disclaimer.
2. Redistributions in binary form must reproduce the above copyright notice, this list of conditions and the following disclaimer in the documentation and/or other materials provided with the distribution.

THIS SOFTWARE IS PROVIDED BY THE COPYRIGHT HOLDERS AND CONTRIBUTORS "AS IS" AND ANY EXPRESS OR IMPLIED WARRANTIES, INCLUDING, BUT NOT LIMITED TO, THE IMPLIED WARRANTIES OF MERCHANTABILITY AND FITNESS FOR A PARTICULAR PURPOSE ARE DISCLAIMED. IN NO EVENT SHALL THE COPYRIGHT HOLDER OR CONTRIBUTORS BE LIABLE FOR ANY DIRECT, INDIRECT, INCIDENTAL, SPECIAL, EXEMPLARY, OR CONSEQUENTIAL DAMAGES (INCLUDING, BUT NOT LIMITED TO, PROCUREMENT OF SUBSTITUTE GOODS OR SERVICES; LOSS OF USE, DATA, OR PROFITS; OR BUSINESS INTERRUPTION) HOWEVER CAUSED AND ON ANY THEORY OF LIABILITY, WHETHER IN CONTRACT, STRICT LIABILITY, OR TORT (INCLUDING NEGLIGENCE OR OTHERWISE) ARISING IN ANY WAY OUT OF THE USE OF THIS SOFTWARE, EVEN IF ADVISED OF THE POSSIBILITY OF SUCH DAMAGE.

- Uses [Mardown Extra](http://michelf.ca/projects/php-markdown/extra/)
- Uses [spyc](http://spyc.sf.net)

##TODO

- Manage images uploads.
- Allow comments.
- Paginate the blog.
- The update (and install?) script should be protected by a (weak and simple) password (avoid that bots consume your precious GitHub hits).
- Eventually add support for Geshi (and nicely format code snippets).
- Respect the publishing date for the rss feed (currently, the date is checked when the RSS feed is generated and not when it is requested).
- Find a way to remove the articles that have been deleted from the repository (without risking that everything gets deleted if GitHub is down...).
