<?php

/**
 *
 * @package     Censor_User
 * @author      Nina Cecilie Højholdt
 * @license     @TODO [description]
 * @copyright   2014 MICO
 * @link        MICO, http://www.mico.dk
 *
 * @wordpress-plugin
 * Plugin Name:     Censor user role
 * Plugin URI:      @TODO
 * Description:     Creates a new user role, Censor, with capabilities to read pages and private pages.
 * Version:         1.0.0
 * Author:          Nina Cecilie Højholdt
 * Author URI:      http://www.mico.dk
 * Text Domain:     censor-user
 * License:         @TODO
 * GitHub URI:      @TODO
 */
 
 class Censor_User {

    protected $plugin_slug = 'censor-user-role';

    public $role_name = 'censor';
    public $role_display_name = 'Censor';


    function __construct() {
        // Add role  when new blog is added
        add_action( 'wpmu_new_blog', array( $this, 'add_role_to_blog' ) );
    }

    function activate( $network_wide ) {
        if ( $network_wide ) {
            $blogs = $this->_blogs();
            foreach ( $blogs as $blog_id ) {
                switch_to_blog( $blog_id );

                $capabilities = $this->capabilities();
                add_role( $this->role_name, $this->role_display_name, $capabilities );
                
                restore_current_blog();
            }

        } else {
            $capabilities = $this->capabilities();
            add_role( $this->role_name, $this->role_display_name, $capabilities );
        }
    }

    function deactivate( $network_wide ) {
        if ( $network_wide ) {
            $blogs = $this->_blogs();
            foreach ( $blogs as $blog_id ) {
                switch_to_blog( $blog_id );


                $user_query = get_users(array( 'role' => 'censor' ) );

                if ($user_query) :
                    foreach ( $user_query as $user ) :
                        $u = new WP_User($user->ID);

                        $u->remove_role('censor');

                        $u->add_role('subscriber');
                    endforeach;
                else :
                endif;

                remove_role( $this->role_name );

                restore_current_blog();
            }

        } else {
            
            $user_query = get_users(array( 'role' => 'censor' ) );

            if ($user_query) :
                foreach ( $user_query as $user ) :
                    $u = new WP_User($user->ID);

                    $u->remove_role('censor');

                    $u->add_role('subscriber');
                endforeach;
            else :
            endif;

            remove_role( $this->role_name );
        }
    }


    // Manage capabilities for the new user
    function capabilities() {

        // Cencors can only read
        $capabilities = array('read' => true, 'read_private_pages' => true);

        $capabilities = apply_filters( 'censor_capabilities', $capabilities );

        return $capabilities;
    }
}


if(!isset($censor_user)) :
    $censor_user = new Censor_User(); 
endif;

register_activation_hook( __FILE__, array( $censor_user, 'activate' ) );
register_deactivation_hook( __FILE__, array( $censor_user, 'deactivate' ) );


?>