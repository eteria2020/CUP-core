<?php

namespace SharengoCore\Service;

class LocationService
{
    /**
     * @param long $latitude
     * @param long $longitude
     * @return string Address corresponding to given lat and lng
     */
    public function getAddressFromCoordinates($latitude, $longitude)
    {
        $url = "http://maps.sharengo.it/reverse.php?format=json&zoom=18&addressdetails=1&lon=" . $longitude . "&lat=" . $latitude;
        $data = @file_get_contents($url);
        $jsondata = json_decode($data,true);

        $address = "\n";
        var_dump($jsondata);die;
        foreach ($jsondata['address'] as $key => $value) {
            $address .= $key . "=" . $value . ",\n";
        }
        return $address;

        return $jsondata['address']['road'] . ', ' .
            $jsondata['address']['neighbourhood'] . ', ' .
            $jsondata['address']['suburb'] . ', ' .
            $jsondata['address']['city'] . ', ' .
            $jsondata['address']['country'];
    }
}
