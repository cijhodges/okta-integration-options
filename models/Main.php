<?php
namespace OktaIntegrationOptions;

class Main
{
    public $domains = [];
    public $force_auth = false;
    public $pages = [];
    public $saml_login = null;
    public $table_name;

    public function __construct()
    {
        global $wpdb;
        $this->table_name = $wpdb->prefix . 'okta_integration_options';

        if ( !$wpdb->query( "SELECT 1 FROM `{$this->table_name}`" ) ) {
            if ( !$this->create_table() ) return false;
        }

        if ( is_admin() ) {
            $this->admin_functions();
            return false;
        }

        add_action( 'wp', array( $this, 'redirect' ) );
    }

    public function add_domain( $domain = null )
    {
        if ( !$domain || in_array( $domain, $this->domains ) ) {
            echo 'not added';
            return false;
        }

        $this->domains[] = $domain;
    }

    public function add_page( $page = null )
    {
        if ( !$page || in_array( $page, $this->pages ) ) return false;

        $this->pages[] = $page;
    }
    
    public function admin_functions()
    {
        global $pagenow;
        global $wpdb;

        require_once 'Updater.php';

        $updater = new \OktaIntegrationOptionsUpdater( __FILE__ );
        $updater->set_username( 'cijhodges' ); // set username
        $updater->set_repository( 'okta-integration-options' ); // set repo
        $updater->initialize();

        switch ( $_SERVER['REQUEST_METHOD'] ) {
            case 'GET':
                add_action( 'admin_menu', function() {
                    add_management_page( 'Okta Integration Options', 'Okta Integration', 'install_plugins', 'okta-integration', function() { include OKTA_INTEGRATION_PATH . '/views/admin.php'; } );
                });
                break;

            default:
                break;
        }

        switch ( $pagenow ) {
            case 'tools.php':
                if ( $_GET['page'] === 'okta-integration' ) {
                    switch ( $_SERVER['REQUEST_METHOD'] ) {
                        case 'GET':
                            $this->read();
                            wp_enqueue_style( '/wp-admin/load-styles.php?c=1&dir=ltr&load%5Bchunk_0%5D=dashicons,admin-bar,common,forms,admin-menu,dashboard,list-tables,edit,revisions,media,themes,about,nav-menus,wp-pointer,widgets&load%5Bchunk_1%5D=,site-icon,l10n,buttons,wp-auth-check&ver=5.6' );
                            wp_enqueue_script( 'okta-integration-admin-page', OKTA_INTEGRATION_URL . 'controllers/admin.js' );
                            break;

                        case 'POST':
                            header( 'Content-type: application/json' );

                            if ( !isset( $_POST['submit'] ) ) {
                                print json_encode( array(
                                    'error' => array(
                                        'code' => 400,
                                        'message' => 'not suibmitted'
                                    )
                                ) );
                                die();
                            }

                            $this->read();
                            if ( isset( $_POST['force_auth'] ) ) $this->set( array( 'force_auth' => $_POST['force_auth'] ) );
                            if ( isset( $_POST['saml_login'] ) ) $this->set( array( 'saml_login' => $_POST['saml_login'] ) );
                            if ( isset( $_POST['domains'] ) ) $this->set( array( 'domains' => $_POST['domains'] ) );
                            if ( isset( $_POST['pages'] ) ) $this->set( array( 'pages' => $_POST['pages'] ) );
                            $this->update();
                            die();
                            break;

                        default:
                            break;
                    }
                }
                break;

            case 'post.php':
                $this->read();
                add_action( 'add_meta_boxes', array( $this, 'custom_box' ) );
                add_action( 'save_post', array( $this, 'save_postdata' ) );
                break;

            default:
                break;
        }
    }

    function box_html() {
        global $post;
        $checked = in_array( $post->ID, $this->pages ) ? true : false;
        ?>
        <div style="margin: 0 0 2rem;">
            <input type="checkbox" name="okta_integration_force_page" id="okta_integration_force_page" value="1"<?php if ( $checked ) { ?> checked<?php } ?>>
            <label for="okta_integration_force_page">Force Okta Authentication</label>
        </div>
        <?php
    }

    public function create_table()
    {
        global $wpdb;

        if ( !$wpdb->query( "CREATE TABLE `{$this->table_name}` (
            `id` INT NOT NULL AUTO_INCREMENT,
            `key` VARCHAR(45) NOT NULL,
            `value` VARCHAR(512) NOT NULL,
            PRIMARY KEY ( `id` )
        )" ) ) {
            return false;
        }

        if ( !$wpdb->query( "INSERT INTO `{$this->table_name}` (
                `key`, `value`
            ) VALUES(
                'force_auth', 'false'
            ), (
                'saml_login', ''
            )"
        ) ) {
            return false;
        }

        return true;
    }

    function custom_box() {
        $screens = [ 'post', 'page' ];
        foreach ( $screens as $screen ) {
            add_meta_box(
                'okta_integration_box',
                'Okta Integration Options',
                array( $this, 'box_html' ),
                $screen
            );
        }
    }

    public function read()
    {
        global $wpdb;
        $results = $wpdb->get_results( "SELECT * FROM `{$this->table_name}`" );

        if ( !$results ) return false;

        $this->domains = [];
        $this->pages = [];

        foreach ( $results as $result ) {
            $key = $result->key;
            $value = $result->value;

            switch ( $key ) {
                case 'force_auth':
                    $this->set( array( 'force_auth' => boolval( $value ) ) );
                    break;

                case 'saml_login':
                    $this->set( array( 'saml_login' => $value ) );
                    break;

                case 'domain':
                    if ( is_string( $value ) ) $this->add_domain( $value );
                    break;

                case 'page':
                    $value = intval( $value );
                    if ( $value && is_numeric( $value ) ) $this->add_page( $value );
                    break;
            }
        }
    }

    function redirect()
    {
        global $wp;
        global $wp_session;

        $this->read();

        $id = get_the_ID();
        $page = home_url( $wp->request );

        if ( !is_user_logged_in() ) {
            if ( 
                $this->force_auth 
                || (
                    $id && in_array( $id, $this->pages )
                )
            ) {
                $login_page = '/wp-login.php';
                if ( $this->saml_login ) $login_page = $this->saml_login;
                include_once OKTA_INTEGRATION_PATH . '/views/redirect-login.php';
                die();
            }

            return false;
        }        

        if ( 
            isset( $_SERVER['HTTP_REFERER'] )
            && in_array( $_SERVER['HTTP_REFERER'], $this->domains )
            && isset( $_COOKIE['oktaIntegrationLastPage'] ) 
            && $page !== $_COOKIE['oktaIntegrationLastPage']
        ) {
            include_once OKTA_INTEGRATION_PATH . '/views/redirect-page.php';
            die();
        }
	}

    public function remove_domain( $domain = null )
    {
        if ( !$domain || !in_array( $domain, $this->domains ) ) return false;

        $i = array_search( $domain, $this->domains );
        unset( $this->domains[$i] );
    }

    public function remove_page( $page = null )
    {
        if ( !$page || !in_array( $page, $this->pages ) ) return false;

        $i = array_search( $page, $this->pages );
        unset( $this->pages[$i] );
    }

    function save_postdata() {
		global $post;
	
		$force_page = 0;
	
		if ( isset( $_POST['okta_integration_force_page'] ) ) {
			$force_page = intval( $_POST['okta_integration_force_page'] );
		}
		
		$pageExists = in_array( $post->ID, $this->pages );
	
		if ( $force_page ) {
			if ( !$pageExists ) {
				$this->add_page( $post->ID );
				$this->update();
			}
		} elseif ( $pageExists ) {
			$this->remove_page( $post->ID );
			$this->update();
		}
	}

    public function set( $props = array() )
    {
        foreach ( $props as $key => $value ) {
            if ( property_exists( $this, $key ) ) {
                switch ( $key ) {
                    case 'force_auth':
                        $this->force_auth = boolval( $value );
                        break;

                    case 'saml_login':
                        $this->saml_login = filter_var( $value, FILTER_SANITIZE_STRING );
                        break;

                    case 'domains':
                    case 'pages':
                        if ( is_array( $value ) ) $this->$key = $value;
                        break;
                    
                    default:
                        break;
                }
            }
        }
    }

    public function update()
    {
        global $wpdb;
        $queries = array();

        $quieries[] = array(
            'stmt' => "UPDATE `{$this->table_name}` SET `key` = 'force_auth', `value` = %s WHERE `key` = 'force_auth';",
            'value' => $this->force_auth ? "1" : "0"
        );

        $quieries[] = array(
            'stmt' => "UPDATE `{$this->table_name}` SET `key` = 'saml_login', `value` = %s WHERE `key` = 'saml_login';",
            'value' => $this->saml_login
        );

        $domain_results = $wpdb->get_results( "SELECT `value` FROM `{$this->table_name}` WHERE `key` = 'domain'" );
        $pages_results = $wpdb->get_results( "SELECT `value` FROM `{$this->table_name}` WHERE `key` = 'page'" );
        $domains_to_delete = [];
        $domains_to_add = [];
        $pages_to_delete = [];
        $pages_to_add = [];

        foreach ( $domain_results as $result ) {
            if ( !in_array( $result->value, $this->domains ) ) {
                $domains_to_delete[] = $result->value;
            }
        }

        foreach ( $this->domains as $domain ) {
            $inDomains = false;

            foreach ( $domain_results as $result ) {
                if ( $domain === $result->value ) {
                    $inDomains = true;
                }
            }

            if ( !$inDomains ) $domains_to_add[] = $domain;
        }

        foreach ( $pages_results as $result ) {
            if ( !in_array( $result->value, $this->pages ) ) {
                $pages_to_delete[] = $result->value;
            }
        }

        foreach ( $this->pages as $page ) {
            $inPages = false;

            foreach ( $pages_results as $result ) {
                if ( $page === intval( $result->value ) ) {
                    $inPages = true;
                }
            }

            if ( !$inPages ) $pages_to_add[] = $page;
        }

        for ( $i = 0; $i < count( $domains_to_add ); $i++ ) {
            $quieries[] = array(
                'stmt' => "INSERT INTO `{$this->table_name}` SET `key` = 'domain', `value` = %s",
                'value' => $domains_to_add[$i]
            );
        }

        for ( $i = 0; $i < count( $domains_to_delete ); $i++ ) {
            $quieries[] = array(
                'stmt' => "DELETE FROM `{$this->table_name}` WHERE `key` = 'domain' && `value` = %s",
                'value' => $domains_to_delete[$i]
            );
        }

        for ( $i = 0; $i < count( $pages_to_add ); $i++ ) {
            $quieries[] = array(
                'stmt' => "INSERT INTO `{$this->table_name}` SET `key` = 'page', `value` = %s;",
                'value' =>$pages_to_add[$i]
            );
        }

        for ( $i = 0; $i < count( $pages_to_delete ); $i++ ) {
            $quieries[] = array(
                'stmt' => "DELETE FROM `{$this->table_name}` WHERE `key` = 'page' && `value` = %s;",
                'value' => $pages_to_delete[$i]
            );
        }

        foreach ( $quieries as $query ) {
            $wpdb->query( $wpdb->prepare( $query['stmt'], $query['value'] ) );
        }

        print json_encode( array(
            'success' => array(
                'code' => 200,
                'message' => 'updated'
            )
        ) );
        die();
    }
}
