<?php

namespace SharengoCore\Service;

class LocationService
{
    private $config;
    private $serverInstance;

    public function __construct($config) {
        $this->config = $config;

        $this->serverInstance["id"] ="";
        if(isset($this->config['serverInstance'])) {
            $this->serverInstance = $this->config['serverInstance'];
        }
    }

    /**
     * Request reverse gecoding from Nominatim of Open Street Map (see http://nominatim.org/release-docs/latest/api/Reverse/)
     * The request is complient to the OSM Usage Policy (see https://operations.osmfoundation.org/policies/nominatim/)
     *
     * @param long $latitude
     * @param long $longitude
     * @return string Address corresponding to given lat and lng
     */
    public function getAddressFromCoordinates($latitude = 0, $longitude = 0)
    {
        $result = null;
        $zoom = 18;

        try {
            if(is_null($latitude) || is_null($longitude)){
                return $result;
            }

            if($latitude == 0 && $longitude == 0) {
                return $result;
            }

            $language = 'it';
            if($this->serverInstance['id']!="") {
                $language = substr($this->serverInstance['id'], 0, 2);
            }

//            $url = "https://nominatim.openstreetmap.org/reverse.php?format=json&zoom=18&addressdetails=1&lon=" . $longitude . "&lat=" . $latitude;
//            $data = @file_get_contents($url);
//            $jsondata = json_decode($data,true);

            sleep(2);
            $usr = sprintf("https://nominatim.openstreetmap.org/reverse.php?format=json&addressdetails=1&zoom=%s&lat=%s&lon=%s&accept-language=%s",
                $zoom,
                $latitude,
                $longitude,
                $language
                );

            $curl = curl_init();
            curl_setopt_array($curl, array(
                CURLOPT_URL => $usr,
                CURLOPT_RETURNTRANSFER => true,
                CURLOPT_ENCODING => "",
                CURLOPT_MAXREDIRS => 10,
                CURLOPT_TIMEOUT => 0,
                CURLOPT_FOLLOWLOCATION => true,
                CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
                CURLOPT_CUSTOMREQUEST => "GET",
                CURLOPT_USERAGENT => 'sharengo.'.$language,
                CURLOPT_REFERER => 'sharengo.'.$language
            ));

            $response = curl_exec($curl);
            $jsondata = json_decode($response,true);
            curl_close($curl);

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
