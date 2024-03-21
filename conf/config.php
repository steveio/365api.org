<?php 

ini_set('display_errors',0);
ini_set('log_errors', 1);
ini_set('error_log', '/www/vhosts/365api.org/logs/365api_error.log');
error_reporting(E_ALL & ~E_NOTICE & ~ E_STRICT);

date_default_timezone_set('Europe/London');

// db connection... (@todo - move into $_CONFIG for consistancy)
$dsn = array("dbhost" => "localhost","dbuser" => "", "dbpass" => "","dbname" => "","dbport" => "5432");


// Solarium
$solr_config = array(
		'adapteroptions' => array(
				'host' => '127.0.0.1',
				'port' => 8983,
				'path' => '/solr/collection1/',
		)
);



define('DEBUG',FALSE);
define('DEV',FALSE);

define('TEST_EMAIL','steveedwards01@yahoo.co.uk');

/* 0 = none, 1 = error, 2 = debug, 3 = verbose debug */
define('LOG_PATH',"/www/vhosts/365api.org/logs/365api_application.log");
define('LOG_LEVEL',3);

define('BASE_PATH','/www/vhosts/365api.org/htdocs');
define('ROOT_PATH',BASE_PATH); // required for some classes
define('ROOT_PATH_IMAGE_UPLOAD','/www/vhosts/oneworld365.org/htdocs'); // required for some classes


define('PATH_2_DATA_DIR',BASE_PATH. '/data/');

define('SITE_TITLE','365 API');

define("FETCHMODE__FULL",0);
define("FETCHMODE__SUMMARY",1);


/* profile types - from db table profile_types */
define("PROFILE_COMPANY",0);
define("PROFILE_PLACEMENT",1);
define("PROFILE_VOLUNTEER",2);   // placement
define("PROFILE_TOUR",3); // placement
define("PROFILE_JOB",4); // placement
define("PROFILE_SUMMERCAMP",5); // company profile
define("PROFILE_VOLUNTEER_PROJECT",6); // company profile
define("PROFILE_SEASONALJOBS",7); // company profile
define("PROFILE_TEACHING",8); // company profile
define("PROFILE_COURSES",9); 


/* listing types */
define("NEW_LISTING",-1);
define("FREE_LISTING",0);
define("BASIC_LISTING",1);
define("ENHANCED_LISTING",2);
define("SPONSORED_LISTING",3);

/* default placement quotas */
define("FREE_PQUOTA",0);
define("BASIC_PQUOTA",1);
define("ENHANCED_PQUOTA",10);
define("SPONSORED_PQUOTA",25);


/* default 8bit profile / enquiry option bitmaps for all new company listing requests */
define("DEFAULT_PROFILE_OPT",'11100000');
define("DEFAULT_ENQUIRY_OPT",'10000000');


/* refdata type mappings 
 * @todo - add a dynamic loader to read these from DB 
 */
define('REFDATA_US_STATE',0);
define('REFDATA_CAMP_TYPE',1);
define('REFDATA_CAMP_JOB_TYPE',2);
define('REFDATA_ACTIVITY',3);
define('REFDATA_INT_RANGE',4);
define('REFDATA_DURATION',5);
define('REFDATA_ORG_SUBTYPE',6);
define('REFDATA_BONDING',7);
define('REFDATA_STAFF_ORIGIN',8);
define('REFDATA_GENDER',9);
define('REFDATA_APPROX_COST',10);
define('REFDATA_HABITATS',11);
define('REFDATA_SPECIES',12);
define('REFDATA_ACCOMODATION',13);
define('REFDATA_MEALS',14);
define('REFDATA_TRAVEL_TRANSPORT',15);
define('REFDATA_ADVENTURE_SPORTS',16);
define('REFDATA_ORG_PROJECT_TYPE',17);
define('REFDATA_CURRENCY',18);
define('REFDATA_JOB_OPTIONS',19);
define('REFDATA_INT_SMALL_RANGE',20);
define('REFDATA_JOB_CONTRACT_TYPE',21);
define('REFDATA_US_REGION',22);
define('REFDATA_AGE_RANGE',23);
define('REFDATA_RELIGION',24);
define('REFDATA_CAMP_GENDER',25);

/* multiple choice refdata form element prefixes */
define('REFDATA_ACTIVITY_PREFIX','CA_');
define('REFDATA_CAMP_TYPE_PREFIX','CT_');
define('REFDATA_CAMP_JOB_TYPE_PREFIX','JT_');
define('REFDATA_SPECIES_PREFIX','SP_');
define('REFDATA_HABITATS_PREFIX','HA_');
define('REFDATA_TRAVEL_TRANSPORT_PREFIX','TT_');
define('REFDATA_ACCOMODATION_PREFIX','AC_');
define('REFDATA_MEALS_PREFIX','ML_');
define('REFDATA_JOB_OPTIONS_PREFIX','JO_');



/* config params required to make classes work */
$_CONFIG = array(
        'url' => 'https://www.oneworld365.org',
        'company_table' => 'company',
        'placement_table' => 'profile_hdr',
        'profile_hdr_table' => 'profile_hdr', /* placement table is a view in some sites, these must use profile_hdr for add/update */
        'index_table' => 'keyword_idx_2',
        'tagcloud_table' => 'keyword_idx_1',
        'comp_country_map' => 'comp_country_map',
        'image_map' => 'image_map',
        'image' => 'image',
        'template_home' => '/www/vhosts/365admin.org/htdocs/templates/',
        
        'company_home' => 'company',

        'aProfileVersion' => array(     0 => "oneworld365.org",
                                                                        1 => "gapyear365.com",
                                                                        2 => "seasonaljobs365.com",
                                                                        3 => "summercampjobs365.com",
                                                                        4 => "tefl365.com"
                                                                ),

        'profile_category_defaults' => array(
							PROFILE_SUMMERCAMP => array(3),
							PROFILE_VOLUNTEER_PROJECT => array(2)
									),

        'profile_activity_defaults' => array(
							PROFILE_SUMMERCAMP => array(27,21)	
									),

        'profile_country_defaults' => array(
							PROFILE_SUMMERCAMP => array(71)	
									)
									
);


/* image/ file upload params */
define("IMAGE_MAX_UPLOAD_SIZE",6291456);

define("IMG_PATH_IDENTIFY","/usr/bin/identify");
define("IMG_PATH_CONVERT","/usr/bin/convert");

define("LANDSCAPE","L");
define("PORTRAIT","P");
define("SQUARE","S");

define("PROFILE_IMAGE",0);
define("LOGO_IMAGE",1);

define("LOGO__DIMENSIONS_MAXWIDTH", 500);
define("LOGO__DIMENSIONS_MINWIDTH", 100);
define("LOGO__DIMENSIONS_MAXHEIGHT", 300);
define("LOGO__DIMENSIONS_MINHEIGHT", 40);


define("LOGO__DIMENSIONS_SMALL_WIDTH", 120); /* width of auto generated logo small version */
define("LOGO__DIMENSIONS_SMALL_HEIGHT", 60); /* width of auto generated logo small version */

define("IMG_HOST","http://www.oneworld365.org/");
define("IMG_BASE_URL",IMG_HOST ."img/");
define("IMG_BASE_PATH","/www/vhosts/oneworld365.org/htdocs/img/");
define("IMG_SEQ","image_seq");

/* cookie params */
define("COOKIE_NAME", "oneworld365");
define("COOKIE_TAB_NAME", "365admin_tab");
define("COOKIE_PATH", "/");
define("COOKIE_EXPIRES", 1056000);

/* no of permitted login attempts before account is locked */ 
define("MAX_LOGIN_ATTEMPTS",20);

/* Password Encryption md5 Hash Salt Length - do not change */
define('SALT_LENGTH', 9);

?>
