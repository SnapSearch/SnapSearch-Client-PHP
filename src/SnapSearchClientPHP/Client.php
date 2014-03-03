<?php

namespace SnapSearchClientPHP;

use SnapSearchClientPHP\SnapSearchException;

/**
 * Client contacts SnapSearch and retrieves the snapshot
 */
class Client{

    /**
     * Email to be used as the username for HTTP basic auth
     * 
     * @var string
     */
    protected $api_email;

    /**
     * Key to be used as the password for HTTP basic auth
     * 
     * @var string
     */
    protected $api_key;

    /**
     * Request parameters as an array to be json encoded and sent to the SnapSearch service
     * 
     * @var array
     */
    protected $request_parameters;

    /**
     * SnapSearch API url
     * 
     * @var string
     */
    protected $api_url;

    /**
     * Absolute path to CA certificate
     * 
     * @var string
     */
    protected $ca_path;

    /**
     * Constructor
     * 
     * @param string  $api_email          Email used for HTTP Basic
     * @param string  $api_key            Key used for HTTP Basic
     * @param array   $request_parameters Parameters passed to SnapSearch API
     * @param string  $api_url            Custom API Url
     * @param string  $ca_path            Custom CA certificate absolute path
     */
    public function __construct(
        $api_email, 
        $api_key,  
        array $request_parameters = null, 
        $api_url = false,
        $ca_path = false
    ){

        $this->api_email = $api_email;
        $this->api_key = $api_key;
        $this->request_parameters = ($request_parameters) ? $request_parameters : array();
        $this->api_url = ($api_url) ? $api_url : 'https://snapsearch.io/api/v1/robot';
        $this->ca_path = ($ca_path) ? $ca_path : __DIR__ . '/../../resources/cacert.pem';

    }

    /**
     * Sends a request to SnapSearch using the current url.
     * 
     * @param  string        $current_url Current URL that the Robot is going to be accessing
     * 
     * @return array|boolean Response array from SnapSearch or boolean false if there was an system error
     * 
     * @throws SnapSearchException If curl error
     * @throws SnapsearchException If validation error
     */
    public function request($current_url){

        //the current url must contain the entire url with the _escaped_fragment_ parsed out
        $this->request_parameters['url'] = $current_url;

        $payload = json_encode($this->request_parameters);

        if(function_exists('mb_strlen')){
            $payload_length = mb_strlen($payload, '8bit');
        }else{
            $payload_length = strlen($payload);
        }

        $curl = curl_init();

        //api url
        curl_setopt($curl, CURLOPT_URL, $this->api_url);
        //post method type
        curl_setopt($curl, CURLOPT_CUSTOMREQUEST, 'POST');
        //http basic auth
        curl_setopt($curl, CURLOPT_USERPWD, "{$this->api_email}:{$this->api_key}");
        //post payload
        curl_setopt($curl, CURLOPT_POSTFIELDS, $payload);
        //request headers
        curl_setopt($curl, CURLOPT_HTTPHEADER, array(
            'Content-Type: application/json',
            'Content-Length: ' . $payload_length,
        ));
        //include response headers
        curl_setopt($curl, CURLOPT_HEADER, true);
        //http timeout of 30s
        curl_setopt($curl, CURLOPT_TIMEOUT, 30);
        //accept compressed responses
        curl_setopt($curl, CURLOPT_ENCODING, '');
        //cacert information
        curl_setopt($curl, CURLOPT_CAINFO, $this->ca_path);
        //verify ssl connection
        curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, true);
        //verify ssl host
        curl_setopt($curl, CURLOPT_SSL_VERIFYHOST, 2);
        //return data as string
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);

        $response = curl_exec($curl);
        $curl_error = curl_error($curl);

        curl_close($curl);

        if($response === false){

            throw new SnapSearchException('Could not establish connection to SnapSearch due to curl connection error: ' . $curl_error);

        }

        $response = $this->parse_response($response);

        $body = $response['body'];

        if(isset($body['code']) AND $body['code'] == 'success'){

            //will return status, headers (array of name => value), html, screenshot, date
            return $body['content'];

        }elseif(isset($body['code']) AND $body['code'] == 'validation_error'){

            //means that something was incorrect from the request parameters or the url could not be accessed
            throw new SnapSearchException('Validation error from SnapSearch. Check your request parameters.', $body['content']);

        }else{

            //system error on SnapSearch, nothing we can do
            return false;

        }
        
    }

    /**
     * Parses the HTTP response and returns an array containing the status, headers and body.
     * 
     * @param  string $response Curl HTTP response containing the headers and body content
     * 
     * @return array
     */
    protected function parse_response($response){

        $result = explode("\r\n\r\n", $response, 2);

        $headers = array_shift($result);
        $body = array_shift($result);

        $status_code = $this->parse_status($headers);
        $headers = $this->parse_headers($headers);
        $body = $this->parse_body($body);

        return array(
            'status'    => $status_code,
            'headers'   => $headers,
            'body'      => $body,
        );

    }

    /**
     * Parses out the status code from the HTTP headers.
     * 
     * @param  string $headers Headers as a string from the HTTP response
     * 
     * @return integer         HTTP status code
     *
     * @throws SnapSearchException If the header string was malformed and could not be parsed
     */
    protected function parse_status($headers){

        $parts = explode(' ', substr($headers, 0, strpos($headers, "\r\n")));

        if(count($parts) < 2 || !is_numeric($parts[1])){

            throw new SnapSearchException('Could not parse response status code due to a malformed SnapSearch response.');

        }

        return intval($parts[1]);

    }

    /**
     * Parses out the headers into a headers array.
     * 
     * @param  string $headers Headers as a string from the HTTP response
     * 
     * @return array           Headers array with lower case header keys to header values. These keys use hyphens in between words.
     */
    protected function parse_headers($headers){

        //extract the lines and remove the HTTP status code portion
        $lines = preg_split("/(\r|\n)+/", $headers, -1, PREG_SPLIT_NO_EMPTY);
        array_shift($lines);

        $headers = array();
        foreach ($lines as $line) {
            list($name, $value) = explode(':', $line, 2);
            $headers[strtolower(trim($name))] = trim($value);
        }

        return $headers;

    }

    /**
     * Parses the JSON body content into an associative array.
     * 
     * @param  string $body Body content as a string
     * 
     * @return array
     */
    protected function parse_body($body){

        return json_decode($body, true);

    }
    
}