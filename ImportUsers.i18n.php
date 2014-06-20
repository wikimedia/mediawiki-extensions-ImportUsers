<?php
/**
 * Internationalization messages file for Import Users extension
 *
 * @file
 * @ingroup Extensions
 */

$messages = array();

/** 
 * English
 */
$messages['en'] = array(
	'importusers'				=> 'Import users',
	'importusers-desc'			=> 'Allows to [[Special:ImportUsers|import users]] in bulk from a UTF-8 encoded CSV file',
	'importusers-uploadfile'		=> 'Upload file',
	'importusers-form-caption'		=> 'Input file:',
	'importusers-file'			=> 'Imput file structure and format:',
	'importusers-file-structure'            => 'Structure:',	
	'importusers-file-format'               => 'Format:',
	'importusers-form-replace-present'	=> 'Replace existing users',
	'importusers-form-add-to-group'		=> 'Add users to existing groups' ,
	'importusers-form-button'		=> 'Import',
	'importusers-user-added'		=> 'User "$1" has been added.',
	'importusers-user-present-update'	=> 'User "$1" already exists and has been updated.',
	'importusers-user-present-no-update'	=> 'User "$1" already exists and has not been updated.',
	'importusers-user-invalid-format'	=> 'User data in the line #$1 has invalid format or is blank and was skipped.',
	'importusers-log'			=> 'Import users log',
	'importusers-log-list'                  => 'Imported or updated users:', 
	'importusers-log-summary'		=> 'Import summary:',
	'importusers-log-summary-all'		=> 'Total users: $1',
	'importusers-log-summary-added'		=> 'Users added: $1',
	'importusers-log-summary-updated'	=> 'Users updated: $1',
	'importusers-login-name'		=> 'Login name',
	'importusers-password'			=> 'password',
	'importusers-email'			=> 'e-mail',
	'importusers-realname'			=> 'real name',
	'importusers-group'			=> 'user group',
	'importusers-utf8'			=> 'UTF-8 encoded',
	'importusers-comma'			=> 'separate fields with commas',
	'importusers-noquotes'			=> 'no double-quotes for text',
	'right-import_users'			=> 'Import users in bulk'
);

/** German (Deutsch)
 * @author Als-Holder
 * @author Kghbln
 * @author MF-Warburg
 * @author The Evil IP address
 */
$messages['de'] = array(
	'importusers' => 'Benutzer importieren',
	'importusers-desc' => 'Ergänzt eine Spezialseite zum [[Special:ImportUsers|Import von Benutzern]] aus Dateien im CSV-Format (UTF-8)',
	'importusers-uploadfile' => 'Datei hochladen',
	'importusers-form-caption' => 'Importdatei:',
	'importusers-file' => 'Aufbau und Formatierung der CSV-Importdatei:',
	'importusers-file-structure' => 'Aufbau:',	
	'importusers-file-format' => 'Formatierung:',
	'importusers-form-replace-present' => 'Bestehende Benutzer ersetzen',
	'importusers-form-add-to-group'	=> 'Benutzer einer vorhandenen Benutzergruppe zuordnen',
	'importusers-form-button' => 'Benutzer importieren',
	'importusers-user-added' => 'Benutzer „$1“ wurde importiert.',
	'importusers-user-present-update' => 'Benutzer „$1“ ist bereits vorhanden und wurde aktualisiert.',
	'importusers-user-present-no-update' => 'Benutzer „$1“ ist bereits vorhanden und wurde nicht aktualisiert.',
	'importusers-user-invalid-format' => 'Die Benutzerdaten in Zeile #$1 haben ein ungültiges Format oder sind leer und wurden nicht importiert oder aktualisiert.',
	'importusers-log' => 'Benutzerimport-Logbuch',
	'importusers-log-list' => 'Importierte oder aktualisierte Benutzer:', 
	'importusers-log-summary' => 'Zusammenfassung des Benutzerimports:',
	'importusers-log-summary-all' => 'Benutzer insgesamt: $1',
	'importusers-log-summary-added' => 'Benutzer hinzugefügt: $1',
	'importusers-log-summary-updated' => 'Benutzer aktualisiert: $1',
	'importusers-login-name' => 'Benutzername',
	'importusers-password' => 'Passwort',
	'importusers-email' => 'E-Mail-Adresse',
	'importusers-realname' => 'Bürgerlicher Name',
	'importusers-group' => 'Benutzergruppe',
	'importusers-utf8' => 'Zeichensatzkodierung in UTF-8',
	'importusers-comma' => 'Kommata als Feldtrenner',
	'importusers-noquotes' => 'keine Texttrenner',
	'right-import_users' => 'Benutzerdaten in großem Umfang importieren'
);
