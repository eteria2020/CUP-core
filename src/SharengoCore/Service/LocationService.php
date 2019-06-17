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
        $result= null;

        try {
            $url = "http://maps.sharengo.it/reverse.php?format=json&zoom=18&addressdetails=1&lon=" . $longitude . "&lat=" . $latitude;
            $data = @file_get_contents($url);
            $jsondata = json_decode($data,true);

            if(isset($jsondata['error'])){

            } else {
                $road = '';
                $city = '';
                $country ='';

                if(isset($jsondata['address']['road'])) {
                    $road = $jsondata['address']['road'];
                }elseif (isset($jsondata['address']['pedestrian'])) {
                    $road = $jsondata['address']['pedestrian'];
                }

                if(isset($jsondata['address']['town'])) {
                    $city = $jsondata['address']['town'];
                }elseif (isset($jsondata['address']['city'])) {
                    $city = $jsondata['address']['city'];
                }elseif (isset($jsondata['address']['village'])) {
                    $city = $jsondata['address']['village'];
                }elseif (isset($jsondata['address']['suburb'])) {
                    $city = $jsondata['address']['suburb'];
                }

                if(isset($jsondata['address']['county'])) {
                    $country = $jsondata['address']['county'];
                }

                if($road=='' && $city=='' && $country=='') {
                    $result = '';
                } else {
                    $result = $road . ', ' . $city . ', ' . $country;
                }

            }

        } catch (Exception $ex) {
            $result= null;
        }

        return $result;
    }
}
