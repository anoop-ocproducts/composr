<?php
# MantisBT - a php based bugtracking system

# MantisBT is free software: you can redistribute it and/or modify
# it under the terms of the GNU General Public License as published by
# the Free Software Foundation, either version 2 of the License, or
# (at your option) any later version.
#
# MantisBT is distributed in the hope that it will be useful,
# but WITHOUT ANY WARRANTY; without even the implied warranty of
# MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
# GNU General Public License for more details.
#
# You should have received a copy of the GNU General Public License
# along with MantisBT.  If not, see <http://www.gnu.org/licenses/>.

	/**
	 * @package MantisBT
	 * @copyright Copyright (C) 2000 - 2002  Kenzaburo Ito - kenito@300baud.org
	 * @copyright Copyright (C) 2002 - 2010  MantisBT Team - mantisbt-dev@lists.sourceforge.net
	 * @link http://www.mantisbt.org
	 */
	 /**
	  * MantisBT Core API's
	  */
	require_once( 'core.php' );

	require_once( 'version_api.php' );

	form_security_validate( 'manage_proj_ver_add' );

	auth_reauthenticate();

	$f_project_id	= gpc_get_int( 'project_id' );
	$f_version		= gpc_get_string( 'version' );
	$f_add_and_edit = gpc_get_bool( 'add_and_edit_version' );

	access_ensure_project_level( config_get( 'manage_project_threshold' ), $f_project_id );

	if ( is_blank( $f_version ) ) {
		trigger_error( ERROR_EMPTY_FIELD, ERROR );
	}

	# We reverse the array so that if the user enters multiple versions
	#  they will likely appear with the last item entered at the top of the list
	#  (i.e. in reverse chronological order).  Unless we find a way to make the
	#  date_order fields different for each one, however, this is fragile, since
	#  the DB may actually pull the rows out in any order
	$t_versions = array_reverse( explode( '|', $f_version ) );
	$t_version_count = count( $t_versions );

	foreach ( $t_versions as $t_version ) {
		if ( is_blank( $t_version ) ) {
			continue;
		}

		$t_version = trim( $t_version );
		if ( version_is_unique( $t_version, $f_project_id ) ) {
			$t_version_id = version_add( $f_project_id, $t_version );
			event_signal( 'EVENT_MANAGE_VERSION_CREATE', array( $t_version_id ) );
		} else if ( 1 == $t_version_count ) {
			# We only error out on duplicates when a single value was
			#  given.  If multiple values were given, we just add the
			#  ones we can.  The others already exist so it isn't really
			#  an error.

			trigger_error( ERROR_VERSION_DUPLICATE, ERROR );
		}
	}

	form_security_purge( 'manage_proj_ver_add' );

	if ( true == $f_add_and_edit ) {
		$t_redirect_url = 'manage_proj_ver_edit_page.php?version_id='.$t_version_id;
	} else {
		$t_redirect_url = 'manage_proj_edit_page.php?project_id='  .$f_project_id;
	}

	html_page_top( null, $t_redirect_url );
?>

<br />
<div align="center">
<?php
	echo lang_get( 'operation_successful' ) . '<br />';

	print_bracket_link( $t_redirect_url, lang_get( 'proceed' ) );
?>
</div>

<?php
	html_page_bottom();
