<?php

namespace App\Repository\Dealers;

use Illuminate\Support\Arr;
use GuzzleHttp\Client as HttpClient;
use GuzzleHttp\Exception\ClientException;

/**
 * 东南汽车。供应商接口
 */
class Northeast
{
    protected $httpClient = null;

    public function __construct()
    {
        $this->httpClient = new HttpClient([
            'base_uri' => 'http://car.soueast-motor.com',
        ]);
    }

    /**
     * 同步经销商
     * @throws Exception
     * @return array
     */
    public function getDealers()
    {
        // 获取省份
        try {
            $response = $this->httpClient->request('GET', '/app/api2.php', [
                'query' => [
                    'act' => 'province',
                ]
            ]);
            $result = json_decode($response->getBody()->getContents(), true);
            if(empty($result) || Arr::get($result, 'status') != 200) {
                throw new \Exception('响应省份异常:' . Arr::get($result, 'message', ''), 1);
            }
            $provinces = Arr::get($result, 'result', []);
            if(!is_array($provinces)) $provinces = [];
        } catch (ClientException $e) {
            throw new \Exception($e->getMessage(), 1);
        }
        
        // 获取市
        $cityDic = [];
        foreach($provinces as $item) {
            $province = $item['province'];
            try {
                $response = $this->httpClient->request('GET', '/app/api2.php', [
                    'query' => [
                        'act' => 'city',
                        'province' => $province,
                    ]
                ]);
                $result = json_decode($response->getBody()->getContents(), true);
                if(empty($result) || Arr::get($result, 'status') != 200) {
                    throw new \Exception('响应市异常:' . Arr::get($result, 'message', ''), 1);
                }
                $citys = Arr::get($result, 'result', []);
                if(!is_array($citys)) $citys = [];
                foreach($citys as $city) {
                    $_city = $city['city'];
                    $cityDic[$_city] = $province;
                }
            } catch (ClientException $e) {
                throw new \Exception($e->getMessage(), 1);
            }
        }

        // 获取经销商
        $dealerResults = [];
        foreach ($cityDic as $city => $province) {
            try {
                $response = $this->httpClient->request('GET', '/app/api2.php', [
                    'query' => [
                        'act' => 'dealer',
                        'city' => $city,
                    ]
                ]);
                $result = json_decode($response->getBody()->getContents(), true);
                if(empty($result) || Arr::get($result, 'status') != 200) {
                    throw new \Exception('响应经销商异常:' . Arr::get($result, 'message', ''), 1);
                }
                $dealers = Arr::get($result, 'result', []);
                if(!is_array($dealers)) $dealers = [];
                foreach($dealers as $dealer) {
                    $_name = Arr::get($dealer, 'short_name', '');
                    $dealerResults[] = [
                        'province' => $province,
                        'city' => $city,
                        'code' => Arr::get($dealer, 'code', ''),
                        'name' => Arr::get($dealer, 'name', $_name),
                        'simplify' => $_name,
                        'type' => '',
                        'tel' => Arr::get($dealer, 'tel', ''),
                        'supports' => strval(Arr::get($dealer, 'brand', '')),
                    ];
                }
            } catch (ClientException $e) {
                throw new \Exception($e->getMessage(), 1);
            }
        }

        return $dealerResults;
    }
}