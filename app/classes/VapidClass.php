<?php

namespace App\classes;

class VapidClass
{
    /**
     * @param false|string $base64String
     *
     * @return int[]
     *
     * @psalm-return list{0?: int<0, 255>,...}
     */
    public static function urlBase64ToUint8Array(string|false $base64String): array
    {
        $base64String = $base64String !== false ? $base64String : '';
        $padding = str_repeat('=', (4 - strlen($base64String) % 4) % 4);
        $base64 = strtr($base64String . $padding, '-_', '+/');
        $rawData = base64_decode($base64);
        $outputArray = array();
        for ($i = 0; $i < strlen($rawData); ++$i) {
            $outputArray[] = ord($rawData[$i]);
        }
        return $outputArray;
    }
}
   


