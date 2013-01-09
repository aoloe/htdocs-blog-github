htdocs-blog-github
==================

A simple php script publishing articles stored in a github repository.

- get the list of the articles from a github repository
- locally cache the articles and check the list of the files for changes by the sha hash

- metadata is written in yaml (compatibility with jekyll)
  - files without metadata are published as soon as they are uploaded (with the date of first discovering)
  - metatadata without date is not yet published
  - metadata must start with "---" at the beginning of the file and end with a "---"
  - if you want the fields to be shown correctly on github markdown preview, you have to indent the metadata fields by four spaces (preformatted text)
  - the following fields are recognized:
    - date ([y]y.[m]m.dddd[ hh:mm])
    - author
    - tags (tag[,tag[,...]])
- add a switch to rebuild the whole cache from scratch
- the update script can be protected by a weak password (just to avoid that bots consume your precious github hits)

uses mardown extra and spyc
