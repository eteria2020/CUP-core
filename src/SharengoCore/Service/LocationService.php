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
        $url = "http://maps.googleapis.com/maps/api/geocode/json?latlng=".$latitude.",".$longitude."&sensor=true";
        $data = @file_get_contents($url);
        $jsondata = json_decode($data,true);
        if(is_array($jsondata) && $jsondata['status'] == "OK")
        {
            return $jsondata['results'][0]['formatted_address'];
        }
    }
}
