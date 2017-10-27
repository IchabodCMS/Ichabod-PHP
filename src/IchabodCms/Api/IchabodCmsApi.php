<?php

/*
 * This file is part of IchabodCMS API Wrapper
 *
 * (c) James Rickard <james@frodosghost.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace IchabodCms\Api;

use IchabodCms\Api\Exception\QueryException;

/**
 * Simple API wrapper for IchabodCMS API
 *
 * IchabodCMS API: http://ichabodcms.com/api/getting-started
 * Wrapper:        https://github.com/IchabodCMS/Ichabod-PHP
 *
 * @version 1.0.0
 */
class IchabodCmsApi
{
    private $applicationId;
    private $apiKey;

    private $api_endpoint = 'http://api.ichabodcms.com';

    public  $verify_ssl   = true;
    private $lastError    = null;
    private $lastErrorNo  = null;
    private $lastResponse = array();

    public function __construct($applicationId, $apiKey)
    {
        $this->applicationId = $applicationId;
        $this->apiKey = $apiKey;
    }

    /**
     * Run the get on the API
     *
     * @param  string $method     Method on the API
     * @param  array  $attributes Attributes possible to be included in the GET query
     * @return JSON
     */
    public function get($method, $attributes = [])
    {
        $response = $this->makeRequest('get', $method, $attributes);

        return $this->formatResponse($response);
    }

    /**
     * Return last error after API query
     *
     * @return array|false
     */
    public function getLastError()
    {
        $lastError = (!is_null($this->lastError)) ? $this->lastError : false;

        return $lastError;
    }

    /**
     * Performs the underlying HTTP request.
     * @param  string  $http_verb   The HTTP verb to use: get, post, put, patch, delete
     * @param  string  $method      The API method to be called
     * @param  array   $data        Assoc array of parameters to be passed
     * @param  integer $timeout     Timeout set for cURL request
     *
     * @return array|false          Assoc array of decoded result
     */
    private function makeRequest($http_verb, $method, $data=[], $timeout=10)
    {
        if (!function_exists('curl_init') || !function_exists('curl_setopt')) {
            throw new \Exception("cURL support is required, but can't be found.");
        }

        $url = $this->api_endpoint . $method;
        $this->last_error    = '';
        $response            = array('headers'=>null, 'body'=>null);
        $this->last_response = $response;

        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);

        $time = new \DateTime();
        $time->setTimezone(new \DateTimeZone('UTC'));
        $time_format = $time->format('c');

        /*$digest = $time_format ."\n". strtoupper($http_verb) ."\n". 'application/vnd.api+json' ."\n". md5(json_encode($data));
        $signature = base64_encode(hash_hmac('sha1', $digest, $this->apiKey, TRUE));*/

        curl_setopt($ch, CURLOPT_HTTPHEADER, array(
            "Accept: application/vnd.api+json",
            "Content-Type: application/vnd.api+json",
            "Date: $time_format",
            "Authorization: Bearer {$this->applicationId}:{$this->apiKey}",
            "X-Accept-Version: 1"
        ));
        curl_setopt($ch, CURLOPT_USERAGENT, 'IchabodAPI (github.com/IchabodCMS/Ichabod-PHP)');
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_TIMEOUT, $timeout);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, $this->verify_ssl);
        curl_setopt($ch, CURLOPT_HTTP_VERSION, CURL_HTTP_VERSION_1_0);
        curl_setopt($ch, CURLOPT_ENCODING, '');

        switch($http_verb) {
            case 'post':
                curl_setopt($ch, CURLOPT_POST, true);
                curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));
                break;
            case 'get':
                $query = http_build_query($data);
                curl_setopt($ch, CURLOPT_URL, $url.'?'.$query);
                break;
            case 'delete':
                curl_setopt($ch, CURLOPT_CUSTOMREQUEST, 'DELETE');
                break;
            case 'patch':
                curl_setopt($ch, CURLOPT_CUSTOMREQUEST, 'PATCH');
                curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));
                break;

            case 'put':
                curl_setopt($ch, CURLOPT_CUSTOMREQUEST, 'PUT');
                curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));
                break;
        }

        $response['body']    = curl_exec($ch);
        $response['headers'] = curl_getinfo($ch);

        if ($response['body'] === false) {
            $this->lastError = curl_error($ch);
            $this->lastErrorNo = curl_errno($ch);

            throw new QueryException(sprintf('cUrl Error (%d) Thrown: %s', $this->lastErrorNo, $this->lastError), [
                'detail' => $this->lastError,
                'status' => $this->lastErrorNo
            ], $this->lastErrorNo);
        }

        curl_close($ch);
        return $response;
    }

    private function formatResponse($response)
    {
        $this->lastResponse = $response;

        if (!empty($response['body'])) {
            $headers = $response['headers'];
            $body = json_decode($response['body'], true);

            if ( !(($headers['http_code'] == '200') || ($headers['http_code'] == '201')) ) {
                throw new QueryException(sprintf('%d: %s', $headers['http_code'], $body['error']['detail']), $body['error'], $headers['http_code']);
            }

            return $body;
        }

        return false;
    }

}
