## Import Users

Import Users is a MediaWiki extension to MediaWiki allowing to import
users in bulk from a UTF-8 encoded CSV file.

This file documents the development changes.

### Version 2.2.0

Released: 2018-03-03  
Authored: Kghbln

* Dropped compatibility for MW 1.24.x and lower
* Converted ImportUsers to use extension registration (by Jayprakash12345)
* Added translation updates (by translators via translatewiki.net)


### Version 2.1.0

Released: 2017-07-04  
Authored: Kghbln

* Added compatibility for MW 1.26.x and higher
* Migrated registration of special pages to `SpecialPage::getGroupName` (by Umherirrender)
* Added translations for many more languages (by translators via translatewiki.net)
* Added special page alias for Bengali (by Aftabuzzaman)
* Fix license notation to match SPDX (by Umherirrender)
* Updated COPYING (by Kghbln)
* Extended README.md (by Kghbln)
* Updated file documentation (by Kghbln)


### Version 2.0.0

Released: 2016-10-28  
Authored: Kghbln

* Dropped compatibility with PHP 5.3 and lower
* Dropped compatibility with MediaWiki 1.22.x and lower
* Removed i18n-shim for php-files
* Converted to PHP 5.4+ short array syntax


### Version 1.5.4

Released: 2016-10-28  
Authored: Kghbln

* Added translations for many more languages (by translators via translatewiki.net)


### Version 1.5.3

Released: 2015-09-16  
Authored: Kghbln

* Added PLURAL and GENDER support to system message
* Added system message documentation
* Added translations for many more languages (by translators via translatewiki.net)


### Version 1.5.2

Released: 2015-01-27  
Authored: Ency, Kghbln

* Added missing system message


### Version 1.5.1

Released: 2015-01-13  
Authored: Ency

* Added Polish translations


### Version 1.5.0

Released: 2015-01-08  
Authored: Kghbln

* Dropped compatibility with PHP 5.2
* Dropped compatibility with MW < 1.17
* Switched to json i18n system
* Improved and extended file documentation


### Version 1.4.2

Released: 2014-11-10  
Authored: Bawolff

* Removed usage of depreciated title method escapeLocalUrl


### Version 1.4.1

Released: 2014-06-20  
Authored: Kghbln

* Move wfMsg depeciated in MW 1.21 to wfMessage


### Version 1.4.0

Released: 2014-06-18  
Authored: Kghbln

* Improve user guidance on "Special:ImportUsers"
* Restructure and improve system-messages


### Version 1.3.1

Released: 2014-06-16  
Authored: Kghbln

* Add missing system message


### Version 1.3.0

Released: 2014-06-16  
Authored: Jpond, Sm8ps

* Add possibility to import user group assignments


### Version 1.2.0

Released: 2011-03-22  
Authored: Ashley

* Rename some functions as per our coding conventions
* Fixed a typo in function name
* Whitespace tweaks
* Move some hardcoded colons to the i18n file for better i18n
* Add code documentation


### Version 1.1.0

Released: 2008-07-09  
Authored: Jhsoby

* Add special page alias


### Version 1.0.0

Released: 2008-06-11  
Authored: Ashley

* Remove "ExtensionFunctions.php" dependency


### Version 0.0.4

Released: 2008-02-10  
Authored: Grondin

* Add internationalization extension description message in "Special:Version"
* Create internationalization file
* Add more message


### Version 0.0.3

Released: 2007-01-07  
Authored: RouslanZenetl

* Improve handling in case of invalid import file format


### Version 0.0.2

Released: 2006-12-16  
Authored: RouslanZenetl, YuriyIlkiv

* Initial public release
