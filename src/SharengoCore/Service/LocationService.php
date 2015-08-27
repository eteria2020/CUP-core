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

    /**
     * @param long[] $polyX coordinates of longitude
     * @param long[] $polyY coordinates of latitude
     * @param long $x
     * @param long $y
     */
    public function isPointInPolygon($polyX,$polyY,$x,$y) {
        $j = count($polyY) - 1 ;
        $oddNodes = 0;

        for ($i=0; $i<$polySides; $i++) {
            if ($polyY[$i] < $y && $polyY[$j] >= $y || $polyY[$j] < $y && $polyY[$i] >= $y) {
                if ($polyX[$i] + ($y - $polyY[$i]) / ($polyY[$j] - $polyY[$i]) * ($polyX[$j] - $polyX[$i]) < $x) {
                    $oddNodes = !$oddNodes;
                }
            }
            $j = $i;
        }

        return $oddNodes;
    }
}
