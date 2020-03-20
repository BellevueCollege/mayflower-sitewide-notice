<?php
/**
 * Plugin Name:     Mayflower Sitewide Notice
 * Plugin URI:      https://github.com/bellevuecollege/mayflower-sitewide-notice
 * Description:     Display sitewide notice across all pages in a Mayflower site
 * Author:          Bellevue College IT Services
 * Author URI:      https://www.bellevuecollege.edu/
 * Text Domain:     mfsn
 * Version:         1.2
 *
 * @package         Mayflower_Sitewide_Notice
 */

// Your code starts here.
/**
 * Register options page
 */

class MFSN {

	public static function active() {
		if ( 'on' === get_option( 'mfsn_enable' ) && '' !== get_option( 'mfsn_source' ) ) {
			$src_site = get_option( 'mfsn_source' );
			if ( 'on' === get_blog_option( $src_site, 'mfsn_enable' ) ) {
				return true;
			}

		} elseif ( 'on' === get_option( 'mfsn_enable' ) ) {
			return true;
		} else {
			return false;
		}
	}

	public static function display() {
		if ( 'on' === get_option( 'mfsn_enable' ) && '' !== get_option( 'mfsn_source' ) ) {
			$src_site = get_option( 'mfsn_source' );
			if ( 'on' === get_blog_option( $src_site, 'mfsn_enable' ) ) {
				echo apply_filters( 'the_content', get_blog_option( get_option( 'mfsn_source' ), 'mfsn_message' ) );
			}

		} elseif ( 'on' === $options['mfsn_enable'] ) {
			echo apply_filters( 'the_content', get_option( 'mfsn_message' ) );
		}
	}

}
class MFSN_Options_Page {

	/**
	 * Constructor.
	 */
	function __construct() {
		add_action( 'admin_menu', array( $this, 'admin_menu' ) );
	}

	/**
	 * Registers a new settings page under Settings.
	 */
	function admin_menu() {
		add_menu_page(
			__( 'Display Sitewide Notice', 'mfsn' ),
			__( 'Sitewide Notice', 'mfsn' ),
			'edit_pages',
			'mfsn',
			array(
				$this,
				'settings_page_callback'
			),
			'dashicons-megaphone',
		);
	}

	/**
	 * Settings page display callback.
	 */
	function settings_page_callback() {

		if ($_SERVER['REQUEST_METHOD'] === 'POST') {
			if (!current_user_can('edit_pages')) {
				wp_die('Unauthorized user!');
			}

			check_admin_referer( 'mfsn_nonce' );
			
			$value = $_POST['mfsn_enable'] === 'on' ? 'on' : '';
			update_option( 'mfsn_enable', $value );

			if (isset($_POST['mfsn_message'])) {
				$value = wp_kses_post( $_POST['mfsn_message'] );
				update_option( 'mfsn_message', $value );
			}
			if (isset($_POST['mfsn_source'])) {
				$value = $_POST['mfsn_source'] !== '0' ? intval($_POST['mfsn_source']) : '' ;
				update_option( 'mfsn_source', $value );
			}
		}
		?>
		<form method='post'>
			<?php wp_nonce_field( 'mfsn_nonce' ); ?>
			<h2>Sitewide Notice Settings</h2>
			<table class="form-table" role="presentation">
				<tbody>
					<tr>
						<th scope="row"><label for='mfsn_enable'>Notice Sitewide?</label></th>
						<td scope="row">
							<input type='checkbox' id="mfsn_enable" name="mfsn_enable" <?php echo get_option( 'mfsn_enable' ) === 'on' ? 'checked' : ''; ?>>
						</td>
					</tr>

					<tr>
						<th scope="row"><label for='mfsn_message'>Notice Text</label></th>
						<td scope="row">
							<?php
							wp_editor(
								get_option( 'mfsn_message' ),
								'mfsn_message',
								array(
									'textarea_name' => 'mfsn_message',
									'wpautop' => true,
									'tinymce' => array(
										'quicktags' => array( 'buttons' => 'strong,em,del,ul,ol,li,close' ),
									)
								)
							);
							?>
						</td>
					</tr>
					<tr>
						<th scope="row"><label for='mfsn_source'>Source Site ID (optional)</label></th>
						<td scope="row">
							<?php
							$sites = get_sites(
								array(
									'number'  => '10000', //Arbitrary high number of sites.
									'orderby' => 'path',
									'public'  => 1,
								)
							);
							?>
							<select id="mfsn_source" name="mfsn_source">
								<option value='' <?php echo '' === get_option( 'mfsn_source' ) ? 'selected="true"' : '' ?>>[none]</option>
								<?php
									foreach( $sites as $site ) {
										?>
										<option value='<?php echo $site->blog_id ?>' <?php echo $site->blog_id === (string)get_option( 'mfsn_source' ) ? 'selected="true"' : '' ?>><?php echo $site->path ?></option>
										<?php
									}
								?>
							</select>
						</td>
					</tr>
					<tr>
						<td></td>
						<td><input type="submit" value="Save" class="button button-primary button-large"></td>
					</tr>
				</tbody>
			</table>
		</form>
		<?php
	}
	
}

new MFSN_Options_Page;