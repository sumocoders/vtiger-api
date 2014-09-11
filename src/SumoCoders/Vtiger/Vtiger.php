<?php

namespace SumoCoders\Vtiger;

use SumoCoders\Vtiger\Adapter\Adapter;

class Vtiger
{
    protected $adapter;
    protected $host;
    protected $user;
    protected $secret;

    protected $sessionId;
    protected $sessionTime;

    public function __construct(Adapter $adapter, $host, $user, $secret)
    {
        $this->adapter = $adapter;
        $this->host = (string) $host;
        $this->user = (string) $user;
        $this->secret = (string) $secret;
    }

    protected function request($method, $endpoint, array $postVars = null)
    {
        return $this->adapter->request($method, $this->host, $endpoint, $postVars);
    }

    protected function getToken()
    {
        $response = $this->request(
            'GET',
            '/webservice.php?operation=getchallenge&username=' . $this->user
        );

        return $response->result->token;
    }

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

    public function getTypes()
    {
        $response = $this->request(
            'GET',
            '/webservice.php?operation=listtypes&sessionName=' . $this->getSession()
        );

        return $response->result;
    }

    public function create($type, $entity)
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

    public function read($id)
    {
        $response = $this->request(
            'GET',
            '/webservice.php?operation=retrieve&sessionName=' . $this->getSession() . '&id=' . $id
        );

        return $response;
    }

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

        $response = $this->request(
            'GET',
            '/webservice.php?operation=query&sessionName=' . $this->getSession() . '&query=' . $query
        );

        return $response;
    }
}
