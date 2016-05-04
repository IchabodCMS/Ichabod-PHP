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

/**
 * Simple API wrapper for IchabodCMS API
 *
 * IchabodCMS API: http://ichabodcms.com/developer
 * Wrapper:        https://github.com/IchabodCMS/Ichabod-PHP
 *
 * @version 1.0.0
 */
class IchabodCmsApi
{
    private $publicKey;
    private $privateKey;

    private $api_endpoint = 'http://ichabodcms.com/api';

    public  $verify_ssl   = true;
    private $lastError    = null;
    private $lastResponse = array();

    public function __construct($publicKey, $privateKey)
    {
        $this->publicKey = $publicKey;
        $this->privateKey = $privateKey;
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
     * @param  integer $data        Timeout set for cURL request
     *
     * @return array|false          Assoc array of decoded result
     */
    private function makeRequest($http_verb, $method, $data=array(), $timeout=10)
    {
        if (!function_exists('curl_init') || !function_exists('curl_setopt')) {
            throw new \Exception("cURL support is required, but can't be found.");
        }

        $url = $this->api_endpoint.'/'.$method;
        $this->last_error    = '';
        $response            = array('headers'=>null, 'body'=>null);
        $this->last_response = $response;

        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);

        $time = new \DateTime();
        $time->setTimezone(new \DateTimeZone('UTC'));
        $time_format = $time->format('c');

        $digest = $time_format ."\n". strtoupper($http_verb) ."\n". 'application/vnd.api+json' ."\n". md5(json_encode($data));
        $signature = base64_encode(hash_hmac('sha1', $digest, $this->privateKey, TRUE));

        curl_setopt($ch, CURLOPT_HTTPHEADER, array(
            "Accept: application/vnd.api+json",
            "Content-Type: application/vnd.api+json",
            "Date: $time_format",
            "Authorization: Atom {$this->publicKey}:{$signature}",
            "X-Accept-Version: 1"
        ));
        curl_setopt($ch, CURLOPT_USERAGENT, 'IchabodCMSApi (github.com/IchabodCMS/Ichabod-PHP)');
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
                $this->lastError = sprintf('%d: %s', $headers['http_code'], $body['message']);
            }

            return $body;
        }

        return false;
    }

}
