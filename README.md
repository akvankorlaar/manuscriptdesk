# manuscriptdesk

**The programs and files are still incomplete (work in progress) , and so installing these extensions is not recommended yet.**

The Manuscript Desk builds on [Mediawiki Software](https://www.mediawiki.org/wiki/MediaWiki) and MediaWiki extensions created for the [Transcribe Bentham Project](http://blogs.ucl.ac.uk/transcribe-bentham/).
Additionally, [Collatex](http://collatex.net/) is used. 

Within the Manuscript Desk, images of manuscript pages can be uploaded, and transcribed using a TEI-Editor. Also, collections containing several manuscript pages can be created, and texts can be collated. 

##Requirements##

The extensions have been tested using Mediawiki 1.23.2. Later versions will probably also work, but compatibility with these has yet to be tested. Running these extensions
on older versions of MediaWiki is not recommended. 

Additionally, your server needs to have: 

* Perl with the ImageMagick Module enabled
* Apache to be able to write to directories
* PHP >= 5.6.3

And ofcourse all the other requirements needed for MediaWiki.

##Technical documentation##

The extensions are largely built according to the recommended structure for MediaWiki extensions. See [the manual for developing extensions]
(https://www.mediawiki.org/wiki/Manual:Developing_extensions) or see the [example extension](https://github.com/wikimedia/mediawiki-extensions-examples/tree/master/Example).

The MediaWiki website is the main source for information on, for example, [hooks](https://www.mediawiki.org/wiki/Manual:Hooks), 
[database access](https://www.mediawiki.org/wiki/Manual:Database_access), [user rights](https://www.mediawiki.org/wiki/Manual:User_rights), and
specific classes such as the [User class](https://www.mediawiki.org/wiki/Manual:User.php).
