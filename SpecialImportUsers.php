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
	 * @param string|null $par parameter passed to the special page or null
	 */
	public function execute( $par ) {
		$use = $this->getUser();
		$out = $this->getOutput();
		if ( !$use->isAllowed( 'import_users' ) ) {
			throw new PermissionsError( 'import_users' );
		}

		$this->setHeaders();

		$upload = $this->getRequest()->getUpload( 'users_file' );
		if ( $upload->exists() ) {
			$out->addHTML( $this->analyzeUsers(
				$upload,
				isset( $_POST['replace_present'] ),
				isset( $_POST['add_to_group'] )
				)
			);
		} else {
			$out->addHTML( $this->makeForm() );
		}
	}

	function makeForm() {
		$lan = $this->getLanguage();

		$titleObj = SpecialPage::getTitleFor( 'ImportUsers' );

		$action = htmlspecialchars( $titleObj->getLocalURL() );

		$fileStructure = $lan->commaList( [
			wfMessage( 'importusers-login-name' )->text(),
			wfMessage( 'importusers-password' )->text(),
			wfMessage( 'importusers-email' )->text(),
			wfMessage( 'importusers-realname' )->text(),
			wfMessage( 'importusers-group' )->text()
			]
		);
		$fileFormat = $lan->commaList( [
			wfMessage( 'importusers-utf8' )->text(),
			wfMessage( 'importusers-comma' )->text(),
			wfMessage( 'importusers-noquotes' )->text()
			]
		);

		$output = '<form enctype="multipart/form-data" method="post" action="' . $action . '">';
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

	function analyzeUsers( $upload, $replace_present, $importusers_add_to_group ) {
		$summary = [
			'all' => 0,
			'added' => 0,
			'updated' => 0
		];

		$filedata = explode( "\n", rtrim( file_get_contents( $upload->getTempName() ) ) );
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
				$output .= $this->setPassword( $nextUser, $newuserarray[1] );
				$nextUser->saveSettings();

				$this->AddToGroup( $nextUser, $newuserarray, $importusers_add_to_group );

				$output .= wfMessage( 'importusers-user-added', $newuserarray[0] )->text() . '<br />';
				$summary['added']++;
			} elseif ( $replace_present ) {
				$output .= $this->setPassword( $nextUser, $newuserarray[1] );
				$nextUser->saveSettings();

				$this->AddToGroup( $nextUser, $newuserarray, $importusers_add_to_group );

				$output .= wfMessage( 'importusers-user-present-update', $newuserarray[0] )->text() . '<br />';
				$summary['updated']++;
			} else {
				$output .= wfMessage( 'importusers-user-present-no-update', $newuserarray[0] )->text() . '<br />';
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
		$user = $this->getUser();

		if ( $user->isAllowed( 'import_users' ) && $add_to_group_checked && isset( $user_array[4] ) ) {
			$userGroupManager = MediaWikiServices::getInstance()->getUserGroupManager();
			$allUserGroups = $userGroupManager->listAllGroups();
			$userGroups = $userGroupManager->getUserGroups( $u );
			for ( $i = 4; $i < count( $user_array ); $i++ ) {
				if ( in_array( $user_array[$i], $allUserGroups ) ) {
					if ( !in_array( $user_array[$i], $userGroups ) ) {
						$userGroupManager->addUserToGroup( $u, $user_array[$i] );
					}
				}
			}
		}
	}

	/**
	 * Set a password on a user or return error
	 *
	 * @param User $user
	 * @param string $password
	 * @return string HTML error message or empty string on success
	 */
	private function setPassword( User $user, string $password ) {
		if ( $password === '' ) {
			// Assume empty means the user intentionally did not
			// want a password set.
			return;
		}
		$status = $user->changeAuthenticationData( [
			'password' => $password,
			'retype' => $password,
			'username' => $user->getName()
		] );
		if ( !$status->isGood() ) {
			// We weren't able to set a password.
			// Probably password is too weak.
			return wfMessage( 'importusers-bad-password' )
				->params( wfEscapeWikiText( $user->getName() ) )
				->params( $status->getWikiText() )
				->parse() . '<br>';
		}
		return '';
	}

	protected function getGroupName() {
		return 'users';
	}
}
