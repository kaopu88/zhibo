<?php

namespace bxkj_common;

require_once(ROOT_PATH . "extend/OpenSearch/Autoloader/Autoloader.php");

use OpenSearch\Client\OpenSearchClient;
use OpenSearch\Client\SearchClient;
use OpenSearch\Util\SearchParamsBuilder;

class OpenSearch
{
    protected static $openSearchClient;

    public static function createOpenSearchClient()
    {
        if (isset(self::$openSearchClient)) {
            return self::$openSearchClient;
        }
        $accessKeyId = config('app.open_search.access_key');
        $secret = config('app.open_search.secret');
        $endPoint = config('app.open_search.host');
        $options = array('debug' => config('app.open_search.debug'));
        self::$openSearchClient = new OpenSearchClient($accessKeyId, $secret, $endPoint, $options);
        return self::$openSearchClient;
    }

    public static function createSearchClient()
    {
        $searchClient = new \bxkj_common\OpenSearchClient(self::createOpenSearchClient());
        return $searchClient;
    }

    public static function createSearchParamsBuilder($appName)
    {
        $openSearchApps = config('app.open_search_app');
        $appName2 = $openSearchApps[$appName];
        $params = new SearchParamsBuilder();
        $params->setAppName($appName2);
        $params->setFormat("fulljson");
        return $params;
    }


}