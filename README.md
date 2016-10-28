## Import Users

Import Users is an extension to MediaWiki allowing to to import users in bulk from a UTF-8
encoded CSV file.


### Compatibility

* PHP 5.3+
* MediaWiki 1.17.x - 1.26.x

**Note that this extension is not yet compatible with MediaWiki 1.27+.** See also the
CHANGELOG.md file provided with the code.


### Installation

(1) Obtain the code from [GitHub](https://github.com/wikimedia/mediawiki-extensions-ImportUsers/releases)

(2) Extract the files in a directory called `ImportUsers` in your `extensions/` folder.

(3) Add the following code at the bottom of your "LocalSettings.php" file:
```
require_once "$IP/extensions/ImportUsers/ImportUsers";
```
(4) Go to "Special:Version" on your wiki to verify that the extension is successfully installed.

(5) Done.


### Notes

After importing users you might want to run the ["initSiteStats.php"][init] (MediaWiki ≥ 1.21.x)
or the ["initStats.php"][init] (MW ≤ 1.20.x) to update the statistics of your wiki on registered
users.


[init]: https://www.mediawiki.org/wiki/Manual:InitSiteStats.php
