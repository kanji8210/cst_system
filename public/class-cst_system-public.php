<?php

/**
 * The public-facing functionality of the plugin.
 *
 * @link       https://https://github.com/kanji8210/cst_system
 * @since      1.0.0
 *
 * @package    Cst_system
 * @subpackage Cst_system/public
 */

/**
 * The public-facing functionality of the plugin.
 *
 * Defines the plugin name, version, and two examples hooks for how to
 * enqueue the public-facing stylesheet and JavaScript.
 *
 * @package    Cst_system
 * @subpackage Cst_system/public
 * @author     Dennis <denisdekemet@gmail.com>
 */
class Cst_system_Public {

	/**
	 * The ID of this plugin.
	 *
	 * @since    1.0.0
	 * @access   private
	 * @var      string    $plugin_name    The ID of this plugin.
	 */
	private $plugin_name;

	/**
	 * The version of this plugin.
	 *
	 * @since    1.0.0
	 * @access   private
	 * @var      string    $version    The current version of this plugin.
	 */
	private $version;

	/**
	 * Initialize the class and set its properties.
	 *
	 * @since    1.0.0
	 * @param      string    $plugin_name       The name of the plugin.
	 * @param      string    $version    The version of this plugin.
	 */
	public function __construct( $plugin_name, $version ) {

		$this->plugin_name = $plugin_name;
		$this->version = $version;

		add_action( 'init', array( $this, 'register_shortcodes' ) );


	}

	/**
	 * Register the stylesheets for the public-facing side of the site.
	 *
	 * @since    1.0.0
	 */
	public function enqueue_styles() {

		/**
		 * This function is provided for demonstration purposes only.
		 *
		 * An instance of this class should be passed to the run() function
		 * defined in Cst_system_Loader as all of the hooks are defined
		 * in that particular class.
		 *
		 * The Cst_system_Loader will then create the relationship
		 * between the defined hooks and the functions defined in this
		 * class.
		 */

		wp_enqueue_style( $this->plugin_name, plugin_dir_url( __FILE__ ) . 'css/cst_system-public.css', array(), $this->version, 'all' );

	}

	/**
	 * Register the JavaScript for the public-facing side of the site.
	 *
	 * @since    1.0.0
	 */
	public function enqueue_scripts() {

		/**
		 * This function is provided for demonstration purposes only.
		 *
		 * An instance of this class should be passed to the run() function
		 * defined in Cst_system_Loader as all of the hooks are defined
		 * in that particular class.
		 *
		 * The Cst_system_Loader will then create the relationship
		 * between the defined hooks and the functions defined in this
		 * class.
		 */

		wp_enqueue_script( $this->plugin_name, plugin_dir_url( __FILE__ ) . 'js/cst_system-public.js', array( 'jquery' ), $this->version, false );

	}

	public function register_shortcodes() {
		add_shortcode( 'ctm_package', array( $this, 'shortcode_package' ) );
		add_shortcode( 'ctm_packages', array( $this, 'shortcode_packages' ) );
	}

	public function shortcode_package( $atts ) {
		$atts = shortcode_atts( array( 'id' => 0 ), $atts, 'ctm_package' );
		$post_id = intval( $atts['id'] );
		if ( ! $post_id ) return '';
		$post = get_post( $post_id );
		if ( ! $post || $post->post_type !== 'ctm_package' ) return '';

		ob_start();
		// reuse template if present
		$tpl = plugin_dir_path( __FILE__ ) . 'templates/shortcode-ctm_package.php';
		if ( is_file( $tpl ) ) {
			include $tpl;
			return ob_get_clean();
		}
		return ob_get_clean();
	}

	/**
	 * Shortcode to list multiple packages.
	 * Usage: [ctm_packages posts_per_page="6" type="Adventure" orderby="date" order="DESC"]
	 */
	public function shortcode_packages( $atts ) {
		$atts = shortcode_atts( array(
			'posts_per_page' => 6,
			'type' => '',
			'orderby' => 'date',
			'order' => 'DESC',
		), $atts, 'ctm_packages' );

		$args = array(
			'post_type' => 'ctm_package',
			'posts_per_page' => intval( $atts['posts_per_page'] ),
			'orderby' => sanitize_text_field( $atts['orderby'] ),
			'order' => sanitize_text_field( $atts['order'] ),
		);

		if ( ! empty( $atts['type'] ) ) {
			$args['tax_query'] = array(
				array(
					'taxonomy' => 'package_type',
					'field' => 'name',
					'terms' => sanitize_text_field( $atts['type'] ),
				),
			);
		}

		$query = new WP_Query( $args );
		if ( ! $query->have_posts() ) return '<p>' . esc_html__( 'No packages found.', 'cst_system' ) . '</p>';

		ob_start();
		$tpl = plugin_dir_path( __FILE__ ) . 'templates/shortcode-ctm_packages.php';
		if ( is_file( $tpl ) ) {
			include $tpl;
		} else {
			while ( $query->have_posts() ) { $query->the_post(); ?>
				<div class="ctm-package-inline">
					<h3><?php the_title(); ?></h3>
					<div><?php the_excerpt(); ?></div>
					<p><a href="<?php the_permalink(); ?>"><?php esc_html_e( 'View details', 'cst_system' ); ?></a></p>
				</div>
			<?php }
			wp_reset_postdata();
		}

		return ob_get_clean();
	}

}
