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
##sm: changed function call (two additional parameters)
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
 
##              $output .= '<tr><td align="right"></td><td><input name="replace_present" type="checkbox" />' .
##                      wfMsg( 'importusers-form-replace-present' ) . '</td></tr>';
# Jack D. Pond changed line to add send email and ask if add user to groups (if in file)
##sm: split     $output .= '<tr><td align=right></td><td><input name="replace_present" type="checkbox" />' .
##    table             wfMsg( 'importusers-form-replace-present' ).'<input name="importusers_send_email" type="checkbox" />' .
##    rows              wfMsg( 'importusers_form_send_email' ).'</td></tr>';
                $output .= '<tr><td align=right></td><td><input name="replace_present" type="checkbox" />' . 
                        wfMsg( 'importusers-form-replace-present' ).'</td></tr>';
                $output .= '<tr><td align=right></td><td><input name="importusers_send_email" type="checkbox" />' .
                        wfMsg( 'importusers_form_send_email' ).'</td></tr>';
##sm: corrected name
                $output .= '<tr><td align=right></td><td><input name="importusers_add_to_group" type="checkbox" />' .
                        wfMsg( 'importusers_form_add_to_group' ).'</td></tr>';
##sm: corrected name
# End change

		$output .= '<tr><td align="right"></td><td><input type="submit" value="' . wfMsg( 'importusers-form-button' ) . '" /></td></tr>';
		$output .= '</table>';
		$output .= '</fieldset>';
		$output .= '</form>';
		return $output;
	}
 
	function analyzeUsers( $fileinfo, $replace_present, $importusers_send_email, $importusers_add_to_group ) {
##sm: changed function signature (two additional parameters)

	        global $wgEmailAuthentication ;
# Jack D. Pond added global $wgEmailAuthentication

##	        $summary=array('all'=>0,'added'=>0,'updated'=>0);
#		$summary = array(
#			'all' => 0,
#			'added' => 0,
#			'updated' => 0
#		);
# Jack D. Pond added email_sent and email_failed counters to array
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
 
# Added line to import user group assignment too
	                        $this->AddToGroup($nextUser,$newuserarray,$importusers_add_to_group);
 
# Extended by Jack D. Pond to send email notifications to users (if enabled)
            	            if( $wgEmailAuthentication && $importusers_send_email && User::isValidEmailAddr( $nextUser->getEmail() ) ) 
	                        {
        	                        global $wgOut;
                	                $error = $nextUser->sendConfirmationMail();
# May want to delete here to end of extend if not using email addresses in import
                        	        if( WikiError::isError( $error ) )
                               	{
	                                        $output.=sprintf(wfMsg( 'importusers_user_invalid_email' ) ,$line+1 ).'<br />';
        	                                $summary['email_failed']++;
                	                } else 
                        	        {
                                	        $summary['email_sent']++;
	                                }
        	                }
# End Extend

				$output .= wfMsg( 'importusers-user-added', $newuserarray[0] ) . '<br />';
				$summary['added']++;
			} else {
				if ( $replace_present ) {
					$nextUser->setPassword( $newuserarray[1] );
					$nextUser->saveSettings();
 
# Added line to import user group assignment too
		                        $this->AddToGroup($nextUser,$newuserarray,$importusers_add_to_group);
 
					$output .= wfMsg( 'importusers-user-present-update', $newuserarray[0] ).'<br />';
					$summary['updated']++;
				} else {
##sm: fix typo in original code		$output .= wfMsg( 'importusers-user-present-no-update', $newuserarray[0] ) . '<br />';
					$output .= wfMsg( 'importusers-user-present-not-update', $newuserarray[0] ) . '<br />';
				}
			}
			$summary['all']++;
		}
 
		$output .= '<b>' . wfMsg( 'importusers-log-summary' ) . '</b><br />';
		$output .= wfMsg( 'importusers-log-summary-all', $summary['all'] ) . '<br />';
		$output .= wfMsg( 'importusers-log-summary-added', $summary['added'] ) . '<br />';
		$output .= wfMsg( 'importusers-log-summary-updated', $summary['updated'] ) . '<br />';
##sm: line break added
# Extended by Jack D. Pond to inform importer if any email addresses didn't check out
	        $output.=wfMsg( 'importusers_log_summary_email_sent' ).': '.$summary['email_sent'].'<br />';
	        $output.=wfMsg( 'importusers_log_summary_email_failed' ).': '.$summary['email_failed'];
# Extension end

		return $output;
	}
 
# Extension by Jack D. Pond that adds imported user to the group in the last parameter in the line (4)
# under the following conditions:
#
# Logged on user importing must have 'userrights'
# The user must have checked the "Add users to groups" box
# The CSV line must have a group to add to(e.g. approved)
# The permission requested must be one of the available groups - does not create a new group
# The user has not already been assigned to that group
#
       function AddToGroup($u,$user_array,$add_to_group_checked){
                global $wgOut, $wgUser;
                if( $wgUser->isAllowed( 'userrights' ) && $add_to_group_checked && IsSet($user_array[4])) {
                        for( $i = 4 ; $i < sizeof( $user_array) ; $i++) {
#sm: added loop over array-size for multiple groups
                                if ( in_array($user_array[ $i],User::getAllGroups())){
                                        if ( !in_array($user_array[ $i],$u->getGroups())){
                                                $u->addGroup( $user_array[ $i] );
                                       }
                                }
                        }
                }
        }
}
