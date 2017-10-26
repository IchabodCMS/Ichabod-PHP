<?php

require('../vendor/autoload.php');

use IchabodCms\Api\IchabodCmsApi;
use IchabodCms\Api\Exception\QueryException;


$client = new IchabodCmsApi('62645410-1398-11e6-9fd8-e53249d75c49', '34058E3C8E463C80C00C1C80C');

/**
 * Get a list of all the published posts for the list
 *
 * @param array $list
 */
try {
    $list = $client->get('/space/f37797e9-0c2a-58f4-bf25-b9623b739513/list');
} catch (QueryException $e) {
    echo '<pre>';
    print_r($e->getMessage());
    echo '</pre>';
}

echo '<pre>';
print_r($list);
echo '</pre>';

/**
 * Get the specific post for the list ID provided in method
 *
 * @param array $post
 */
try {
    $post = $client->get('/space/f37797e9-0c2a-58f4-bf25-b9623b739513/another-post-title');
} catch (QueryException $e) {
    echo '<pre>';
    print_r($e->getMessage());
    echo '</pre>';
}

echo '<pre>';
print_r($post);
echo '</pre>';
