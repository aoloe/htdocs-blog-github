#htdocs-blog-github

A simple php script publishing articles stored in a GitHub repository.

##Features

- Get the list of the articles from a github repository
- Locally cache the articles as html files
- Update the cache on demand
- Only the pages that have been changed are updated (the SHA hash gets checked)
- RSS feed
- It only works with GitHub (for now) since it uses GitHub's API.

##Writing and publishing articles

- Add the markdown files to your repository
- You can edit them on your computer and push them to GitHub or edit them directly in the GitHub's web interface.
- Metadata is written in yaml (compatibility with jekyll)
  - files without metadata are or without a date are not published;
  - metadata must start with "---" at the beginning of the file and end with a "---"
  - if you want the fields to be shown correctly on github markdown preview, you have to indent the metadata fields by four spaces (preformatted text)
  - the following fields are recognized:
    - date ([y]y.[m]m.dddd[ hh:mm])
    - author
    - tags (tag[,tag[,...]])

##Install

- Download the files from this repository
- Unpack / upload them to your server

##Copyright and Credits

copyright (c) 2013, Ale Rimoldi, except where otherwise mentioned.
All rights reserved.

Redistribution and use in source and binary forms, with or without modification, are permitted provided that the following conditions are met:

1. Redistributions of source code must retain the above copyright notice, this list of conditions and the following disclaimer.
2. Redistributions in binary form must reproduce the above copyright notice, this list of conditions and the following disclaimer in the documentation and/or other materials provided with the distribution.

THIS SOFTWARE IS PROVIDED BY THE COPYRIGHT HOLDERS AND CONTRIBUTORS "AS IS" AND ANY EXPRESS OR IMPLIED WARRANTIES, INCLUDING, BUT NOT LIMITED TO, THE IMPLIED WARRANTIES OF MERCHANTABILITY AND FITNESS FOR A PARTICULAR PURPOSE ARE DISCLAIMED. IN NO EVENT SHALL THE COPYRIGHT HOLDER OR CONTRIBUTORS BE LIABLE FOR ANY DIRECT, INDIRECT, INCIDENTAL, SPECIAL, EXEMPLARY, OR CONSEQUENTIAL DAMAGES (INCLUDING, BUT NOT LIMITED TO, PROCUREMENT OF SUBSTITUTE GOODS OR SERVICES; LOSS OF USE, DATA, OR PROFITS; OR BUSINESS INTERRUPTION) HOWEVER CAUSED AND ON ANY THEORY OF LIABILITY, WHETHER IN CONTRACT, STRICT LIABILITY, OR TORT (INCLUDING NEGLIGENCE OR OTHERWISE) ARISING IN ANY WAY OUT OF THE USE OF THIS SOFTWARE, EVEN IF ADVISED OF THE POSSIBILITY OF SUCH DAMAGE.

- Uses [Mardown Extra](http://michelf.ca/projects/php-markdown/extra/)
- Uses [spyc](spyc.sf.net)

##TODO

- Remove the layout for impagina.org from index.php (put a template in view/)
- Manage images uploads
- Allow comments
- Paginate the blog
- Add a switch to rebuild the whole cache from scratch
- The update (and install?) script should be protected by a (weak and simple) password (avoid that bots consume your precious GitHub hits)
- Eventually add support geshi for Geshi
