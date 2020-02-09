<?php


require_once(BASE_PATH."/classes/Brand.php");
require_once(BASE_PATH."/classes/logger.php");
require_once(BASE_PATH."/classes/error.class.php");
require_once(BASE_PATH."/classes/request.php");
require_once(BASE_PATH."/classes/http.php");
//require_once(BASE_PATH."/classes/session.php");
require_once(BASE_PATH."/classes/db_pgsql.class.php");
//require_once(BASE_PATH."/classes/cache.class.php");
require_once(BASE_PATH."/classes/file.class.php");
require_once(BASE_PATH."/classes/template.class.php");
require_once(BASE_PATH."/classes/layout.class.php");

require_once(BASE_PATH."/classes/category.class.php");
require_once(BASE_PATH."/classes/activity.class.php");
require_once(BASE_PATH."/classes/country.class.php");
require_once(BASE_PATH."/classes/continent.class.php");
require_once(BASE_PATH."/classes/Refdata.php");
require_once(BASE_PATH."/classes/review.class.php");
require_once(BASE_PATH."/classes/website.class.php");
require_once(BASE_PATH."/classes/mapping.class.php");
require_once(BASE_PATH."/classes/validation.class.php");
require_once(BASE_PATH."/classes/name_service.class.php");
require_once(BASE_PATH."/classes/image.class.php");
require_once(BASE_PATH."/classes/enquiry.class.php");
require_once(BASE_PATH."/classes/pagerJS.class.php");

/* @depreciated - to be replaced by Profile* class topology below */
require_once(BASE_PATH."/classes/company.class.php");
require_once(BASE_PATH."/classes/placement.class.php");

/* Profile System */
require_once(BASE_PATH."/classes/ProfileType.php");
require_once(BASE_PATH."/classes/ProfileInterface.php");
require_once(BASE_PATH."/classes/ProfileAbstract.class.php");
require_once(BASE_PATH."/classes/ProfileFactory.class.php");
require_once(BASE_PATH."/classes/ProfilePlacement.class.php");
require_once(BASE_PATH."/classes/ProfileCompany.class.php");
require_once(BASE_PATH."/classes/ProfileGeneral.class.php");
require_once(BASE_PATH."/classes/ProfileTour.class.php");
require_once(BASE_PATH."/classes/ProfileJob.class.php");
require_once(BASE_PATH."/classes/ProfileSummerCamp.php");
require_once(BASE_PATH."/classes/ProfileVolunteerTravelProject.php");
require_once(BASE_PATH."/classes/ProfileSeasonalJobsEmployer.php");
require_once(BASE_PATH."/classes/ProfileTeachingProject.php");
require_once(BASE_PATH."/classes/ArchiveManager.php");

// article system
require_once(BASE_PATH."/classes/link.class.php");
require_once(BASE_PATH."/classes/article.class.php");


/* SOLR Search Engine */
require_once("./classes/SolrSearch.php");
require_once("./classes/SolrPlacementSearch.php");
require_once("./classes/SolrCompanySearch.php");
require_once("./classes/SolrCombinedProfileSearch.php");
require_once("./classes/SolrCombinedSearch.php");
require_once("./classes/SolrQuery.php");


/* Placement Profile By Company */
require_once("./classes/BalancedDistributor.php");


require_once(BASE_PATH."/classes/json.class.php");





/* set no cache headers */
header( 'Expires: Sat, 26 Jul 1997 05:00:00 GMT' );
header( 'Last-Modified: ' . gmdate( 'D, d M Y H:i:s' ) . ' GMT' );
header( 'Cache-Control: no-store, no-cache, must-revalidate' );
header( 'Cache-Control: post-check=0, pre-check=0', false );
header( 'Pragma: no-cache' );

/* enforce UTF-8 rendering */
header('Content-Type: text/html; charset=utf-8');


/* establish database connection */
$db = new db($dsn,$debug = false);

