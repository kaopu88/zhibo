<?php

namespace bxkj_common;

use OpenSearch\Client\SearchClient;
use OpenSearch\Generated\Search\SearchParams;

class OpenSearchClient extends SearchClient
{
    public function execute(SearchParams $searchParams)
    {
        $result = parent::execute($searchParams);
        return new OpenSearchResult($result);
    }
}