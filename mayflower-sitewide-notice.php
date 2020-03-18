<?php
/**
 * Plugin Name:     Mayflower Sitewide Notice
 * Plugin URI:      https://github.com/bellevuecollege/mayflower-sitewide-notice
 * Description:     Display sitewide notice across all pages in a Mayflower site
 * Author:          Bellevue College IT Services
 * Author URI:      https://www.bellevuecollege.edu/
 * Text Domain:     mfsn
 * Version:         0.0.0
 *
 * @package         Mayflower_Sitewide_Notice
 */

// Your code starts here.
/**
 * Register options page
 */

class MFSN {

	public static function active() {
		$options = get_option( 'mfsn_options' );
		if ( 'on' === $options['mfsn_enable'] && '' !== $options['mfsn_source'] ) {
			$src_options = get_blog_option( $options['mfsn_source'], 'mfsn_options' );
			if ( 'on' === $src_options['mfsn_enable'] ) {
				return true;
			}

		} elseif ( 'on' === $options['mfsn_enable'] ) {
			return true;
		} else {
			return false;
		}
	}

	public static function display() {
		$options = get_option( 'mfsn_options' );

		if ( 'on' === $options['mfsn_enable'] && '' !== $options['mfsn_source'] ) {
			$src_options = get_blog_option( $options['mfsn_source'], 'mfsn_options' );
			if ( 'on' === $src_options['mfsn_enable'] ) {
				echo apply_filters( 'the_content', $src_options['mfsn_message'] );
			}

		} elseif ( 'on' === $options['mfsn_enable'] ) {
			echo apply_filters( 'the_content', $options['mfsn_message'] );
		}
	}

}
class MFSN_Options_Page {

	/**
	 * Constructor.
	 */
	function __construct() {
		add_action( 'admin_init', array( $this, 'settings') );
		add_action( 'admin_menu', array( $this, 'admin_menu' ) );

	}

	/**
	 * Registers a new settings page under Settings.
	 */
	function admin_menu() {
		add_menu_page(
			__( 'Display Sitewide Notice', 'mfsn' ),
			__( 'Sitewide Notice', 'mfsn' ),
			'manage_options',
			'mfsn',
			array(
				$this,
				'settings_page_callback'
			),
			'dashicons-megaphone',
		);
	}

	function settings() {
		register_setting(
			'mfsn',
			'mfsn_options'
		);

		add_settings_section(
			'mfsn_section',
			'Sitewide Notice Settings',
			array(
				$this,
				'settings_section_callback'
			),
			'mfsn'
		);

		add_settings_field(
			'mfsn_enable',
			__( 'Display Notice Sitewide?', 'mfsn' ),
			array(
				$this,
				'enable_field_render'
			),
			'mfsn',
			'mfsn_section'
		);
		add_settings_field(
			'mfsn_message',
			__( 'Notice Text', 'mfsn' ),
			array(
				$this,
				'notification_field_render'
			),
			'mfsn',
			'mfsn_section'
		);
		add_settings_field(
			'mfsn_source',
			__( 'Source Site ID (Optional)', 'mfsn' ),
			array(
				$this,
				'source_field_render'
			),
			'mfsn',
			'mfsn_section'
		);
	}

	/**
	 * Settings page display callback.
	 */
	function settings_page_callback() {
		?>
		<form action='options.php' method='post'>	
			<?php
			settings_fields( 'mfsn' );
			do_settings_sections( 'mfsn' );
			submit_button();
			?>
	
		</form>
		<?php
	}
	function settings_section_callback() {
	}
	function enable_field_render() {
		$options = get_option( 'mfsn_options' );
		?>
			<input type='checkbox' name="mfsn_options[mfsn_enable]" <?php echo $options['mfsn_enable'] === 'on' ? 'checked' : ''; ?>>

		<?php
	}
	function notification_field_render() {
		$options = get_option( 'mfsn_options' );

		wp_editor(
			$options['mfsn_message'],
			'mfsn_options-mfsn_message',
			array(
				'textarea_name' => 'mfsn_options[mfsn_message]',
				'wpautop' => true,
				'tinymce' => array(
					'quicktags' => array( 'buttons' => 'strong,em,del,ul,ol,li,close' ),
				)
			)
		);
	}
	function source_field_render() {
		$sites = get_sites(
			array(
				'number'  => '10000', //Arbitrary high number of sites.
				'orderby' => 'path',
				'public'  => 1,
			)
		);
		$options = get_option( 'mfsn_options' );
		?>
			<select name="mfsn_options[mfsn_source]">
			<option value='' <?php echo '' === $options['mfsn_source'] ? 'selected="true"' : '' ?>>[none]</option>
				<?php
					foreach( $sites as $site ) {
						?>
						<option value='<?php echo $site->blog_id ?>' <?php echo $site->blog_id === $options['mfsn_source'] ? 'selected="true"' : '' ?>><?php echo $site->path ?></option>
						<?php
					}
				?>
			</select>

		<?php
	}
}

new MFSN_Options_Page;