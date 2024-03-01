<?

require_once("./conf/config.php");
require_once("./init.php");
require_once("/www/vhosts/365admin.org/htdocs/conf/brand_config.php");
require_once("/www/vhosts/365admin.org/htdocs/classes/authenticate.class.php");
require_once("/www/vhosts/365admin.org/htdocs/classes/user.class.php");
require_once("/www/vhosts/oneworld365.org/htdocs/classes/RequestRouter.class.php");


try {

    // Process URI / Request ------------------------------------------
    $request_array = Request::GetUri("ARRAY");

    Logger::DB(2,"API request: ".$_SERVER['REQUEST_URI']);

    if (is_array($request_array)) {
    	foreach($request_array as $uri_segment) {
    		if ($uri_segment == "") continue;
    		if (!NameService::validUriNamespaceIdentifier($uri_segment)) {
    		    header('HTTP/1.1 404 Not Found');
    			throw new Exception("Invalid uri segment: ".$uri_segment);
    			die();
    		}
    	}
    }

    $permitted_hosts = array("oneworld365.org");
    $hostname = Request::GetHostName();

    $_CONFIG['url'] = $aBrandConfig[$hostname]['website_url'];
    $_CONFIG['company_home'] = $aBrandConfig[$hostname]['company_base_url'];
    $oBrand = new Brand($aBrandConfig[$hostname]);

    $permitted_methods = array("search");

    if (!in_array(strtolower($request_array[1]), $permitted_methods)) {
        header('HTTP/1.1 404 Not Found');
        throw new Exception("Invalid API method: ".implode("_",$request_array).$hostname);
    	die();
    }


    // Build Search Query Parameters ---------------------------------

    $oSolrQuery = new SolrQuery;
    $oSolrQuery->parseFilterQueryFromRequest();
    $oSolrQuery->setDefaultProfileType();

    // refine search panel UI visibility
    $rf = (isset($_REQUEST['rf']) && $_REQUEST['rf'] == 1) ? 1 : 0;

    // return data type (default to JSON)
    $returnDataType = (isset($_REQUEST['rt']) && $_REQUEST['rt'] == "HTML") ? $_REQUEST['rt'] : "JSON";

    // query origin 0 = website, 1 = admin system
    $query_origin = (isset($_REQUEST['o']) && in_array($_REQUEST['o'],array(0,1))) ? $_REQUEST['o'] : 0;

    // search type: 0 = facet only, 1 return profile results
    $search_type = (isset($_REQUEST['st']) && $_REQUEST['st'] == 0) ? 0 : 1;
    
    $iRows = (is_numeric($_REQUEST['rows']) && $_REQUEST['rows'] < 1000) ? $_REQUEST['rows'] : 50;
    
    // sort company results by prod_id (disabled for admin system requests)
    if ($oSolrQuery->getFilterQueryByName('profile_type') == "0") {
    	$oSolrQuery->setSort('prod_type','desc');
    }

    /**
     * Setup SOLR Query & facets from URI request array
     *
     */

    if ($search_type == 0) // facet only query, default query unless keywords supplied
    {
        $query = (strlen($request_array[2]) > 1) ? $request_array[2] : "*:*";
        $oSolrQuery->setQuery($query);
    } else {
        $oSolrQuery->setupFilterQuery($request_array);
    }

    $oSolrQuery->setupFacetFieldset();
    
    /**
     * Select result processing class based on requested content type(s)
     */
    switch($oSolrQuery->getFilterQueryByName('profile_type')) {
        case "0" : // company
            $class = "SolrCompanySearch";
            break;
        case "1" : // placement
            $class = "SolrPlacementSearch";
            break;
        case "(1 OR 0)" : // company and placement
            $class = "SolrCombinedProfileSearch";
            break;
        case "2" : // articles
            $class = "SolrCompanySearch";
            break;
    }


    // Placement search - filter inactive profiles
    if ($oSolrQuery->getFilterQueryByName('profile_type') == 1)
        $oSolrQuery->setFilterQueryByName("active","1");


    // duration / price facets
    $aFacetQuery = array();
    $aFacetQuery[] = array('duration_0_1' => '(duration_from:[* TO 6])');
    $aFacetQuery[] = array('duration_1_2' => '(duration_from:[7 TO 13])');
    $aFacetQuery[] = array('duration_2_4' => '(duration_from:[14 TO 27])');
    $aFacetQuery[] = array('duration_4_8' => '(duration_from:[28 TO 55])');
    $aFacetQuery[] = array('duration_8_24' => '(duration_from:[60 TO 167])');
    $aFacetQuery[] = array('duration_24_*' => '(duration_from:[182 TO *])');
    $aFacetQuery[] = array('duration_all' => '(*:*)');

    $oSolrQuery->setFacetQuery("duration",$aFacetQuery);


    $aFacetQuery = array();
    $aFacetQuery[] = array('price_0_250' => '(price_from:[* TO 249])');
    $aFacetQuery[] = array('price_250' => '(price_from:[250 TO 499])');
    $aFacetQuery[] = array('price_500' => '(price_from:[500 TO 749])');
    $aFacetQuery[] = array('price_750' => '(price_from:[750 TO 999])');
    $aFacetQuery[] = array('price_1000' => '(price_from:[1000 TO 1999])');
    $aFacetQuery[] = array('price_2000' => '(price_from:[2000 TO *])');
    $aFacetQuery[] = array('price_all' => '(*:*)');

    $oSolrQuery->setFacetQuery("price",$aFacetQuery);

    Logger::DB(2,"API query: ".$oSolrQuery->getQuery());
    Logger::DB(2,"API fq: ".json_encode($oSolrQuery->getFilterQuery()));


    // RUN SEARCH ------------------------------------------------------

    // pagesize / start index
    $iStart = (is_numeric($_REQUEST['start']) && $_REQUEST['start'] != 0)  ? (($_REQUEST['start'] -1) * $iRows) : 0;
    $iPageNum = (is_numeric($_REQUEST['start']) && $_REQUEST['start'] != 0) ? $_REQUEST['start'] : 0;

    Logger::DB(2,"API start: ".$iStart." , rows: ".$iRows);
    Logger::DB(2,"ProfileType: ".$oSolrQuery->getFilterQueryByName('profile_type'));
    

    $oSolrSearch = new $class($solr_config);
    $oSolrSearch->setRows($iRows);
    $oSolrSearch->setStart($iStart);

    if ($oSolrQuery->getFilterQueryByName('profile_type') == "(1 OR 0)")
    {
	   $oSolrSearch->setBoostQuery('prod_type:5^25, prod_type:3^5, prod_type:2^3, prod_type:1^2, prod_type:0');
    }

    // add facetField
    if (count($oSolrQuery->getFacetField()) >= 1) {
    	foreach($oSolrQuery->getFacetField() as $facet) {
    		$oSolrSearch->addFacetField($facet);
    	}
    }

    // add facetQuery
    if (count($oSolrQuery->getFacetQuery()) >= 1) {
    	foreach($oSolrQuery->getFacetQuery() as $key => $facetQuerySet) {
    		$oSolrSearch->addFacetQuery($key,$facetQuerySet);
    	}
    }

    $oSolrSearch->setFacetFieldFilterQueryExclude($oSolrQuery->getFacetFieldFilterQueryExclude());
    $oSolrSearch->setSiteId($oBrand->GetSiteId());


    /*
    print_r("<pre>");
    print_r($oSolrQuery);
    print_r($oSolrSearch);
    print_r("</pre>");
    die();
    */

    
    // run the search
    $oSolrSearch->search($oSolrQuery->getQuery(),$oSolrQuery->getFilterQuery(),$oSolrQuery->getSort());
    $oSolrSearch->processResult();

    // fetch returned profile id's, instantiate collection of profile objects
    $aProfileId = $oSolrSearch->getId();
    $aProfile = array();
    $bSort = true;
    
    if (is_array($aProfileId) && count($aProfileId) >= 1) {
        if ($oSolrQuery->getFilterQueryByName('profile_type') == "1")  { // PLACEMENTS
    		
            $aProfileUnsorted = PlacementProfile::Get("ID_LIST_SEARCH_RESULT",$aProfileId);

    		foreach($aProfile as $oProfile) {
    			$doc = $oSolrSearch->getResultByProfileId($oProfile->GetId());
    			$oProfile->SetDurationFrom($doc->duration_from);
    			$oProfile->SetDurationTo($doc->duration_to);
    		}
        } elseif ($oSolrQuery->getFilterQueryByName('profile_type') == "0") { // COMPANY RESULTS
    		//Logger::DB(2,"COMP ID: ".implode(",",$aProfileId));
    		$aProfileUnsorted = CompanyProfile::Get("ID",$aProfileId);
    	} elseif ($oSolrQuery->getFilterQueryByName('profile_type') == "2") { // articles

    		foreach($aProfileId as $id) {

    			$oArticle = new Article;
    			$oArticle->SetFetchMode(FETCHMODE__SUMMARY);
    			$oArticle->GetById($id);
    			if (!is_numeric($oArticle->GetId())) continue;
    			$aProfileUnsorted[$id] = $oArticle;

    		}

    	} elseif($oSolrQuery->getFilterQueryByName('profile_type') == "(1 OR 0)") { // company profiles & placements
    	    
            $aProfile = $oSolrSearch->getProfile();
            $bSort = false;
            
    	}

        /*
    	print_r("<pre>");
    	print_r("SOLR Count: ".$oSolrSearch->getNumFound()."<br />");
    	print_r("ProfileId: ".count($aProfileId)."<br />");
    	print_r("NumberProfile: ".count($aProfile)."<br />");
    	print_r($aProfile);
    	print_r("</pre>");
    	die();
    	*/

    	
    	if ($bSort)
    	{
        	$aProfile = array();
        	foreach ($aProfileId as $id) {
        		if (isset($aProfileUnsorted[$id])) {
        			$aProfile[$id] = $aProfileUnsorted[$id];
        		}
        	}
    	}
    }

    if (!is_array($aProfile)) {
    	throw new Exception("API 0 Profile objects returned from Profile::Get()");
    }

    /**
     * SOLR Results found
     * @var int $iNumFound
     */
    $iNumFound = $oSolrSearch->getNumFound();

    // JSON encode Response data + Profiles --------------------------------------------

    $aResponse = array();
    $aResponse['status'] = 1;
    $aResponse['profileType'] = $oSolrQuery->getFilterQueryByName('profile_type');
    $aResponse['data'] = array();
    $aResponse['total_results'] = $oSolrSearch->getNumFound();
    $aResponse['total_profile'] = count($aProfile);
    $aResponse['total_profile_id'] = count($aProfileId);
    $aResponse['start'] = $iStart;
    $aResponse['rows'] = $iRows;
    $aResponse['hasPager'] = false;
    $aResponse['rf'] = $rf;


    Logger::DB(2,"API Total Result: ".$oSolrSearch->getNumFound().", Found profile id: ".count($aProfileId).", profiles objects returned: ".count($aProfile));


    if ($returnDataType == "HTML") // pre-rendered server side HTML template
    {

        $sProfileHTML = "";

        for($i=0; $i<8; $i++)
        {
            $oProfile = array_shift($aProfile);
            if (!is_object($oProfile)) continue;        
            $oTemplate = new Template();
            $oTemplate->SetTemplatePath("/www/vhosts/365admin.org/htdocs/templates/");
            $oTemplate->Set("oProfile", $oProfile);
            $oTemplate->LoadTemplate("profile_summary.php");
            $sProfileHTML .= $oTemplate->Render();
        }

        $aResponse['data']['profile']['b1'] = $sProfileHTML;

        $sProfileHTML = "";

        for($i=0; $i<count($aProfile); $i++)
        {
            $oProfile = array_shift($aProfile);
            if (!is_object($oProfile)) continue;
            $oTemplate = new Template();
            $oTemplate->SetTemplatePath("/www/vhosts/365admin.org/htdocs/templates/");
            $oTemplate->Set("oProfile", $oProfile);
            $oTemplate->LoadTemplate("profile_summary.php");
            $sProfileHTML .= $oTemplate->Render();
        }
        
        $aResponse['data']['profile']['b2'] = $sProfileHTML;

    } else { // return structured JSON format data
        foreach($aProfile as $oProfile) {
            if (!is_object($oProfile)) continue;
            $aResponse['data']['profile'][] = $oProfile->toJSON();
        }        
    }



    // add any facetField results
    if (count($oSolrQuery->getFacetField()) >= 1) {
    	foreach($oSolrQuery->getFacetField() as $facet) {
    		foreach($facet as $key => $value) {
    			$aResponse['data']['facet'][$key]['name'] = $key;
    			$aResponse['data']['facet'][$key]['data'] = $oSolrSearch->getFacetFieldResult($key);
    		}
    	}
    }


    // add any facetQuery results
    if (count($oSolrQuery->getFacetQuery()) >= 1) {
    	foreach($oSolrQuery->getFacetQuery() as $key => $facetQuerySet) {
    		$aResponse['data']['facet'][$key]['name'] = $key;
    		$aResponse['data']['facet'][$key]['data'] = $oSolrSearch->getFacetQueryResult($key);
    	}
    }


    // build pager
    if ($iRows != 0 && ($iNumFound > $iRows)) 
    {

    	$oPager = new PagedResultSet();
    	$oPager->SetResultsPerPage($iRows);
    	$oPager->setPageNum($iPageNum);
    	$oPager->GetByCount($iNumFound,"P1");

    	$aResponse['data']['hasPager'] = true;
    	$aResponse['data']['pagerHtml'] = $oPager->RenderJSPaginator();
    	$aResponse['pageNum'] = ($iPageNum == 0) ? 1 : $iPageNum;
    	$aResponse['totalPages'] = (is_object($oPager)) ? $oPager->GetNumPages() : 1;

    } else {
    	$aResponse['data']['hasPager'] = false;
    }


    header('Content-type: application/x-json');
    echo $_GET['callback'] . '('.json_encode($aResponse).')';
    die();


} catch (Exception $e) {
    throw $e;
}

/* Global Exception Handler */
function exception_handler($e) {
    
    error_log($e);

    $aResponse = array();
    $aResponse['status'] = 1;
    $aResponse['error'] = "An error occured.";
    
    header('Content-type: application/x-json');
    echo $_GET['callback'] . '('.json_encode($aResponse).')';
    die();
    
}

set_exception_handler('exception_handler');


?>
