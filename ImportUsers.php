<?php
/**
 * An extension to MediaWiki that allows to import users in bulk from a UTF-8 encoded CSV file.
 *
 * @file
 * @ingroup Extensions
 * @package MediaWiki
 *
 * @version 1.5.0 2015-01-08
 *
 * @links https://github.com/kghbln/ImportUsers/blob/master/README.md Documentation
 * @links https://www.mediawiki.org/wiki/Extension_talk:ImportUsers Support
 * @links https://github.com/kghbln/ImportUsers/issues Bug tracker
 * @links https://github.com/kghbln/ImportUsers Source code
 *
 * @author Rouslan Zenetl, Yuriy Ilkiv
 *
 * @license Public Domain - you are free to use this extension for any reason and mutilate it to your heart's liking.
 */

/* Ensure that the script cannot be executed outside of MediaWiki. */
if ( !defined( 'MEDIAWIKI' ) ) {
	die( 'This file is part of a MediaWiki extension and cannot be run standalone!' );
	}

/* Display extension properties on MediaWiki. */
$wgExtensionCredits['specialpage'][] = array(
	'path' => __FILE__,
	'name' => 'Import Users',
	'version' => '1.4.2',
	'author' => array(
		'Yuriy Ilkiv',
		'Rouslan Zenetl',
		'...'
		),
	'url' => 'https://www.mediawiki.org/wiki/Extension:ImportUsers',
	'descriptionmsg' => 'importusers-desc',
	'license-name' => 'PD'
);

/*  Register extension class. */
$wgAutoloadClasses['SpecialImportUsers'] = __DIR__ . '/ImportUsers_body.php';

/* Register extension messages. */
$wgExtensionMessagesFiles['ImportUsers'] = __DIR__ . '/ImportUsers.i18n.php';
$wgExtensionMessagesFiles['ImportUsersAlias'] = __DIR__ . '/ImportUsers.alias.php';

/* Register special page into MediaWiki. */
$wgSpecialPages['ImportUsers'] = 'SpecialImportUsers';
$wgSpecialPageGroups['ImportUsers'] = 'users';

/* Create new right and set permissions */
$wgAvailableRights[] = 'import_users';
$wgGroupPermissions['bureaucrat']['import_users'] = true;
