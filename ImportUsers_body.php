<?php

class SpecialImportUsers extends SpecialPage {

	/**
	 * Constructor -- set up the new special page
	 */
	public function __construct() {
		parent::__construct( 'ImportUsers', 'import_users' );
	}

	/**
	 * Show the special page
	 *
	 * @param $par Mixed: parameter passed to the special page or null
	 */
	public function execute( $par ) {
		global $wgOut, $wgUser;

		if( !$wgUser->isAllowed( 'import_users' ) ) {
			$wgOut->permissionRequired( 'import_users' );
			return;
		}

		$this->setHeaders();

		if ( isset( $_FILES['users_file'] ) ) {
			$wgOut->addHTML( $this->analyzeUsers( $_FILES['users_file'], isset( $_POST['replace_present'] ), isset( $_POST['importusers_send_email'] ), isset( $_POST['importusers_add_to_group'] ) ) );
		} else {
			$wgOut->addHTML( $this->makeForm() );
		}
	}

	function makeForm() {
		global $wgLang;

		$titleObj = SpecialPage::getTitleFor( 'ImportUsers' );
		$action = $titleObj->escapeLocalURL();
		$fileFormat = $wgLang->commaList( array(
			wfMsg( 'importusers-login-name' ),
			wfMsg( 'importusers-password' ),
			wfMsg( 'importusers-email' ),
			wfMsg( 'importusers-realname' )
		) );
		$output = '<form enctype="multipart/form-data" method="post"  action="' . $action . '">';
		$output .= '<dl><dt>' . wfMsg( 'importusers-form-file' ) . '</dt><dd>' . $fileFormat . '.</dd></dl>';
		$output .= '<fieldset><legend>' . wfMsg( 'importusers-uploadfile' ) . '</legend>';
		$output .= '<table border="0" a-valign="center" width="100%">';
		$output .= '<tr><td align="right" width="160">' . wfMsg( 'importusers-form-caption' ) .
			' </td><td><input name="users_file" type="file" size=40 /></td></tr>';
                $output .= '<tr><td align=right></td><td><input name="replace_present" type="checkbox" />' . 
                        wfMsg( 'importusers-form-replace-present' ).'</td></tr>';
                $output .= '<tr><td align=right></td><td><input name="importusers_send_email" type="checkbox" />' .
                        wfMsg( 'importusers_form_send_email' ).'</td></tr>';
                $output .= '<tr><td align=right></td><td><input name="importusers_add_to_group" type="checkbox" />' .
                        wfMsg( 'importusers_form_add_to_group' ).'</td></tr>';
		$output .= '<tr><td align="right"></td><td><input type="submit" value="' . wfMsg( 'importusers-form-button' ) . '" /></td></tr>';
		$output .= '</table>';
		$output .= '</fieldset>';
		$output .= '</form>';
		return $output;
	}
 
	function analyzeUsers( $fileinfo, $replace_present, $importusers_send_email, $importusers_add_to_group ) {

	        global $wgEmailAuthentication ;

	        $summary=array('all'=>0,'added'=>0,'updated'=>0,'email_sent'=>0,'email_failed'=>0);
 
		$filedata = explode( "\n", rtrim( file_get_contents( $fileinfo['tmp_name'] ) ) );
		$output = '<h2>' . wfMsg( 'importusers-log' ) . '</h2>';

		foreach ( $filedata as $line => $newuserstr ) {
			$newuserarray = explode( ',', trim( $newuserstr ) );
			if ( count( $newuserarray ) < 2 ) {
				$output .= wfMsg( 'importusers-user-invalid-format', $line + 1 ) . '<br />';
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
 
	                        $this->AddToGroup($nextUser,$newuserarray,$importusers_add_to_group);
 
            	            if( $wgEmailAuthentication && $importusers_send_email && User::isValidEmailAddr( $nextUser->getEmail() ) ) 
	                        {
        	                        global $wgOut;
                	                $error = $nextUser->sendConfirmationMail();

                        	        if( WikiError::isError( $error ) )
                               	{
	                                        $output.=sprintf(wfMsg( 'importusers_user_invalid_email' ) ,$line+1 ).'<br />';
        	                                $summary['email_failed']++;
                	                } else 
                        	        {
                                	        $summary['email_sent']++;
	                                }
        	                }

				$output .= wfMsg( 'importusers-user-added', $newuserarray[0] ) . '<br />';
				$summary['added']++;
			} else {
				if ( $replace_present ) {
					$nextUser->setPassword( $newuserarray[1] );
					$nextUser->saveSettings();
 
		                        $this->AddToGroup($nextUser,$newuserarray,$importusers_add_to_group);
 
					$output .= wfMsg( 'importusers-user-present-update', $newuserarray[0] ).'<br />';
					$summary['updated']++;
				} else {
					$output .= wfMsg( 'importusers-user-present-not-update', $newuserarray[0] ) . '<br />';
				}
			}
			$summary['all']++;
		}
 
		$output .= '<b>' . wfMsg( 'importusers-log-summary' ) . '</b><br />';
		$output .= wfMsg( 'importusers-log-summary-all', $summary['all'] ) . '<br />';
		$output .= wfMsg( 'importusers-log-summary-added', $summary['added'] ) . '<br />';
		$output .= wfMsg( 'importusers-log-summary-updated', $summary['updated'] ) . '<br />';
	        $output .= wfMsg( 'importusers_log_summary_email_sent' ).': '.$summary['email_sent'].'<br />';
	        $output .= wfMsg( 'importusers_log_summary_email_failed' ).': '.$summary['email_failed'];

		return $output;
	}
 
       function AddToGroup($u,$user_array,$add_to_group_checked){
                global $wgOut, $wgUser;
                if( $wgUser->isAllowed( 'userrights' ) && $add_to_group_checked && IsSet($user_array[4])) {
                        for( $i = 4 ; $i < sizeof( $user_array) ; $i++) {
                                if ( in_array($user_array[ $i],User::getAllGroups())){
                                        if ( !in_array($user_array[ $i],$u->getGroups())){
                                                $u->addGroup( $user_array[ $i] );
                                       }
                                }
                        }
                }
        }
}
