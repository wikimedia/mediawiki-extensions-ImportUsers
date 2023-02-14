<?php
/**
 * Action file for Import Users extension
 *
 * @file
 * @ingroup Extensions
 */

use MediaWiki\MediaWikiServices;

class SpecialImportUsers extends FormSpecialPage {
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
	 * @return array
	 */
	protected function getFormFields() {
		return [
			// Note required=>true is broken on file uploads, so
			// don't specify it here (T327007).
			'csvfile' => [
				'type' => 'file',
				'label-message' => 'importusers-uploadfile',
				'validation-callback' => [ $this, 'validateCSVFile' ],
				// Try to be generous with possible types in case of mislabel.
				'accept' => [
					'text/csv',
					'text/plain',
					'text/x-csv',
					// The internet claims CSV files are sometimes sent as this
					'application/vnd.ms-excel',
					'.csv',
					'.txt'
				]
			],
			'replacePresent' => [
				'type' => 'check',
				'label-message' => 'importusers-form-replace-present',
			],
			'addToGroup' => [
				'type' => 'check',
				'label-message' => 'importusers-form-add-to-group',
			],
		];
	}

	/**
	 * Check that the CSV file is valid
	 *
	 * @return Message|bool If file is ok or the error message
	 */
	public function validateCSVFile() {
		$upload = $this->getRequest()->getUpload( 'wpcsvfile' );
		if ( !$upload->exists() ) {
			return $this->msg( 'htmlform-required' );
		}
		$tmpName = $upload->getTempName();
		if ( !file_exists( $tmpName ) || filesize( $tmpName ) < 3 ) {
			return $this->msg( 'htmlform-required' );
		}
		$contents = file_get_contents( $tmpName, false, null, 0, 2048 );
		if ( strpos( $contents, ',' ) === false ) {
			return $this->msg( 'importusers-invalid-file' );
		}
		// Possible TODO: Check that the file is valid UTF-8 and doesn't
		// contain weird binary characters.
		return true;
	}

	/**
	 * Get the description of the csv format.
	 *
	 * @return string
	 */
	private function getFileStructure() {
		// Do not use language comma list, since this is
		// supposed to be showing the CSV format, so we
		// would not want the comma translated into ፣ or 、.
		return implode( ', ', [
			$this->msg( 'importusers-login-name' )->plain(),
			$this->msg( 'importusers-password' )->plain(),
			$this->msg( 'importusers-email' )->plain(),
			$this->msg( 'importusers-realname' )->plain(),
			$this->msg( 'importusers-group' )->plain()
		] );
	}

	/**
	 * @param HTMLForm $form
	 */
	protected function alterForm( HTMLForm $form ) {
		$form->setWrapperLegendMsg( 'importusers-uploadfile' )
			->setSubmitTextMsg( 'importusers-form-button' );
	}

	protected function preHtml() {
		return Html::element( 'h3', [], $this->msg( 'importusers-file' )->text() )
			. Html::rawElement( 'dl', [],
				Html::element( 'dt', [], $this->msg( 'importusers-file-structure' )->text() )
				. Html::element( 'dd', [], $this->getFileStructure() )
				. Html::element( 'dt', [], $this->msg( 'importusers-file-format' )->text() )
				. Html::element( 'dd', [], $this->msg( 'importusers-file-format-desc' )->text() )
			);
	}

	public function onSubmit( $data ) {
		// Due to password hashing, this can take a long time to process.
		// increase limit
		$this->useTransactionalTimeLimit();

		$upload = $this->getRequest()->getUpload( 'wpcsvfile' );
		$this->getOutput()->addWikiTextAsInterface( $this->analyzeUsers(
			$this->getFileContents( $upload ),
			$data['replacePresent'],
			$data['addToGroup']
		) );

		return Status::newGood();
	}

	/**
	 * @param WebRequestUpload $upload
	 * @return array All the lines in the file.
	 */
	private function getFileContents( WebRequestUpload $upload ) {
		$fileContents = rtrim( file_get_contents( $upload->getTempName() ) );
		// This will ensure everything is UTF-8 NFC.
		$fileCleaned = UtfNormal\Validator::cleanUp( $fileContents );
		return explode( "\n", $fileCleaned );
	}

	/**
	 * @param string[] $lines Lines of CSV file
	 * @param bool $replace_present
	 * @param bool $importusers_add_to_group
	 * @return string Output interpreted as wikitext (not html).
	 */
	private function analyzeUsers( $lines, $replace_present, $importusers_add_to_group ) {
		$summary = [
			'all' => 0,
			'added' => 0,
			'updated' => 0
		];

		$output = '<h3>' . wfMessage( 'importusers-log' )->text() . '</h3><br />';
		$output .= '<b>' . wfMessage( 'importusers-log-list' )->text() . '</b><br />';

		foreach ( $lines as $line => $newuserstr ) {
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
			$nextUser = User::newFromName( $newuserarray[0], 'creatable' );
			if ( !$nextUser ) {
				$output .= wfMessage( 'importusers-user-invalid' )
					->params( wfEscapeWikiText( $newuserarray[0] ) )->text() . '<br>';
				continue;
			}
			$nextUser->setEmail( $newuserarray[2] );
			$nextUser->setRealName( $newuserarray[3] );
			$uid = $nextUser->idForName();
			if ( $uid === 0 ) {
				$nextUser->addToDatabase();
				$output .= $this->setPassword( $nextUser, $newuserarray[1] );
				$nextUser->saveSettings();

				$this->addToGroup( $nextUser, $newuserarray, $importusers_add_to_group );

				$output .= wfMessage( 'importusers-user-added', $newuserarray[0] )->text() . '<br />';
				$summary['added']++;
			} elseif ( $replace_present ) {
				$output .= $this->setPassword( $nextUser, $newuserarray[1] );
				$nextUser->saveSettings();

				$this->addToGroup( $nextUser, $newuserarray, $importusers_add_to_group );

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

	function addToGroup( $u, $user_array, $add_to_group_checked ) {
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
				->text() . '<br>';
		}
		return '';
	}

	/** @inheritDoc */
	protected function getGroupName() {
		return 'users';
	}
}
