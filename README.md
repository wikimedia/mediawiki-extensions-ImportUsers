## Import Users

Import Users is an extension to MediaWiki allowing to to import users in bulk from a UTF-8
encoded CSV file.


### Compatibility

* PHP 5.4+
* MediaWiki 1.25+

See also the CHANGELOG.md file provided with the code.


### Installation

(1) Obtain the code from [GitHub](https://github.com/wikimedia/mediawiki-extensions-ImportUsers/releases)

(2) Extract the files in a directory called `ImportUsers` in your `extensions/` folder.

(3) Add the following code at the bottom of your "LocalSettings.php" file:
```
wfLoadExtension( 'ImportUsers' );
```
(4) Configure if desired. See the "Configuration" section below.

(5) Go to "Special:Version" on your wiki to verify that the extension is successfully installed.

(6) Done.


### Configuration

By default the `import_users` right provided by this extension is assigned to the "bureaucrat" user group.
In case you would like to assign the right to an additional user group, e.g. "sysop" add the following line
to your "LocalSettings.php" file after the inclusion of the extension as described in the "Installation"
section above:

```
$wgGroupPermissions['sysop']['import_users'] = true;
```

### Notes

After importing users you might want to run the "initSiteStats.php" maintenance script [0] to update
the statistics of your wiki on registered users.

[0] https://www.mediawiki.org/wiki/Manual:InitSiteStats.php
