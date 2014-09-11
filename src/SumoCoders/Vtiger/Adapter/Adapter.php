<?php

namespace SumoCoders\Vtiger\Adapter;

interface Adapter
{
    public function request($method, $host, $endpoint, array $postVars = null);
}
