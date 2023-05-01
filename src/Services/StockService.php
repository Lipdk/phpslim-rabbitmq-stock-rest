<?php

namespace App\Services;

use App\Utilities\Config;
use StdClass;

class StockService
{
    /**
     * @param $stockCode
     * @return array
     */
    public function getStockInfo($stockCode): array
    {
        $response = [];
        $curlUrl = sprintf(Config::getStockApiUrl(), $stockCode);
        parse_str(parse_url($curlUrl)['query'] ?? '', $parsedUrl);
        $format = $parsedUrl['e'] ?? 'json';
        $curl = curl_init();

        curl_setopt_array($curl, [
            CURLOPT_URL => $curlUrl,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_ENCODING => '',
            CURLOPT_MAXREDIRS => 10,
            CURLOPT_TIMEOUT => 0,
            CURLOPT_FOLLOWLOCATION => true,
            CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
            CURLOPT_CUSTOMREQUEST => 'GET',
        ]);

        $curlResponse = curl_exec($curl);
        curl_close($curl);

        if (!empty($curlResponse)) {
            if ($format == 'csv') {
                $rows = array_map('str_getcsv', explode("\n", $curlResponse));
                $response = array_combine(array_map('strtolower', $rows[0] ?? []), $rows[1] ?? []);
            } else {
                $response = json_decode($curlResponse, true);
                $response = $response['symbols'][0] ?? [];
            }
        }

        $date = new \DateTime($response['date'] ?? 'now');
        $response['date'] = $date->format('Y-m-d\TH:i:s\Z');

        return $response;
    }

    /**
     * @param array $stock
     * @return array
     */
    public function normalizeStockResponse(array $stock = []) : array
    {
        $response = [];
        $properties = [
            'name' => 'string',
            'symbol' => 'string',
            'open' => 'float',
            'high' => 'float',
            'low' => 'float',
            'close' => 'float',
        ];

        foreach ($properties as $property => $type) {
            if (array_key_exists($property, $stock)) {
                $value = $stock[$property];

                if ($type == 'float' && is_string($stock[$property])) {
                    $value = number_format((float)$stock['open'], 2, '.', '' );
                }

                $response[$property] = $value;
            }
        }

        return $response;
    }


}
