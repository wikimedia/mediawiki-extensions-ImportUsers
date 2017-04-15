<?php
/**
 * Action file for Import Users extension
 *
 * @file
 * @ingroup Extensions
 */

class SpecialImportUsers extends SpecialPage {

	/**
	 * Constructor -- set up the new special page
	 */
	public function __construct() {
		parent::__construct( 'ImportUsers', 'import_users' );
	}

	public function doesWrites() {
		return true;
	}

	/**
	 * Show the special page
	 *
	 * @param $par Mixed: parameter passed to the special page or null
	 */
	public function execute( $par ) {
		global $wgOut, $wgUser;

		if( !$wgUser->isAllowed( 'import_users' ) ) {
			throw new PermissionsError( 'import_users' );
		}

		$this->setHeaders();

		if ( isset( $_FILES['users_file'] ) ) {
			$wgOut->addHTML( $this->analyzeUsers(
				$_FILES['users_file'],
				isset( $_POST['replace_present'] ),
				isset( $_POST['add_to_group'] )
				)
			);
		} else {
			$wgOut->addHTML( $this->makeForm() );
		}
	}

	function makeForm() {
		global $wgLang;

		$titleObj = SpecialPage::getTitleFor( 'ImportUsers' );

		$action = htmlspecialchars( $titleObj->getLocalURL() );

		$fileStructure = $wgLang->commaList( [
			wfMessage( 'importusers-login-name' )->text(),
			wfMessage( 'importusers-password' )->text(),
			wfMessage( 'importusers-email' )->text(),
			wfMessage( 'importusers-realname' )->text(),
			wfMessage( 'importusers-group' )->text()
			]
		);
		$fileFormat = $wgLang->commaList( [
			wfMessage( 'importusers-utf8' )->text(),
			wfMessage( 'importusers-comma' )->text(),
			wfMessage( 'importusers-noquotes' )->text()
			]
		);

		$output = '<form enctype="multipart/form-data" method="post"  action="' . $action . '">';
		$output .= '<h3>' . wfMessage( 'importusers-file' )->text() . '</h3>';
		$output .= '<dl>
				<dt>' . wfMessage( 'importusers-file-structure' )->text() . '</dt>
					<dd>' . $fileStructure . '</dd>';
		$output .= '<dt>' . wfMessage( 'importusers-file-format' )->text() . '</dt>
				<dd>' . $fileFormat . '</dd>
			</dl>';
		$output .= '<fieldset>
			<legend>' . wfMessage( 'importusers-uploadfile' )->text() . '</legend>';
		$output .= '<table border="0" a-valign="center" width="100%">';
		$output .= '<tr>
				<td align="right" width="160">' . wfMessage( 'importusers-form-caption' )->text() . ' </td>
				<td><input name="users_file" type="file" size=40 /></td>
			</tr>';
                $output .= '<tr>
				<td align=right></td>
				<td><input name="replace_present" type="checkbox" />' . wfMessage( 'importusers-form-replace-present' )->text() . '</td>
			</tr>';
                $output .= '<tr>
				<td align=right></td>
				<td><input name="add_to_group" type="checkbox" />' . wfMessage( 'importusers-form-add-to-group' )->text() . '</td>
			</tr>';
		$output .= '<tr>
				<td align="right"></td>
				<td><input type="submit" value="' . wfMessage( 'importusers-form-button' )->text() . '" /></td>
			</tr>';
		$output .= '</table>';
		$output .= '</fieldset>';
		$output .= '</form>';

		return $output;
	}

	function analyzeUsers( $fileinfo, $replace_present, $importusers_add_to_group ) {

		$summary = [
			'all' => 0,
			'added' => 0,
			'updated' => 0
		];

		$filedata = explode( "\n", rtrim( file_get_contents( $fileinfo['tmp_name'] ) ) );
		$output = '<h3>' . wfMessage( 'importusers-log' )->text() . '</h3><br />';
		$output .= '<b>' . wfMessage( 'importusers-log-list' )->text() . '</b><br />';

		foreach ( $filedata as $line => $newuserstr ) {
			$newuserarray = explode( ',', trim( $newuserstr ) );
			if ( count( $newuserarray ) < 2 ) {
				$output .= wfMessage( 'importusers-user-invalid-format', $line + 1 )->text() . '<br />';
				continue;
			}
			if ( !isset( $newuserarray[2] ) ) {
				$newuserarray[2] = '';
			}
			if ( !isset( $newuserarray[3] ) ) {
				$newuserarray[3] = '';
			}
			$nextUser = User::newFromName( $newuserarray[0] );
			$nextUser->setEmail( $newuserarray[2] );
			$nextUser->setRealName( $newuserarray[3] );
			$uid = $nextUser->idForName();
			if ( $uid === 0 ) {
				$nextUser->addToDatabase();
				$nextUser->setPassword( $newuserarray[1] );
				$nextUser->saveSettings();
 
	                        $this->AddToGroup( $nextUser, $newuserarray, $importusers_add_to_group );
 
				$output .= wfMessage( 'importusers-user-added', $newuserarray[0] )->text() . '<br />';
				$summary['added']++;
			} else {
				if ( $replace_present ) {
					$nextUser->setPassword( $newuserarray[1] );
					$nextUser->saveSettings();
 
		                        $this->AddToGroup( $nextUser, $newuserarray, $importusers_add_to_group );
 
					$output .= wfMessage( 'importusers-user-present-update', $newuserarray[0] )->text() . '<br />';
					$summary['updated']++;
				} else {
					$output .= wfMessage( 'importusers-user-present-no-update', $newuserarray[0] )->text() . '<br />';
				}
			}
			$summary['all']++;
		}

		$output .= '<br /><b>' . wfMessage( 'importusers-log-summary' )->text() . '</b><br />';
		$output .= wfMessage( 'importusers-log-summary-all', $summary['all'] )->text() . '<br />';
		$output .= wfMessage( 'importusers-log-summary-added', $summary['added'], $newuserarray[0] )->text() . '<br />';
		$output .= wfMessage( 'importusers-log-summary-updated', $summary['updated'] )->text() . '<br />';

		return $output;
	}

       function AddToGroup( $u, $user_array, $add_to_group_checked ) {
                global $wgOut, $wgUser;
                if( $wgUser->isAllowed( 'import_users' ) && $add_to_group_checked && isset( $user_array[4] ) ) {
                        for( $i = 4 ; $i < sizeof( $user_array) ; $i++ ) {
                                if ( in_array( $user_array[ $i], User::getAllGroups() ) ) {
                                        if ( !in_array( $user_array[ $i], $u->getGroups() ) ) {
                                                $u->addGroup( $user_array[ $i] );
                                       }
                                }
                        }
                }
        }

	protected function getGroupName() {
		return 'users';
	}
}
