<?php
/**
 * An extension to MediaWiki allowing to import users in bulk from a UTF-8 encoded CSV file.
 *
 * @file
 * @ingroup Extensions
 * @package MediaWiki
 *
 * @links https://www.mediawiki.org/wiki/Extension:ImportUsers Homepage
 * @links https://phabricator.wikimedia.org/diffusion/EIUS/browse/master/README.md Documentation
 * @links https://www.mediawiki.org/wiki/Extension_talk:ImportUsers Support
 * @links https://phabricator.wikimedia.org/tag/importusers/ Bug tracker
 * @links https://gerrit.wikimedia.org/r/p/mediawiki/extensions/ImportUsers Source code
 * @links https://github.com/wikimedia/mediawiki-extensions-ImportUsers/releases Downloads
 *
 * @author Rouslan Zenetl, Yuriy Ilkiv (original authors)
 *
 * @license Unlicense
 */

if ( function_exists( 'wfLoadExtension' ) ) {
	wfLoadExtension( 'ImportUsers' );
	// Keep i18n globals so mergeMessageFileList.php doesn't break
	$wgMessagesDirs['ImportUsers'] = __DIR__ . '/i18n';
	wfWarn(
		'Deprecated PHP entry point used for the ImportUsers extension. ' .
		'Please use wfLoadExtension instead, ' .
		'see https://www.mediawiki.org/wiki/Extension_registration for more details.'
	);
	return;
} else {
	die( 'This version of the ImportUsers extension requires MediaWiki 1.25+' );
}
