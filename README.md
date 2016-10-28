## Import Users

Import Users is an extension to MediaWiki allowing to to import users in bulk from a UTF-8
encoded CSV file.


### Compatibility

* PHP 5.4+
* MediaWiki 1.23+

See also the CHANGELOG.md file provided with the code.


### Installation

(1) Obtain the code from [GitHub](https://github.com/wikimedia/mediawiki-extensions-ImportUsers/releases)

(2) Extract the files in a directory called `ImportUsers` in your `extensions/` folder.

(3) Add the following code at the bottom of your "LocalSettings.php" file:
```
require_once "$IP/extensions/ImportUsers/ImportUsers.php";
```
(4) Go to "Special:Version" on your wiki to verify that the extension is successfully installed.

(5) Done.


### Notes

After importing users you might want to run the "initSiteStats.php" maintenance script [0] to update
the statistics of your wiki on registered users.


[0] https://www.mediawiki.org/wiki/Manual:InitSiteStats.php
