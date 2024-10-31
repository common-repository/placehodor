<?php

namespace LNJ;

class PlaceHodor {
    private $rewrite_rules;

    /**
     * construct
     */
	public function __construct() {

		add_action( 'admin_init', array( $this, 'lnjph_plugin_version' ) );
		add_action( 'admin_init', array( $this, 'lnjph_settings_init' ) );

		add_action( 'init', array( $this, 'lnjph_add_rewrite_rules' ) );

		add_action( 'admin_menu', array( $this, 'lnjph_settings_page' ) );
		add_action( 'admin_enqueue_scripts', array( $this, 'lnjph_enqueue_scripts_base' ) );
		add_action( 'admin_enqueue_scripts', array( $this, 'lnjph_admin_enqueue_scripts' ) );

		add_action( 'wp', array( $this, 'lnjph_rewrite_process' ) );
		add_action( 'wp_enqueue_scripts', array( $this, 'lnjph_enqueue_scripts_base' ) );
		add_action( 'wp_enqueue_scripts', array( $this, 'lnjph_index_enqueue_scripts' ) );

		add_action( 'add_option_lnjph_settings_update', array( $this, 'lnjph_settings_update' ), 9999 );
		add_action( 'update_option_lnjph_settings_update', array( $this, 'lnjph_settings_update' ), 9999 );

		add_filter( 'query_vars', array( $this, 'lnjph_add_vars' ) );

		add_filter( 'plugin_action_links_placehodor/placehodor.php', array( $this, 'lnjph_settings_link' ) );
		add_filter( '404_template', array( $this, 'lnjph_are_you_there' ) );
		add_filter( 'get_post_metadata', array( $this, 'lnjph_get_post_metadata' ), 99, 4 );
		add_filter( 'wp_get_attachment_url', array( $this, 'lnjph_get_attachment_url' ), 99, 2 );

		register_activation_hook( LNJPH_PLUGIN_FILE, array( $this, 'lnjph_install' ) );
		register_deactivation_hook( LNJPH_PLUGIN_FILE, array( $this, 'lnjph_uninstall' ) );

		$this->rewrite_rules = array(
			array(
				'regex' => '^' . LNJPH_NAMESPACE . '/' . LNJPH_SLUG . '/?$',
				'query' => 'index.php?lnjph_rewrite=lnjph',
				'after' => 'top',
			),
		);
	}

    /*
     * Singleton
     */
	public static function lnjph_run() {
		new self();
	}

    /*
     * install
     */
	public function lnjph_install() {
		update_option( 'lnjph_install', true );
		update_option( 'lnjph_install_date', date_i18n( 'Y-m-d H:i:s' ) );
		update_option( 'lnjph_uninstall_date', null );
		//
		self::lnjph_create_sub();
		//
		if ( isset( $this->rewrite_rules ) && is_array( $this->rewrite_rules ) && count( $this->rewrite_rules ) > 0 ) {
			foreach ( $this->rewrite_rules as $rewrite_rule ) {
				add_rewrite_rule( $rewrite_rule['regex'], $rewrite_rule['query'], $rewrite_rule['after'] );
			}
		}
		//
		flush_rewrite_rules();
	}

    /**
     *
     */
	public function lnjph_uninstall() {
		update_option( 'lnjph_install', false );
		update_option( 'lnjph_install_date', null );
		update_option( 'lnjph_uninstall_date', date_i18n( 'Y-m-d H:i:s' ) );
		//
		self::lnjph_delete_sub();
	}

    /**
     * Check version and process what needed
     */
	public function lnjph_plugin_version() {
		// Change each time the plugin is up :
		$lnjph_plugin_version_current = get_option( 'lnjph_plugin_version' );
		if (
		false === $lnjph_plugin_version_current ||
		( ! empty( $lnjph_plugin_version_current ) && version_compare( $lnjph_plugin_version_current, LNJPH_PLUGIN_VERSION, '<' ) )
		) {
			update_option( 'lnjph_plugin_version', LNJPH_PLUGIN_VERSION );

			self::lnjph_check_sub();
		}
	}

    /*
     * Add query vars
     */
	public function lnjph_add_vars( $vars ) {
		$vars[] = 'lnjph_rewrite';
		//
		return $vars;
	}

    /*
     * Maintain rewrite rules
     */
	public function lnjph_add_rewrite_rules() {
		if ( isset( $this->rewrite_rules ) && is_array( $this->rewrite_rules ) && count( $this->rewrite_rules ) > 0 ) {
			foreach ( $this->rewrite_rules as $rewrite_rule ) {
				add_rewrite_rule( $rewrite_rule['regex'], $rewrite_rule['query'], $rewrite_rule['after'] );
			}
		}
	}

    /*
     * Display rewrite content needed
     */
	public function lnjph_rewrite_process() {
		$lnjph_rewrite = get_query_var( 'lnjph_rewrite' );

		if ( ! empty( $lnjph_rewrite ) && 'lnjph' === $lnjph_rewrite ) {
			http_response_code( 200 );
			require LNJPH_PATH . '/rewrites/placehodor.php';
			exit;
		}
	}

	public static function lnjph_create_sub() {
		//
        $filesystem    = self::lnjph_get_filesystem();
		$wp_upload_dir = wp_upload_dir();
		//
		$banner_src  = LNJPH_PATH . '/icon.png';
		$banner_dest = $wp_upload_dir['path'] . '/placehodor-default-post-thumbnail.png';

        $contents = $filesystem->get_contents( $banner_src );
        $filesystem->put_contents( $banner_dest, $contents );   // it's good, copy that

		$filetype = wp_check_filetype( basename( $banner_dest ), null );

		$attachment = array(
			'guid'           => $wp_upload_dir['url'] . '/' . basename( $banner_dest ),
			'post_mime_type' => $filetype['type'],
			'post_title'     => preg_replace( '/\.[^.]+$/', '', basename( $banner_dest ) ),
			'post_content'   => '',
			'post_status'    => 'inherit',
		);
		$attach_id  = wp_insert_attachment( $attachment, $banner_dest, 0 );
		require_once ABSPATH . 'wp-admin/includes/image.php';
		$attach_data = wp_generate_attachment_metadata( $attach_id, $banner_dest );
		wp_update_attachment_metadata( $attach_id, $attach_data );

		update_option( 'lnjph_default_post_thumbnail', $attach_id );
	}

	public static function lnjph_check_sub() {
		//
		$lnjph_default_post_thumbnail = get_option( 'lnjph_default_post_thumbnail' );
		if ( ! $lnjph_default_post_thumbnail ) {
			self::lnjph_create_sub();
		}
		$attached_file = get_attached_file( $lnjph_default_post_thumbnail );
		if ( $lnjph_default_post_thumbnail && ! $attached_file ) {
			self::lnjph_create_sub();
		}
		if ( $lnjph_default_post_thumbnail && $attached_file && ! file_exists( $attached_file ) ) {
			self::lnjph_create_sub();
		}
	}

	public static function lnjph_delete_sub() {
		//
		$lnjph_default_post_thumbnail = get_option( 'lnjph_default_post_thumbnail' );
		if ( $lnjph_default_post_thumbnail ) {
			wp_delete_attachment( $lnjph_default_post_thumbnail, true );
		}
		//
		update_option( 'lnjph_default_post_thumbnail', null );
	}

    /*
     * enqueue scripts
     */
	public function lnjph_enqueue_scripts_base() {
		wp_enqueue_style( LNJPH_SLUG . '-front', LNJPH_PLUGIN_URL . 'assets/css/front.css', array(), LNJPH_PLUGIN_VERSION, 'all' );
	}

	public function lnjph_admin_enqueue_scripts() {
		wp_enqueue_style( LNJPH_SLUG . '-admin', LNJPH_PLUGIN_URL . 'assets/css/admin.css', array(), LNJPH_PLUGIN_VERSION, 'all' );

		if ( ! did_action( 'wp_enqueue_media' ) ) {
			wp_enqueue_media();
		}

		wp_enqueue_script( LNJPH_SLUG . '-admin', LNJPH_PLUGIN_URL . 'assets/js/admin.js', array( 'jquery' ), LNJPH_PLUGIN_VERSION, false );

		$this->lnjph_enqueue_scripts_translations( LNJPH_SLUG . '-admin' );
	}

	public function lnjph_index_enqueue_scripts() {
		wp_enqueue_script( LNJPH_SLUG . '-front', LNJPH_PLUGIN_URL . 'assets/js/front.js', array( 'jquery' ), LNJPH_PLUGIN_VERSION, false );

		$this->lnjph_enqueue_scripts_translations( LNJPH_SLUG . '-front' );
	}

	public function lnjph_enqueue_scripts_translations( $handle ) {
		$lnjph_translations = array(
			'error_undefined' => __( 'Sorry, an error occured.', 'placehodor' ),
			'processing'      => __( 'Processing...', 'placehodor' ),
			'check'           => __( 'Check', 'placehodor' ),
			'finish'          => __( 'Finish', 'placehodor' ),
			'choose_image'    => __( 'Choose a new sub image', 'placehodor' ),
			'use_image'       => __( 'Use this image', 'placehodor' ),
			'set_image'       => __( 'Set a new one', 'placehodor' ),
			'ajaxurl'         => admin_url( 'admin-ajax.php' ),
			'sub_url'         => get_site_url() . '/' . LNJPH_NAMESPACE . '/' . LNJPH_SLUG . '/default.png',
		);
		wp_localize_script( $handle, 'placehodor', $lnjph_translations );
	}

    /**
     * Add settings link
     */
	public function lnjph_settings_link( $links ) {
		$settings_link = '<a href="' . admin_url( 'admin.php?page=lnjph-page' ) . '">' . __( 'Settings', 'placehodor' ) . '</a>';

		array_unshift( $links, $settings_link );

		return $links;
	}

    /*
     * Admin : add settings page
     */
	public function lnjph_settings_page() {
		add_menu_page(
            __( 'PlaceHodor - settings', 'placehodor' ),    // Page title
            __( 'PlaceHodor', 'placehodor' ),               // Menu title
            'manage_options',                               // Capability
            'lnjph-page',                                   // Slug of setting page
            array( $this, 'lnjph_settings_page_content' ),  // Call Back function for rendering
            // icon URL
            // position
		);
	}

    /*
     * Ini settings
     */
	public function lnjph_settings_init() {
		add_settings_section(
            'lnjph-settings-section',                       // id of the section
            __( 'PlaceHodor - settings', 'placehodor' ),    // title to be displayed
            '',                                             // callback function to be called when opening section
            'lnjph-page'                                    // page on which to display the section, this should be the same as the slug used in add_submenu_page()
		);
		//
		add_settings_section(
            'lnjph-settings-thumbs-section',                    // id of the section
            __( 'Thumbnail options - settings', 'placehodor' ), // title to be displayed
            '',                                                 // callback function to be called when opening section
            'lnjph-page'                                        // page on which to display the section, this should be the same as the slug used in add_submenu_page()
		);
		//
		register_setting(
            'lnjph-page',
            'lnjph_settings_update'
		);
		//
        register_setting(
            'lnjph-page',
            'lnjph_sub_mode'
        );
		//
        register_setting(
            'lnjph-page',
            'lnjph_custom_content_solo'
        );
		//
        register_setting(
            'lnjph-page',
            'placehold_co_font'
        );
		//
        register_setting(
            'lnjph-page',
            'placehold_co_text'
        );
		//
        register_setting(
            'lnjph-page',
            'placehold_co_color'
        );
		//
        register_setting(
            'lnjph-page',
            'placehold_co_bgcolor'
        );
		//
        register_setting(
            'lnjph-page',
            'placeimg_com_mode'
        );
		//
        register_setting(
            'lnjph-page',
            'placeimg_com_cat'
        );
		//
        register_setting(
            'lnjph-page',
            'picsum_photos_mode'
        );
		//
        register_setting(
            'lnjph-page',
            'lnjph_sub_thumbnail'
        );
        //
        $lnjph_sub_mode = get_option( 'lnjph_sub_mode' );
        add_settings_field(
            'lnjph_sub_mode',                       // id of the settings field
            __( 'Provider', 'placehodor' ),         // title
            array( $this, 'lnjph_render_field' ),   // callback function
            'lnjph-page',                           // page on which settings display
            'lnjph-settings-section',               // section on which to show settings
            array(
                'type'    => 'select',
                'id'      => 'lnjph_sub_mode',
                'name'    => 'lnjph_sub_mode',
                'value'   => $lnjph_sub_mode,
				'options' => array(
					'solo'          => __( 'Solo mode', 'placehodor' ),
					'placehold.co'  => __( 'Placehold.co', 'placehodor' ),
					'picsum.photos' => __( 'Picsum.photos', 'placehodor' ),
				),
            )
        );
        //
        $placehodor_sub_id = get_option( 'placehodor_sub_id' );
		ob_start();
		?>
		<?php if ( $placehodor_sub_id ) : ?>
			<a href="#" class="placehodor-button-upload">
				<img src="<?php echo wp_get_attachment_url( $placehodor_sub_id ); ?>" class="placehodor-sub-img" />
			</a>
		<?php else : ?>
			<a href="#" class="placehodor-button-upload button button-secondary"><?php _e( 'Set a new one', 'placehodor' ); ?></a>
		<?php endif; ?>

		<a href="#" class="placehodor-button-remove" style="display: <?php echo ( absint( $placehodor_sub_id ) > 0 ) ? 'block' : 'none'; ?>;"><?php _e( 'Remove sub image', 'placehodor' ); ?></a>

		<input type="hidden" id="placehodor_sub_id" name="placehodor_sub_id" value="<?php echo esc_attr( $placehodor_sub_id ); ?>" />

		<?php
		$custom_content = ob_get_clean();
		add_settings_field(
            'lnjph_custom_content_solo',            // id of the settings field
            __( 'Sub image', 'placehodor' ),        // title
            array( $this, 'lnjph_render_field' ),   // callback function
            'lnjph-page',                           // page on which settings display
            'lnjph-settings-section',               // section on which to show settings
            array(
                'type'           => 'custom_content',
                'custom_content' => $custom_content,
            )
        );
        //
        $placehold_co_font = get_option( 'placehold_co_font' );
        add_settings_field(
            'placehold_co_font',                    // id of the settings field
            __( 'Font', 'placehodor' ),             // title
            array( $this, 'lnjph_render_field' ),   // callback function
            'lnjph-page',                           // page on which settings display
            'lnjph-settings-section',               // section on which to show settings
            array(
                'type'    => 'select',
                'id'      => 'placehold_co_font',
                'name'    => 'placehold_co_font',
                'value'   => $placehold_co_font,
				'options' => array(
					'lato'             => __( 'Lato', 'placehodor' ),
					'lora'             => __( 'Lora', 'placehodor' ),
					'montserrat'       => __( 'Montserrat', 'placehodor' ),
					'open-sans'        => __( 'Open Sans', 'placehodor' ),
					'oswald'           => __( 'Oswald', 'placehodor' ),
					'playfair-display' => __( 'Playfair Display', 'placehodor' ),
					'pt-sans'          => __( 'PT Sans', 'placehodor' ),
					'raleway'          => __( 'Raleway', 'placehodor' ),
					'roboto'           => __( 'Roboto', 'placehodor' ),
					'source-sans-pro'  => __( 'Source Sans Pro', 'placehodor' ),
				),
            )
        );
		//
		$placehold_co_text = get_option( 'placehold_co_text' );
        add_settings_field(
            'placehold_co_text',                 // id of the settings field
            __( 'Text image', 'placehodor' ),       // title
            array( $this, 'lnjph_render_field' ),   // callback function
            'lnjph-page',                           // page on which settings display
            'lnjph-settings-section',               // section on which to show settings
            array(
                'type'        => 'text',
                'id'          => 'placehold_co_text',
                'name'        => 'placehold_co_text',
                'value'       => $placehold_co_text,
				'placeholder' => '',
            )
        );
        //
        $placehold_co_color = get_option( 'placehold_co_color' );
        add_settings_field(
            'placehold_co_color',                // id of the settings field
            __( 'Text image color', 'placehodor' ), // title
            array( $this, 'lnjph_render_field' ),   // callback function
            'lnjph-page',                           // page on which settings display
            'lnjph-settings-section',               // section on which to show settings
            array(
                'type'        => 'text',
                'id'          => 'placehold_co_color',
                'name'        => 'placehold_co_color',
                'value'       => $placehold_co_color,
				'placeholder' => '#000000',
            )
        );
        //
        $placehold_co_bgcolor = get_option( 'placehold_co_bgcolor' );
        add_settings_field(
            'placehold_co_bgcolor',                      // id of the settings field
            __( 'Background image color', 'placehodor' ),   // title
            array( $this, 'lnjph_render_field' ),           // callback function
            'lnjph-page',                                   // page on which settings display
            'lnjph-settings-section',                       // section on which to show settings
            array(
                'type'        => 'text',
                'id'          => 'placehold_co_bgcolor',
                'name'        => 'placehold_co_bgcolor',
                'value'       => $placehold_co_bgcolor,
				'placeholder' => '#FFF296',
            )
        );
        //
        $placeimg_com_cat = get_option( 'placeimg_com_cat' );
        add_settings_field(
            'placeimg_com_cat',                     // id of the settings field
            __( 'Image category', 'placehodor' ),   // title
            array( $this, 'lnjph_render_field' ),   // callback function
            'lnjph-page',                           // page on which settings display
            'lnjph-settings-section',               // section on which to show settings
            array(
                'type'    => 'select',
                'id'      => 'placeimg_com_cat',
                'name'    => 'placeimg_com_cat',
                'value'   => $placeimg_com_cat,
				'options' => array(
					'animals' => __( 'Animals', 'placehodor' ),
					'arch'    => __( 'Architecture', 'placehodor' ),
					'nature'  => __( 'Nature', 'placehodor' ),
					'people'  => __( 'People', 'placehodor' ),
					'tech'    => __( 'Tech', 'placehodor' ),
				),
            )
        );
        //
        $placeimg_com_mode = get_option( 'placeimg_com_mode' );
        add_settings_field(
            'placeimg_com_mode',                    // id of the settings field
            __( 'Image option', 'placehodor' ),     // title
            array( $this, 'lnjph_render_field' ),   // callback function
            'lnjph-page',                           // page on which settings display
            'lnjph-settings-section',               // section on which to show settings
            array(
                'type'    => 'select',
                'id'      => 'placeimg_com_mode',
                'name'    => 'placeimg_com_mode',
                'value'   => $placeimg_com_mode,
				'options' => array(
					'normal'    => __( 'Normal', 'placehodor' ),
					'grayscale' => __( 'Grayscale', 'placehodor' ),
					'sepia'     => __( 'Sepia', 'placehodor' ),
				),
            )
        );
        //
        $picsum_photos_mode = get_option( 'picsum_photos_mode' );
        add_settings_field(
            'picsum_photos_mode',                   // id of the settings field
            __( 'Image option', 'placehodor' ),     // title
            array( $this, 'lnjph_render_field' ),   // callback function
            'lnjph-page',                           // page on which settings display
            'lnjph-settings-section',               // section on which to show settings
            array(
                'type'    => 'select',
                'id'      => 'picsum_photos_mode',
                'name'    => 'picsum_photos_mode',
                'value'   => $picsum_photos_mode,
				'options' => array(
					'normal'    => __( 'Normal', 'placehodor' ),
					'grayscale' => __( 'Grayscale', 'placehodor' ),
					'blur'      => __( 'Blur', 'placehodor' ),
				),
            )
        );
		//
		add_settings_field(
            'lnjph_custom_content_sample',          // id of the settings field
            __( 'Sample', 'placehodor' ),           // title
            array( $this, 'lnjph_render_field' ),   // callback function
            'lnjph-page',                           // page on which settings display
            'lnjph-settings-section',               // section on which to show settings
            array(
                'type'           => 'custom_content',
                'custom_content' => '<div class="sample">' . __( 'Processing...', 'placehodor' ) . '</div>',
            )
        );
        //
        $lnjph_sub_thumbnail = get_option( 'lnjph_sub_thumbnail' );
        add_settings_field(
            'lnjph_sub_thumbnail',                              // id of the settings field
            __( 'Set default post thumbnail', 'placehodor' ),   // title
            array( $this, 'lnjph_render_field' ),               // callback function
            'lnjph-page',                                       // page on which settings display
            'lnjph-settings-thumbs-section',                    // section on which to show settings
            array(
                'type'    => 'select',
                'id'      => 'lnjph_sub_thumbnail',
                'name'    => 'lnjph_sub_thumbnail',
                'value'   => $lnjph_sub_thumbnail,
				'options' => array(
					false => __( 'No', 'placehodor' ),
					true  => __( 'Yes', 'placehodor' ),
				),
            )
        );
	}

    /*
     * Render settings fields
     */
    public function lnjph_render_field( $args ) {
        $value = ( isset( $args['value'] ) && ! empty( $args['value'] ) ) ? esc_attr( $args['value'] ) : '';
        switch ( $args['type'] ) {
            case 'select':
				?>
				<select id="<?php echo esc_attr( $args['id'] ); ?>" name="<?php echo esc_attr( $args['id'] ); ?>">
				<?php if ( isset( $args['options'] ) && is_array( $args['options'] ) && count( $args['options'] ) > 0 ) : ?>
					<?php foreach ( $args['options'] as $key => $label ) : ?>
						<option
							value="<?php echo esc_attr( $key ); ?>"
							<?php if ( $key == $value ) : ?>
								selected="selected"
							<?php endif; ?>>
							<?php echo esc_attr( $label ); ?>
						</option>
					<?php endforeach; ?>
				<?php endif; ?>
				</select>
				<?php
                break;
            case 'custom_content':
				echo $args['custom_content'];
                break;
            default:
				?>
                <input
					id="<?php echo esc_attr( $args['id'] ); ?>"
					type="text"
					name="<?php echo esc_attr( $args['name'] ); ?>"
					value="<?php echo esc_attr( $value ); ?>"
					placeholder="<?php echo esc_attr( $args['placeholder'] ); ?>"
					class="regular-text" />
				<?php
                break;
        }
    }


    /*
     * Settings page content
     */
	public function lnjph_settings_page_content() {
		// check user capabilities
		if ( ! current_user_can( 'manage_options' ) ) {
			return;
		}
		?>
        <div class="wrap">
			<?php settings_errors(); ?>
            <form method="post" action="options.php" id="fplacehodor">
				<input type="hidden" id="lnjph_settings_update" name="lnjph_settings_update" value="<?php echo date_i18n( 'Y-m-d H:i:s' ); ?>" />
				<?php wp_nonce_field( 'lnjph-settings', 'lnjph_settings' ); ?>
				<?php settings_fields( 'lnjph-page' ); ?>
				<?php do_settings_sections( 'lnjph-page' ); ?>
				<?php submit_button(); ?>
            </form>
        </div>
		<?php
	}

	/*
	* Update options
	*/
	public function lnjph_settings_update() {
		if ( isset( $_POST['lnjph_settings'] ) ) {
			if ( ! wp_verify_nonce( $_POST['lnjph_settings'], 'lnjph-settings' ) ) {
				wp_die( __( 'Sorry, your nonce did not verify.', 'placehodor' ) );
			} else {
				if ( isset( $_POST['placehodor_sub_id'] ) ) :
					update_option( 'placehodor_sub_id', absint( $_POST['placehodor_sub_id'] ) );
				endif;
			}
		}
	}

	/*
	* Get filesystem function
	*/
	public static function lnjph_get_filesystem() {
		static $filesystem;
		if ( $filesystem ) {
			return $filesystem;
		}
		require_once ABSPATH . 'wp-admin/includes/class-wp-filesystem-base.php';
		require_once ABSPATH . 'wp-admin/includes/class-wp-filesystem-direct.php';

		$filesystem = new \WP_Filesystem_Direct( new \stdClass() );

		if ( ! defined( 'FS_CHMOD_DIR' ) ) {
			define( 'FS_CHMOD_DIR', ( @fileperms( ABSPATH ) & 0777 | 0755 ) );
		}
		if ( ! defined( 'FS_CHMOD_FILE' ) ) {
			define( 'FS_CHMOD_FILE', ( @fileperms( ABSPATH . 'index.php' ) & 0777 | 0644 ) );
		}

		return $filesystem;
	}

    /*
    * Replace image
    */
	public function lnjph_are_you_there( $template ) {
		global $wp_query;

		if ( ! isset( $wp_query->query['pagename'] ) && ! isset( $_SERVER['REQUEST_URI'] ) ) {
			return $template;
		}

		$url_requested = ( isset( $wp_query->query['pagename'] ) ) ? $wp_query->query['pagename'] : $_SERVER['REQUEST_URI'];
		$file_url      = site_url( $url_requested );

		$image_url = self::lnjph_get_sub_image_url( $file_url );

		if ( $image_url ) {
			$status = wp_remote_get( $image_url, array( 'sslverify' => false ) );
			if ( ! is_wp_error( $status ) ) {
				$headers = $status['headers'];
				if (
					isset( $headers['content-type'] ) &&
					preg_match(
						'/^(image\/jpeg|image\/png)/',
						$headers['content-type'],
						$matches
                    )
				) {
					foreach ( $headers as $key => $value ) {
						if ( is_string( $value ) ) {
							header( $key . ': ' . $value );
						}
					}
					echo $status['body'];
					exit;
				}
			}
		}
		return $template;
	}

    /*
    * Check if is image
    * little trick for webp
    */
	public static function lnjph_is_image( $url ) {
		$check = wp_check_filetype( site_url( $url ) );

		if ( is_array( $check ) && isset( $check['type'] ) && preg_match( '/^image/', $check['type'] ) ) {
			return $check['type'];
		} elseif ( preg_match( '/\.webp$/', $url ) ) {
			return 'image/webp';
		}

		return false;
	}

    /*
    * Check if file exists
    */
	public static function lnjph_file_exists( $url ) {
		$status = wp_remote_get( $url, array( 'sslverify' => false ) );
		if ( ! is_wp_error( $status ) && isset( $status['response']['code'] ) && 200 === $status['response']['code'] ) {
			return true;
		}

		return false;
	}

    /*
    * get image size
    */
	public static function lnjph_get_size( $url ) {
		preg_match_all( '/-([0-9]+)x([0-9]+)/', $url, $matches );
		if ( isset( $matches ) && is_array( $matches ) && count( $matches ) > 2 ) {
			if ( ! empty( end( $matches[1] ) ) && ! empty( end( $matches[2] ) ) ) {
				return array( end( $matches[1] ), end( $matches[2] ) );
			}
        }

		return false;
	}

    /*
    * get sub image url
    */
	public function lnjph_get_sub_image_url( $image_missing_url ) {
		$file_type = self::lnjph_is_image( $image_missing_url );
		$size      = self::lnjph_get_size( $image_missing_url );

		if ( $size && is_array( $size ) && 2 === count( $size ) ) {
			$width  = $size[0];
			$height = $size[1];
		} else {
			$width  = 1440;
			$height = 900;
		}

		if ( $file_type ) {
			switch ( get_option( 'lnjph_sub_mode' ) ) {
				case 'solo':
					$placehodor_sub_id = get_option( 'placehodor_sub_id' );
					if ( $placehodor_sub_id ) {
						$image_url = wp_get_attachment_url( $placehodor_sub_id ); // default size

						//
						if ( ! $image_url ) {
							return false;
						}

						// if thumb size detected
						if ( $size && is_array( $size ) && 2 === count( $size ) ) {
							// check if thumb already exists
							$attachment = wp_get_attachment_image_src( $placehodor_sub_id, $size );
							if ( $attachment && is_array( $attachment ) && count( $attachment ) > 0 ) {
								$image_url = $attachment[0];
								if ( isset( $attachment[3] ) && ! $attachment[3] ) {
									require_once ABSPATH . 'wp-admin/includes/image.php';

									$image_path = wp_get_original_image_path( $placehodor_sub_id );
									add_image_size( LNJPH_SLUG . '-' . $size[0] . 'x' . $size[1], $size[0], $size[1], true );
									wp_generate_attachment_metadata( $placehodor_sub_id, $image_path );
									$attachment = wp_get_attachment_image_src( $placehodor_sub_id, $size );
									if ( $attachment && is_array( $attachment ) && count( $attachment ) > 0 ) {
										$image_url = $attachment[0];
									}
								}
							}
						}
					} else {
						return false;
					}
					break;
				case 'placeimg.com':
					$placeimg_com_cat  = ( get_option( 'placeimg_com_cat' ) ) ? get_option( 'placeimg_com_cat' ) : 'animals';
					$placeimg_com_mode = ( get_option( 'placeimg_com_mode' ) ) ? get_option( 'placeimg_com_mode' ) : 'normal';
					$image_url         = 'https://placeimg.com/' . $width . '/' . $height . '/' . $placeimg_com_cat . '/' . $placeimg_com_mode;
					break;
				case 'placehold.co':
					$placehold_co_font    = ( get_option( 'placehold_co_font' ) ) ? get_option( 'placehold_co_font' ) : 'lato';
					$placehold_co_color   = ( get_option( 'placehold_co_color' ) ) ? get_option( 'placehold_co_color' ) : '#000000';
					$placehold_co_bgcolor = ( get_option( 'placehold_co_bgcolor' ) ) ? get_option( 'placehold_co_bgcolor' ) : '#FFF296';
					$placehold_co_text    = ( get_option( 'placehold_co_text' ) ) ? get_option( 'placehold_co_text' ) : '';
					$placehold_co_color   = str_replace( '#', '', $placehold_co_color );
					$placehold_co_bgcolor = str_replace( '#', '', $placehold_co_bgcolor );
					$placehold_co_text    = esc_html( $placehold_co_text );
					$image_url            = 'https://placehold.co/' . $width . 'x' . $height . '/' . $placehold_co_bgcolor . '/' . $placehold_co_color . '/png/?text=' . str_replace( '_', '+', $placehold_co_text ) . '&font=' . $placehold_co_font;
					break;
				case 'picsum.photos':
				default:
					$image_url = 'https://picsum.photos/' . $width . '/' . $height . '?random=1';
					switch ( get_option( 'picsum_photos_mode' ) ) {
						case 'grayscale':
							$image_url .= '&grayscale';
							break;
						case 'blur':
							$image_url .= '&blur=2';
							break;
					}
					break;
			}
			return $image_url;
		}
		return false;
	}

    /*
     * Sub post thumbnail
     */
	public function lnjph_get_post_metadata( $metadata, $object_id, $meta_key, $single ) {
		if ( ! get_option( 'lnjph_sub_thumbnail' ) ) {
			return $metadata;
		}
		//
		if ( '_thumbnail_id' !== $meta_key ) {
			return $metadata;
		}
		//
		remove_filter( 'get_post_metadata', array( $this, 'lnjph_get_post_metadata' ), 99, 4 );
		$_thumbnail_id = get_post_meta( $object_id, '_thumbnail_id', 1 );
		add_filter( 'get_post_metadata', array( $this, 'lnjph_get_post_metadata' ), 99, 4 );
		//
		if ( ! empty( $_thumbnail_id ) ) {
			return $_thumbnail_id;
		}
		//
		if ( get_option( 'placehodor_sub_id' ) ) {
			return get_option( 'placehodor_sub_id' );
		}
		//
		if ( get_option( 'lnjph_default_post_thumbnail' ) ) {
			return get_option( 'lnjph_default_post_thumbnail' );
		}

		return $metadata;
	}

    /*
     * Replace Default Sub Image URL
     */
	public function lnjph_get_attachment_url( $url, $post_id ) {
		if (
			$post_id === (int) get_option( 'lnjph_default_post_thumbnail' ) &&
			'solo' !== get_option( 'lnjph_sub_mode' ) &&
			! get_option( 'placehodor_sub_id' )
		) {
			return '/' . LNJPH_NAMESPACE . '/' . LNJPH_SLUG . '/' . LNJPH_SLUG . '.png';
		}
		return $url;
	}
}
