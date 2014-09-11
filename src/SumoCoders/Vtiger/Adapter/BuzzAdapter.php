<?php

namespace SumoCoders\Vtiger\Adapter;

use SumoCoders\Vtiger\Exception\InvalidResponseException;
use Buzz\Message\RequestInterface;
use Buzz\Message\MessageInterface;
use Buzz\Message\Request;
use Buzz\Message\Form\FormRequest;
use Buzz\Message\Response;
use Buzz\Client\FileGetContents;

/**
 * An adapter for the Buzz HTTP client
 *
 * @author Toon Daelman <toon@sumocoders.be>
 */
class BuzzAdapter implements Adapter
{
    /**
     * Issue a request
     *
     * @param string $method   The HTTP method for the request
     * @param string $host     The api host for the request
     * @param string $endpoint The api endpoint for the request
     * @param array  $postVars A list of post variables
     *
     * @return \stdClass The decoded json response from the server
     */
    public function request($method, $host, $endpoint, array $postVars = null)
    {
        $request = $this->createRequest($method, $host, $endpoint, $postVars);
        $response = $this->send($request);
        $data = $this->decode($response);

        $this->validate($data, $response);

        return $data;
    }

    /**
     * Create a request object
     *
     * @param string $method   The HTTP method for the request
     * @param string $host     The api host for the request
     * @param string $endpoint The api endpoint for the request
     * @param array  $postVars A list of post variables
     *
     * @return RequestInterface The request
     */
    protected function createRequest($method, $host, $endpoint, array $postVars = null)
    {
        if (empty($postVars)) {
            $request = new Request($method, $endpoint, $host);
        } else {
            $request = new FormRequest($method, $endpoint, $host);

            foreach ($postVars as $key => $value) {
                $request->setField($key, $value);
            }
        }

        return $request;
    }

    /**
     * Send the request
     *
     * @param RequestInterface $request The request to send
     *
     * @return MessageInterface The response
     */
    protected function send(RequestInterface $request)
    {
        $response = new Response();
        $client = new FileGetContents();

        $client->send($request, $response);

        return $response;
    }

    /**
     * Decode the response content from json
     *
     * @param MessageInterface $response The response we received
     *
     * @return \stdClass The decoded json response from the server
     */
    protected function decode(MessageInterface $response)
    {
        return json_decode($response->getContent());
    }

    /**
     * Validate the json response
     *
     * @param \stdClass        $data     The decoded json response from the server
     * @param MessageInterface $response The response we received
     *
     * @throws InvalidResponseException when the response indicates failure
     */
    protected function validate($data, MessageInterface $response)
    {
        if (!isset($data->success) || $data->success !== true || !isset($data->result)) {
            throw new InvalidResponseException('Invalid response: ' . $response);
        }
    }
}
