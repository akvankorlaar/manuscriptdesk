# The Manuscript Desk

**The programs and files are still incomplete (work in progress) , and so installing these extensions is not recommended yet.**

The Manuscript Desk builds on [Mediawiki Software](https://www.mediawiki.org/wiki/MediaWiki) and MediaWiki extensions created for the [Transcribe Bentham Project](http://blogs.ucl.ac.uk/transcribe-bentham/).
Additionally, [Collatex](http://collatex.net/) is used. 

Within the Manuscript Desk, images of manuscript pages can be uploaded, and transcribed using a TEI-Editor. Also, collections containing several manuscript pages can be created, and texts can be collated. 

##Requirements##

The extensions have been tested using Mediawiki 1.23.2 and 1.23.10. Later versions will probably also work, but compatibility with these has yet to be tested. Running these extensions
on older versions of MediaWiki is not recommended. 

Additionally, your server needs to have: 

* Perl with the ImageMagick Module enabled.
* Apache to be able to write to directories.

And ofcourse all the other requirements needed for MediaWiki.See: https://www.mediawiki.org/wiki/Manual:Installation_requirements.

##Installation##

Full installation instructions for MediaWiki can be found here: https://www.mediawiki.org/wiki/Manual:Installation_guide.

**Short instructions:** 

Before installing MediaWiki, you should already have a full web-framework (Apache, MySQL, PHP, Perl, ImageMagick) installed.
When this is done, you should download MediaWiki version 1.23 from https://github.com/wikimedia/mediawiki/tree/REL1_23

The MediaWiki files should be extracted to a subdirectory of your website root (for example /w).

You should point your browser to the directory where you have extracted MediaWiki, and follow the on-screen instructions.

**Important:**

During the on-screen instructions you have to:

- Enable the WikiEditor extension.
- Enable file uploads.

For the other settings the default options can be used.

###Installing the extensions###

Once MediaWiki has successfully installed (you should see the default website when navigating to it with your browser),
you can install the extensions by: 

- Moving the content in w/extensions into your local MediaWiki's extensions folder (for example w/extensions).
- Moving the content in w/skins into your local MediaWiki's skins folder (for example w/skins).
- Moving the initialUpload and zoomImages folders to your website root.
- Moving the .htaccess file to your website root (NOT the .htaccess files in the initialUpload and zoomImages folders - these should stay in the initialUpload and zoomImages folders).
- Importing the CollateTable.sql, TempcollateTable.sql, ManuscriptsTable.sql and CollectionsTable.sql into your local MediaWiki's database (there should already be many tables in this database 
  after MediaWiki's default installation. These should remain there).
- Going to localsettings.php located in the root of your MediaWiki installation (for example w/localsettings.php), and by appending all code in exampleConf.php (except for the php tag) to this file.
- Configuring the variables $wgWebsiteRoot, $wgPrimaryDisk in localsettings.php.
- Configuring $wgNewManuscriptOptions['perl_path'] if needed. If you can call perl scripts from the command line by just typing 'perl path/to/a/perl/file.pl', the default 'perl'.
should be used.
- Installing CollateX. This can be done by downloading and installing the latest version from:http://collatex.net/.
- CollateX can be configured by modifying $wgCollationOptions['collatex_url'] in localsettings.php.

Once everything is installed, you should log in as administrator, go to Special:UserRights (for example: localhost/md/Special:UserRights), enter your own username, and add yourself to the 'ManuscriptEditors' group. 
With the default installation, every registered user needs to be added manually to this group to be able to access the functionality of the ManuscriptDesk. 
If you want to change this, you can reconfigure the section on User Permissions in localSettings.php. See https://www.mediawiki.org/wiki/Manual:User_rights

##Technical documentation##

The extensions are largely built according to the recommended structure for MediaWiki extensions. See [the manual for developing extensions]
(https://www.mediawiki.org/wiki/Manual:Developing_extensions) or see the [example extension](https://github.com/wikimedia/mediawiki-extensions-examples/tree/master/Example).

The MediaWiki website is also the main source for information on, for example, [hooks](https://www.mediawiki.org/wiki/Manual:Hooks), 
[database access](https://www.mediawiki.org/wiki/Manual:Database_access), [user rights](https://www.mediawiki.org/wiki/Manual:User_rights), and
specific classes such as the [User class](https://www.mediawiki.org/wiki/Manual:User.php).


##License##

The Manuscript Desk is open-sourced software licensed under the [GNU license](http://www.gnu.org/licenses/gpl-3.0.en.html). 