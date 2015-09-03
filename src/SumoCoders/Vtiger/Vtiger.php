<?php

namespace SumoCoders\Vtiger;

use SumoCoders\Vtiger\Adapter\Adapter;

/**
 * Vtiger api wrapper
 *
 * @author Toon Daelman <toon@sumocoders.be>
 */
class Vtiger
{
    /**
     * @var Adapter The adapter we'll use for sending requests
     */
    protected $adapter;

    /**
     * @var string The host of the Vtiger instance
     */
    protected $host;

    /**
     * @var string The username of the user we'll login as
     */
    protected $user;

    /**
     * @var string The api secret for this user
     */
    protected $secret;

    /**
     * @var string The current session id
     */
    protected $sessionId;

    /**
     * @var int The time the session was made (for long running sessions, we'll automatically reconnect)
     */
    protected $sessionTime;

    /**
     * Constructor
     *
     * @param Adapter $adapter The adapter we'll use for sending requests
     * @param string  $host    The host of the Vtiger instance
     * @param string  $user    The username of the user we'll login as
     * @param string  $secret  The api secret for this user
     */
    public function __construct(Adapter $adapter, $host, $user, $secret)
    {
        $this->adapter = $adapter;
        $this->host = (string) $host;
        $this->user = (string) $user;
        $this->secret = (string) $secret;
    }

    /**
     * Issue a a request (delegate it to our adapter)
     *
     * @param string $method   The HTTP method for the request
     * @param string $endpoint The api endpoint for the request
     * @param array  $postVars A list of post variables
     *
     * @return array An associative array (parsed json response from the api)
     */
    protected function request($method, $endpoint, array $postVars = null)
    {
        return $this->adapter->request($method, $this->host, $endpoint, $postVars);
    }

    /**
     * Get a login token (the first step in authorizing)
     *
     * @return string A login token
     */
    protected function getToken()
    {
        $response = $this->request(
            'GET',
            '/webservice.php?operation=getchallenge&username=' . $this->user
        );

        return $response->result->token;
    }

    /**
     * Validate the login token (the second step in authorizing)
     *
     * @param string $token The token we received in the first step of authorizing
     *
     * @return string The session id for the created session
     */
    protected function login($token)
    {
        $accessKey = md5($token . $this->secret);

        $response = $this->request(
            'POST',
            '/webservice.php',
            array(
                'operation' => 'login',
                'username' => $this->user,
                'accessKey' => $accessKey,
            )
        );

        return $response->result->sessionName;
    }

    /**
     * Get the current session id or walk through the authorizing process for a new one.
     *
     * @return string The session id for the current session
     */
    protected function getSession()
    {
        $time = time();

        if (empty($this->sessionId) || $time - 240 > $this->sessionTime) {
            $token = $this->getToken();
            $this->sessionId = $this->login($token);
            $this->sessionTime = $time;
        }

        return $this->sessionId;
    }

    /**
     * Get a list of all types that are in the CRM
     *
     * @return array A response object
     */
    public function getTypes()
    {
        $response = $this->request(
            'GET',
            '/webservice.php?operation=listtypes&sessionName=' . $this->getSession()
        );

        return $response;
    }

    /**
     * Create an entity
     *
     * @param string $type   The entity type
     * @param array  $entity The new entity
     *
     * @return array A response object
     */
    public function create($type, array $entity)
    {
        $response = $this->request(
            'POST',
            '/webservice.php',
            array(
                'operation' => 'create',
                'sessionName' => $this->getSession(),
                'element' => json_encode($entity),
                'elementType' => $type,
            )
        );

        return $response;
    }

    /**
     * Get the data from an entity
     *
     * @param string $id The entity's id
     *
     * @return array A response object
     */
    public function read($id)
    {
        $response = $this->request(
            'GET',
            '/webservice.php?operation=retrieve&sessionName=' . $this->getSession() . '&id=' . $id
        );

        return $response;
    }

    /**
     * Update an entity
     *
     * @param array $entity The new entity data (make sure to include id)
     *
     * @return array A response object
     */
    public function update($entity)
    {
        $response = $this->request(
            'POST',
            '/webservice.php',
            array(
                'operation' => 'update',
                'sessionName' => $this->getSession(),
                'element' => json_encode($entity),
            )
        );

        return $response;
    }

    /**
     * Delete an entity
     *
     * @param string $id The entity's id
     *
     * @return array A response object
     */
    public function delete($id)
    {
        $response = $this->request(
            'POST',
            '/webservice.php',
            array(
                'operation' => 'delete',
                'sessionName' => $this->getSession(),
                'id' => $id,
            )
        );

        return $response;
    }

    /**
     * Query the database
     *
     * @param string $select The selected fields
     * @param string $from   The name of the entities we want
     * @param string $where  The where filter
     * @param string $order  The order clause
     * @param string $limit  The limit clause
     * @param string $offset The offset clause
     *
     * @return array A response object
     */
    public function query($select, $from, $where = null, $order = null, $limit = null, $offset = null)
    {
        $query = 'SELECT ' . $select;
        $query .= ' FROM ' . $from;

        if ($where) {
            $query .= ' WHERE ' . $where;
        }

        if ($order) {
            $query .= ' ORDER BY  ' . $order;
        }

        if ($limit) {
            $query .= ' LIMIT ';

            if ($offset) {
                $query .= $offset . ', ';
            }

            $query .= $limit;
        }
        $query .= ';';

        $response = $this->request(
            'GET',
            '/webservice.php?operation=query&sessionName=' . $this->getSession() . '&query=' . urlencode($query)
        );

        return $response;
    }
}
