<?php

namespace SumoCoders\Vtiger\Adapter;

/**
 * The adapter interface, all adapters should implement this
 *
 * @author Toon Daelman <toon@sumocoders.be>
 */
interface Adapter
{
    /**
     * Issue a request
     *
     * @param string $method   The HTTP method for the request
     * @param string $host     The api host for the request
     * @param string $endpoint The api endpoint for the request
     * @param array  $postVars A list of post variables
     *
     * @return array An associative array (decoded json response from the server)
     */
    public function request($method, $host, $endpoint, array $postVars = null);
}
