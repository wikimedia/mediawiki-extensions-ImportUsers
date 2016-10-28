<?php
/**
 * An extension to MediaWiki allowing to import users in bulk from a UTF-8 encoded CSV file.
 *
 * @file
 * @ingroup Extensions
 * @package MediaWiki
 *
 * @version 2.0.0 2016-10-28
 *
 * @links https://www.mediawiki.org/wiki/Extension:ImportUsers Homepage
 * @links https://phabricator.wikimedia.org/diffusion/EIUS/browse/master/README.md Documentation
 * @links https://www.mediawiki.org/wiki/Extension_talk:ImportUsers Support
 * @links https://phabricator.wikimedia.org/tag/importusers/ Bug tracker
 * @links https://phabricator.wikimedia.org/diffusion/EIUS/extensions-importusers.git Source code
 * @links https://github.com/wikimedia/mediawiki-extensions-ImportUsers/releases Downloads
 *
 * @author Rouslan Zenetl, Yuriy Ilkiv (original authors)
 *
 * @license Public Domain - you are free to use this extension for any reason and mutilate it to your heart's liking.
 */

/* Ensure that the script cannot be executed outside of MediaWiki. */
if ( !defined( 'MEDIAWIKI' ) ) {
	die( 'This file is part of a MediaWiki extension and cannot be run standalone!' );
	}

/* Display extension properties on MediaWiki. */
$wgExtensionCredits['specialpage'][] = [
	'path' => __FILE__,
	'name' => 'Import Users',
	'version' => '2.0.0',
	'author' => [
		'Yuriy Ilkiv',
		'Rouslan Zenetl',
		'...'
	],
	'url' => 'https://www.mediawiki.org/wiki/Extension:ImportUsers',
	'descriptionmsg' => 'importusers-desc',
	'license-name' => 'PD'
];

/*  Register extension class. */
$wgAutoloadClasses['SpecialImportUsers'] = __DIR__ . '/ImportUsers_body.php';

/* Register extension messages. */
$wgMessagesDirs['ImportUsers'] = __DIR__ . '/i18n';
$wgExtensionMessagesFiles['ImportUsersAlias'] = __DIR__ . '/ImportUsers.alias.php';

/* Register special page into MediaWiki. */
$wgSpecialPages['ImportUsers'] = 'SpecialImportUsers';
$wgSpecialPageGroups['ImportUsers'] = 'users';

/* Create new right and set permissions */
$wgAvailableRights[] = 'import_users';
$wgGroupPermissions['bureaucrat']['import_users'] = true;
