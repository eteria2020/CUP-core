<?php

namespace SharengoCore\Service;

use MvLabsDriversLicenseValidation\Response\Response;
use SharengoCore\Entity\Customers;
use SharengoCore\Entity\DriversLicenseValidation;
use SharengoCore\Entity\Repository\DriversLicenseValidationRepository;
use SharengoCore\Service\CountriesService;

use Doctrine\ORM\EntityManager;

class DriversLicenseValidationService
{
    /**
     * @var EntityManager
     */
    private $entityManager;

    /**
     * @var DriversLicenseValidationRepository
     */
    private $repository;

    /**
     * @var CountriesService
     */
    private $countriesService;


    /**
     * DriversLicenseValidationService constructor.
     *
     * @param EntityManager $entityManager
     * @param DriversLicenseValidationRepository $repository
     * @param \SharengoCore\Service\CountriesService $countriesService
     */
    public function __construct(
        EntityManager $entityManager,
        DriversLicenseValidationRepository $repository,
        CountriesService $countriesService
    ) {
        $this->entityManager = $entityManager;
        $this->repository = $repository;
        $this->countriesService = $countriesService;
    }

    /**
     * @param Customers $customer
     * @return [DriversLicenseValidation]
     */
    public function getByCustomer(Customers $customer)
    {
        return $this->repository->findByCustomer($customer);
    }

    /**
     * @param Customers $customer
     * @param Response $response
     * @param mixed[] $data
     * @param boolean $isFromScript
     * @param boolean|null $saveToDb
     * @param boolean $clear clears EntityManager after flush()
     * @return DriversLicenseValidation
     */
    public function addFromResponse(
        Customers $customer,
        Response $response,
        array $data,
        $isFromScript = false,
        $saveToDb = true,
        $clear = false
    ) {
        $validation = $this->addFromData(
            $customer,
            $response->valid(),
            $response->code(),
            $response->message(),
            $data,
            $isFromScript,
            $saveToDb,
            $clear
        );

        return $validation;
    }

    /**
     * @param Customers $customer
     * @param boolean $valid
     * @param string $code
     * @param string $message
     * @param mixed[] $data
     * @param boolean $isFromScript
     * @param boolean|null $saveToDb
     * @param boolean $clear clears EntityManager after flush()
     * @return DriversLicenseValidation
     */
    public function addFromData(
        Customers $customer,
        $valid,
        $code,
        $message,
        array $data,
        $isFromScript = false,
        $saveToDb = true,
        $clear = false
    ) {
        $validation = new DriversLicenseValidation(
            $customer,
            $valid,
            $code,
            $message,
            $data,
            $isFromScript
        );

        if ($saveToDb) {
            $this->entityManager->persist($validation);
            $this->entityManager->flush();
            if ($clear) {
                $this->entityManager->clear();
            }
        }

        return $validation;
    }

//    /**
//     * Fix the birth town for vehicle registration.
//     *
//     * @param $data
//     * @return string
//     */
//    public function changeTownForValidationDriverLicense($data)
//    {
//        $birthTown = strtoupper(trim($data['birthTown']));
//        if ($birthTown=="CASTELLAMMARE DI STABIA"){
//            $birthTown = 'CASTELLAMMARE STABIA';
//        }
//
//        return $birthTown;
//    }
//
//     /**
//     * This method returns a different birthPrertince,
//     * so if birthProvince == 'MB' changes to 'MI'
//     * because after creating an MB province the whole city was under the MI province.
//     * While birthProvince == 'LC' and the city is in array$municipalities_lecco_special
//     * sets birthProvince = 'BG' because the city in $municipalities_lecco_special
//     * was under the province of BG, all the More cities were under the province of CO
//     * @param array $data
//     * @return string
//     */
//    public function changeProvinceForValidationDriverLicense($data) {
//        $birthProvince = $data['birthProvince'];
//        switch ($birthProvince) {
//            //Monza-Brinaza --> Milano
//            case 'MB':
//                $birthProvince = 'MI';
//                break;
//            //Lecco --> Bergamo || Como
//            case 'LC':
//                $municipalities_lecco_special = array("CALOLZIOCORTE", "CARENNO", "ERVE", "MONTE MARENZO", "VERCURAGO");
//                $birthTown = strtoupper(trim($data['birthTown']));
//                if (in_array($birthTown, $municipalities_lecco_special)){
//                    $birthProvince = 'BG';
//                }else{
//                    $birthProvince = 'CO';
//                }
//                break;
//            //Biella --> Vercelli
//            case 'BI':
//                $birthProvince = 'VC';
//                break;
//            //Barletta-Andria-Trani --> Bari
//            case 'BT':
//                $birthProvince = 'BA';
//                break;
//            //Forlì-Cesena --> Forlì(old)
//            case 'FC':
//                $birthProvince = 'FO';
//                break;
//            //Pesaro-Urbino --> Pesaro(old)
//            case 'PU':
//                $birthProvince = 'PS';
//                break;
//            // Rimini/NOVAFELTRIA --> 'PS'
//            case 'RN':
//                $birthTown = strtoupper(trim($data['birthTown']));
//                if ($birthTown=="NOVAFELTRIA"){
//                    $birthProvince = 'PS';
//                }
//                break;
//            //Vibo-Valentia --> Catanzaro
////            case 'VV':
////                $birthProvince = 'CZ';
////                break;
//            //Carbonia-Iglesias --> Cagliari
//            case 'CI':
//                $birthProvince = 'CA';
//                break;
//        }
//        return $birthProvince;
//    }

    /**
     * Fix the data before send to check driver license.
     *
     * @param $data
     * @return mixed
     */
    public function fixDataForValidationDriverLicense($data) {
        // Fix country

        $data['driverLicenseSurname'] = $this->replaceDiacriticsChar($data['driverLicenseSurname']);
        $data['driverLicenseName'] = $this->replaceDiacriticsChar($data['driverLicenseName']);
        $data['birthTown'] = $this->replaceDiacriticsChar($data['birthTown']);

        $data['birthCountryMCTC'] = $this->countriesService->getMctcCode($data['birthCountry']);

        // Fix province and town
        switch ($data['birthProvince']) {
            // FERMO (AP) --> FEERMO (FM)
            case 'AP':
                if ($data['birthTown']=='FERMO') {
                    $data['birthProvince'] = 'FM';
                }
                break;

            //Biella --> Vercelli
            case 'BI':
                $data['birthProvince'] = 'VC';
                break;

            //Barletta-Andria-Trani --> Bari
            case 'BT':
                $data['birthProvince'] = 'BA';
                break;

            //Carbonia-Iglesias --> Cagliari
            case 'CI':
                $data['birthProvince'] = 'CA';
                break;

            //Forlì-Cesena --> Forlì(old)
            case 'FC':
                $data['birthProvince'] = 'FO';
                break;

            //Lecco --> Bergamo || Como
            case 'LC':
                $municipalities_lecco_special = array("CALOLZIOCORTE", "CARENNO", "ERVE", "MONTE MARENZO", "VERCURAGO");
                $birthTown = strtoupper(trim($data['birthTown']));
                if (in_array($birthTown, $municipalities_lecco_special)){
                    $data['birthProvince'] = 'BG';
                } else {
                    $data['birthProvince'] = 'CO';
                }
                break;

            //Monza-Brinaza --> Milano
            case 'MB':
                $data['birthProvince'] = 'MI';
                break;

            // CASTELLAMMARE DI STABIA --> CASTELLAMMARE STABIA
            case 'NA':
                if ($data['birthTown']=='CASTELLAMMARE DI STABIA'){
                    $data['birthTown'] = 'CASTELLAMMARE STABIA';
                }
                break;

            // BARI SARDO (OR) --> BARI SARDO (NU)
            case 'OR':
                if ($data['birthTown']=='BARI SARDO') {
                    $data['birthProvince'] = 'NU';
                }
                break;

            // PESARO-URBINO (PU) --> PESARO-URBINO (PS)
            case 'PU':
                $data['birthProvince'] = 'PS';
                break;

            // NOVAFELTRIA (RN) --> NOVAFELTRIA (PS)
            case 'RN':
                if ($data['birthTown']=='NOVAFELTRIA'){
                    $data['birthProvince'] = 'PS';
                }
                break;

            // SAN GAVINO MONREALE (VS) --> SAN GAVINO MONREALE (CA)
            case 'VS':
                if ($data['birthTown']=='SAN GAVINO MONREALE'){
                    $data['birthProvince'] = 'CA';
                }
                break;

            // VIBO VALENTIA (VV) --> VIBO VALENTIA (CS)
            case 'VV':
                if ($data['birthTown']=='VIBO VALENTIA'){
                    $data['birthProvince'] = 'CS';
                }
                break;
        }

        return $data;
    }

    /**
     * Replace the diacritics chars widt a similar char and '
     *
     * @param $name
     */
    private function replaceDiacriticsChar($name) {

        $table = array(
            "À"=>"a'", "à"=>"a'",
            "Á"=>"A'", "á"=>"a'",
            "È"=>"E'", "è"=>"e'",
            "É"=>"E'", "é"=>"e'",
            "Ì"=>"I'", "ì"=>"i'",
            "Í"=>"I'", "í"=>"i'",
            "Ò"=>"O'", "ò"=>"o'",
            "Ó"=>"O'", "ó"=>"o'",
            "Ù"=>"U'", "ù"=>"u'",
            "Ú"=>"U'", "ú"=>"u'",
            "Ý"=>"Y'", "ý"=>"y'"
        );

        return strtr($name, $table);
    }
}
