<?php

namespace SharengoCore\Service;

class CrawlerService
{
    private function apiRequest($id, $status)
    {
        try {
            $curl = curl_init();

            curl_setopt_array($curl, array(
                CURLOPT_URL => "https://manage.sharengo.it/crawler/api.php?id=" . $id . "&status=" .$status,
                CURLOPT_RETURNTRANSFER => true,
                CURLOPT_ENCODING => "",
                CURLOPT_MAXREDIRS => 10,
                CURLOPT_TIMEOUT => 10,
                CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
                CURLOPT_CUSTOMREQUEST => "GET",
                CURLOPT_POSTFIELDS => "",
                CURLOPT_HTTPHEADER => array(
                    "cache-control: no-cache"
                ),
            ));

            $response = curl_exec($curl);
            $err = curl_error($curl);

            curl_close($curl);

            if ($err) {
                return null;
            } else {
                $response = json_decode($response, true);
                return $response;
            }
        } catch(\Exception $e){
            return null;
        }
    }

    public function isValidUser($id){
        $response = $this->apiRequest($id, null);

        if (is_array($response)){
            return (isset($response["status"]) && $response["status"] == 200);
        } else {
            return false;
        }
    }

    public function getCustomerInformation($id){
        
        return $this->apiRequest($id, null);

    }

    public function setLoggerEndTs($id, $status){
        $response = $this->apiRequest($id, $status);

        if (is_array($response)){
            return (isset($response["status"]) && $response["status"] == 200);
        } else {
            return false;
        }
    }


}
