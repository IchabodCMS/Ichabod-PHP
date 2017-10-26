<?php

require('../vendor/autoload.php');

use IchabodCms\Api\IchabodCmsApi;
use IchabodCms\Api\Exception\QueryException;


$client = new IchabodCmsApi('<application_id>', '<api_key>');

/**
 * Get a list of all the published posts for the list
 *
 * @param array $list
 */
try {
    $list = $client->get('/space/<space_id>/list');
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
    $post = $client->get('/space/<space_id>/<slug>');
} catch (QueryException $e) {
    echo '<pre>';
    print_r($e->getMessage());
    echo '</pre>';
}

echo '<pre>';
print_r($post);
echo '</pre>';
