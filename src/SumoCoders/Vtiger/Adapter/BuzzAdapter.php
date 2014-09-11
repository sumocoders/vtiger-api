<?php

namespace SumoCoders\Vtiger\Adapter;

use SumoCoders\Vtiger\Exception\InvalidResponseException;
use Buzz\Message\Request;
use Buzz\Message\Form\FormRequest;
use Buzz\Message\Response;
use Buzz\Client\FileGetContents;

class BuzzAdapter implements Adapter
{
    public function request($method, $host, $endpoint, array $postVars = null)
    {
        $request = $this->createRequest($method, $host, $endpoint, $postVars);
        $response = $this->send($request);
        $data = $this->decode($response);

        $this->validate($data, $response);

        return $data;
    }

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

    protected function send($request)
    {
        $response = new Response();
        $client = new FileGetContents();

        $client->send($request, $response);

        return $response;
    }

    protected function decode($response)
    {
        return json_decode($response->getContent());
    }

    protected function validate($data, $response)
    {
        if (!isset($data->success) || $data->success !== true || !isset($data->result)) {
            throw new InvalidResponseException('Invalid response: ' . $response);
        }
    }
}
