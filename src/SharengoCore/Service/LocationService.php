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
                $road = (isset($jsondata['address']['road']) ?
                        $jsondata['address']['road'] :
                        (isset($jsondata['address']['pedestrian']) ? $jsondata['address']['pedestrian'] : ''));

                $city = (isset($jsondata['address']['town']) ?
                        $jsondata['address']['town'] :
                        $jsondata['address']['city']);

                $result = 
                    (($road != '') ? $road . ', ' : '') .
                    (($city != '') ? $city . ', ' : '') .
                    $jsondata['address']['county'];
            }

        } catch (Exception $ex) {
            $result= null;
        }

        return $result;
    }
}
