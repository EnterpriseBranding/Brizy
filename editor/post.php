<?php if ( ! defined( 'ABSPATH' ) ) {
	die( 'Direct access forbidden.' );
}

class Brizy_Editor_Post extends Brizy_Admin_Serializable {

	const BRIZY_POST = 'brizy-post';
	const BRIZY_POST_SIGNATURE_KEY = 'brizy-post-signature';
	const BRIZY_POST_HASH_KEY = 'brizy-post-hash';


	/**
	 * @var Brizy_Editor_API_Page
	 */
	protected $api_page;

	/**
	 * @var int
	 */
	protected $wp_post_id;

	/**
	 * @var string
	 */
	protected $compiled_html_body;

	/**
	 * @var string
	 */
	protected $compiled_html_head;

	/**
	 * @var bool
	 */
	protected $needs_compile;

	/**
	 * @var bool
	 */
	//protected $store_assets;

	/**
	 * @var array
	 */
	//protected $assets = array();

	/**
	 * Json for the editor.
	 *
	 * @var string
	 */
	protected $editor_data;

	/**
	 * Brizy_Editor_Post constructor.
	 *
	 * @param $wp_post_id
	 */
	public function __construct( $wp_post_id ) {
		$this->wp_post_id = (int) $wp_post_id;
	}


	/**
	 * @return string
	 */
	public function serialize() {
		$get_object_vars = get_object_vars( $this );

		unset( $get_object_vars['wp_post_id'] );
		unset( $get_object_vars['api_page'] );
		unset( $get_object_vars['store_assets'] );
		unset( $get_object_vars['assets'] );

		return serialize( $get_object_vars );
	}

	/**
	 * @param $data
	 */
	public function unserialize( $data ) {
		parent::unserialize( $data ); // TODO: Change the autogenerated stub

		if($this->get_api_page())
		{
			$save_data         = $this->get_api_page()->get_content();

			$this->editor_data = $save_data;
		}

		unset($this->api_page);
	}

	/**
	 * @param $apost
	 *
	 * @return Brizy_Editor_Post
	 * @throws Brizy_Editor_Exceptions_NotFound
	 * @throws Brizy_Editor_Exceptions_UnsupportedPostType
	 */
	public static function get( $apost ) {

		$wp_post_id = $apost;

		if ( $apost instanceof WP_Post ) {
			$wp_post_id = $apost->ID;
		}

		if ( ! in_array( ( $type = get_post_type( $wp_post_id ) ), brizy()->supported_post_types() ) ) {
			throw new Brizy_Editor_Exceptions_UnsupportedPostType(
				"Brizy editor doesn't support '{$type}' post type 1"
			);
		}

		$brizy_editor_storage_post = Brizy_Editor_Storage_Post::instance( $wp_post_id );

		$post = $brizy_editor_storage_post->get( self::BRIZY_POST );

		$post->wp_post_id = $wp_post_id;

		return $post;
	}

	/**
	 * @param $project
	 * @param $post
	 *
	 * @return Brizy_Editor_Post
	 * @throws Brizy_Editor_Exceptions_UnsupportedPostType
	 * @throws Exception
	 */
	public static function create( $project, $post ) {
		if ( ! in_array( ( $type = get_post_type( $post->ID ) ), brizy()->supported_post_types() ) ) {
			throw new Brizy_Editor_Exceptions_UnsupportedPostType(
				"Brizy editor doesn't support '$type' post type 2"
			);
		}
		Brizy_Logger::instance()->notice( 'Create post', array( $project, $post ) );

		//$api_page_obj = Brizy_Editor_API_Page::get()->set_title( $post->post_title );
		//$api_page     = Brizy_Editor_User::get()->create_page( $project, $api_page_obj );

		$post = new self( $post->ID );

		return $post;
	}

//	public function updatePageData( $data = null ) {
//
//		Brizy_Logger::instance()->notice( 'Update page data', array( 'new_data' => $data ) );
//		$this->api_page = new Brizy_Editor_API_Page( $data );
//		$this->save_locally();
//	}

	/**
	 * @return bool
	 */
	public function save() {

		try {
			//$brizy_editor_user = Brizy_Editor_User::get();
			//$project           = Brizy_Editor_Project::get();
			//$api_project       = $project->get_api_project();
			//$updated_page      = $brizy_editor_user->update_page( $api_project, $this->api_page );
			//$this->updatePageData( $updated_page );

			// store the signature only once
			//if ( ! ( $signature = get_post_meta( $this->wp_post_id, self::BRIZY_POST_SIGNATURE_KEY, true ) ) ) {
				//update_post_meta( $this->wp_post_id, self::BRIZY_POST_SIGNATURE_KEY, Brizy_Editor_Signature::get() );
				//update_post_meta( $this->wp_post_id, self::BRIZY_POST_HASH_KEY, $this->get_api_page()->get_id() );
			//}

			$this->storage()->set( self::BRIZY_POST, $this );

		} catch ( Exception $exception ) {
			Brizy_Logger::instance()->exception( $exception );
			return false;
		}
	}

	/**
	 * @deprecated;
	 */
	public function get_api_page() {
		return $this->api_page;
	}

	/**
	 * @return mixed
	 */
	public function get_id() {
		return $this->wp_post_id;
	}

	public function get_editor_data() {
		return isset( $this->editor_data ) ? $this->editor_data : '';
	}

	public function set_editor_data( $content ) {
		$this->editor_data = stripslashes( $content );

		return $this;
	}

	public function get_compiled_html_body() {
		return $this->compiled_html_body;
	}

	public function get_compiled_html_head() {
		return $this->compiled_html_head;
	}

	public function set_compiled_html_body( $html ) {
		$this->compiled_html_body = $html;

		return $this;
	}

	public function set_compiled_html_head( $html ) {
		// remove all title and meta tags.
		$this->compiled_html_head = $this->strip_tags_content( $html, '<title>', true );

		return $this;
	}

	/**
	 * @return bool
	 */
	public function can_edit() {
		return current_user_can( 'edit_pages' );
	}

	/**
	 * @return $this
	 * @throws Brizy_Editor_Exceptions_AccessDenied
	 */
	public function enable_editor() {
		if ( ! $this->can_edit() ) {
			throw new Brizy_Editor_Exceptions_AccessDenied( 'Current user cannot edit page' );
		}

		$this->storage()->set( Brizy_Editor_Constants::USES_BRIZY, 1 );

		return $this;
	}

	/**
	 * @return $this
	 * @throws Brizy_Editor_Exceptions_AccessDenied
	 */
	public function disable_editor() {
		if ( ! $this->can_edit() ) {
			throw new Brizy_Editor_Exceptions_AccessDenied( 'Current user cannot edit page' );
		}

		$this->storage()->delete( Brizy_Editor_Constants::USES_BRIZY );

		return $this;
	}

	/**
	 * @return Brizy_Editor_Storage_Post
	 */
	public function storage() {

		return Brizy_Editor_Storage_Post::instance( $this->wp_post_id );
	}


	/**
	 * @return array|null|WP_Post
	 */
	public function get_wp_post() {
		return get_post( $this->get_id() );
	}


	/**
	 * @return bool
	 */
	public function uses_editor() {

		try {
			return (bool) $this->storage()->get( Brizy_Editor_Constants::USES_BRIZY );
		} catch ( Exception $exception ) {
			return false;
		}
	}


	/**
	 * @return string
	 */
	public function edit_url() {
		return add_query_arg(
			array( Brizy_Editor_Constants::EDIT_KEY => '' ),
			get_permalink( $this->get_id() )
		);
	}

	/**
	 * @return string
	 */
	public function edit_url_iframe() {
		return add_query_arg(
			array( Brizy_Editor_Constants::EDIT_KEY_IFRAME => '' ),
			get_permalink( $this->get_id() )
		);
	}


	/**
	 * @return bool
	 * @throws Brizy_Editor_Exceptions_ServiceUnavailable
	 * @throws Exception
	 */
	public function compile_page() {

		Brizy_Logger::instance()->notice( 'Compile page', array( $this ) );

		$compiled_html = Brizy_Editor_User::get()->compile_page( Brizy_Editor_Project::get(), $this );

		$this->set_compiled_html_head( $compiled_html->get_head() );
		$this->set_compiled_html_body( $compiled_html->get_body() );

		$this->set_needs_compile( false );

		return true;
	}


	public function set_needs_compile( $v ) {
		$this->needs_compile = (bool) $v;

		return $this;
	}

	public function get_needs_compile() {
		return $this->needs_compile;
	}

	function strip_tags_content( $text, $tags = '', $invert = false ) {

		preg_match_all( '/<(.+?)[\s]*\/?[\s]*>/si', trim( $tags ), $tags );
		$tags = array_unique( $tags[1] );

		if ( is_array( $tags ) AND count( $tags ) > 0 ) {
			if ( $invert == false ) {
				return preg_replace( '@<(?!(?:' . implode( '|', $tags ) . ')\b)(\w+)\b.*?>(.*?</\1>)?@si', '', $text );
			} else {
				return preg_replace( '@<(' . implode( '|', $tags ) . ')\b.*?>(.*?</\1>)?@si', '', $text );
			}
		} elseif ( $invert == false ) {
			return preg_replace( '@<(\w+)\b.*?>.*?</\1>@si', '', $text );
		}

		return $text;
	}

	/**
	 * @return array
	 */
	public function get_templates() {
		$type = get_post_type( $this->get_id() );
		$list = array(
			array(
				'id'    => '',
				'title' => __( 'Default' )
			)
		);

		return apply_filters( "brizy:$type:templates", $list );
	}

	/**
	 * @param string $atemplate
	 *
	 * @return $this
	 */
	public function set_template( $atemplate ) {

		if ( $atemplate == '' ) {
			delete_post_meta( $this->get_id(), '_wp_page_template' );
		} else {
			update_post_meta( $this->get_id(), '_wp_page_template', $atemplate );
		}

		return $this;
	}
}
