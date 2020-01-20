<?php


namespace App\Controller;
use App\Util\DB;
use Exception;
use App\Entity\Weather;


class WeatherController
{
    /**
     * @var DB
     */
    public $db;
    private $curl;

    public function __construct()
    {
        $db = new DB();
        $this->db = $db;
    }

    /**
     * @return bool|string
     */
    public function executeRequest()
    {
        // build the url
        $this->curl = curl_init();
        $url = sprintf("%s?%s&%s", API_URL, 'id=2759794', 'APPID='.API_KEY);
        curl_setopt($this->curl, CURLOPT_URL, $url);
        curl_setopt($this->curl, CURLOPT_RETURNTRANSFER, 1);
        $result = curl_exec($this->curl);
        curl_close($this->curl);
        // make this return the Weather object
        return $result;
    }
}