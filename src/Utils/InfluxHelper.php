<?php

namespace App\Utils;

use InfluxDB2;
use InfluxDB2\Client;
use InfluxDB2\FluxTable;
use InfluxDB2\Model\Query;
use InfluxDB2\Point;
use App\Services\Config;

class InfluxHelper
{
    protected InfluxDB2\QueryApi $queryApi;
    protected InfluxDB2\WriteApi $writeApi;
    public string $bucket_name;

    public function __construct()
    {
        $client = new Client(array_merge(
            Config::getInfluxConfig(),
            ["precision" => InfluxDB2\Model\WritePrecision::NS]
        ));

        $this->queryApi = $client->createQueryApi();
        $this->writeApi = $client->createWriteApi();

        $this->bucket_name = Config::getInfluxConfig()["bucket"];
    }

    /**
     * @param $query Query|string
     * @return FluxTable[]|null
     */
    public function query($query)
    {
        return $this->queryApi->query($query);
    }

    /**
     * @param $point Point|string
     * @return void
     */
    public function write($point)
    {
        $this->writeApi->write($point);
    }
}
