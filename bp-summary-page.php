<?php
/**
 * Plugin Name: BP Summary Page
 * Plugin URI:  http://wordpress.com
 * Description: Adds a Summary page to Buddypress profile and Nav menu. 
 * Author:      Kyle Traff
 * Version:     1.0
 * Author URI:  http://wordpress.com
 */

define( BPSP_ROOT_PATH, plugin_dir_path(__FILE__));

class BP_Summary_Page {
	
	var $component_name = 'Summary';
	var $component_slug = 'summary';
	var $user_id = 0;
	var $messages = array();
	
	public function __construct() {
		add_action('bp_init', array($this,'init'));
	}
	
	function init() {
		global $bp,$current_user;
		$this->user_id = $bp->displayed_user->id;
		
		if( $bp->current_component == $this->component_slug && !empty($_REQUEST) ) {
			$this->process_request();
		}
		
		//Add Top level menu in user profile
		bp_core_new_nav_item(
			array(
				'name' => __( $this->component_name , 'buddypress'),
				'slug' => $this->component_slug,
				'position' => 10,
				'show_for_displayed_user' => true,
				'screen_function' => array($this, 'summary_page'),
				'default_subnav_slug' => 'bio'
		));
		
		
		$parent_url = trailingslashit( $bp->displayed_user->domain . $this->component_slug );
		
		$main_page = array(
			'name'            => 'Biography',
			'slug'            => 'bio',
			'parent_url'      => $parent_url,
			'parent_slug'     => $this->component_slug,
			'screen_function' => array($this, 'summary_page'),
			'position'        => 10,
			'user_has_access' => 'all'
		);
		bp_core_new_subnav_item($main_page);
		
		add_action('bp_template_content', array($this, 'template_content'));
	}
	
	function summary_page() {
		bp_core_load_template( 'members/single/plugins' );
	}
	
	function template_content() {
		global $bp;
		
		include( BPSP_ROOT_PATH .'pages/style.php');
		
		if( !empty($this->messages)) {
			foreach($this->messages as $m){
				echo '<div class="bprp-updated"><p>'.$m.'</p></div>';
			}
		}
		
		if( $bp->current_component == $this->component_slug ) {
			$this->view_page();
		}
	}

	function view_page(){
		include( BPSP_ROOT_PATH .'pages/view.php');
	}
	
	function process_request() {
		
		if( ! (bp_is_my_profile() || current_user_can('administrator') ) ) {
			return;
		}
		
		// administrative functions go here
	}
}

function bp_summary_biography($user_id) {
	$biography = bp_summary_get_biography($user_id);
	$bio_lines = preg_split("/[\r\n]+/", $biography, -1, PREG_SPLIT_NO_EMPTY);
	foreach ($bio_lines as $key => $value) {
		?>
		<p class="paragraph"><?php echo $value; ?></p>
		<?php
	}
}
	function bp_summary_get_biography($user_id) {
		return get_user_meta($user_id, 'description', true);
	}

$BP_Summary_Page = new BP_Summary_Page();