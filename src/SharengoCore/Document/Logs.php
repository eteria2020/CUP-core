<?php

namespace SharengoCore\Document;

use Doctrine\ODM\MongoDB\Mapping\Annotations as ODM;

/**
 * Events
 *
 * @ODM\Document(collection="logs", repositoryClass="SharengoCore\Document\Repository\LogsRepository")
 */
class Logs
{
    /** @ODM\Id */
    private $id;

    /**
     * @var boolean
     *
     * @ODM\Field(type="boolean", name="AccStatus");
     */
    private $accStatus;
    
    /**
     * @var string
     *
     * @ODM\Field(type="string", name="AndroidBuild");
     */
    private $androidBuild;
    
    /**
     * @var string
     *
     * @ODM\Field(type="string", name="AndroidDevice");
     */
    private $androidDevice;
    
    /**
     * @var DateTime
     *
     * @ODM\Field(type="date", name="AppCode");
     */
    private $appCode;
    
    /**
     * @var string
     *
     * @ODM\Field(type="string", name="AppName");
     */
    private $appName;
    
    /**
     * @var boolean
     *
     * @ODM\Field(type="boolean", name="BsmFault");
     */
    private $bsmFault;
    
    /**
     * @var boolean
     *
     * @ODM\Field(type="boolean", name="BrakesOn");
     */
    private $brakesOn;
    
    /**
     * @var boolean
     *
     * @ODM\Field(type="boolean", name="ChCommStatus");
     */
    private $chCommStatus;
    
    /**
     * @var boolean
     *
     * @ODM\Field(type="boolean", name="ChFault");
     */
    private $chFault;
    
    /**
     * @var boolean
     *
     * @ODM\Field(type="boolean", name="ChHeatFault");
     */
    private $chHeatFault;
    
    /**
     * @var DateTime
     *
     * @ODM\Field(type="date", name="ChStatus");
     */
    private $chStatus;
    
    /**
     * @var boolean
     *
     * @ODM\Field(type="boolean", name="ChargeCommStatus");
     */
    private $chargeCommStatus;
    
    /**
     * @var string
     *
     * @ODM\Field(type="string", name="DeviceSn");
     */
    private $deviceSn;
    
    /**
     * @var string
     *
     * @ODM\Field(type="string", name="GPS");
     */
    private $gps;
    
    /**
     * @var string
     *
     * @ODM\Field(type="string", name="GearStatus");
     */
    private $gearStatus;
    
    /**
     * @var string
     *
     * @ODM\Field(type="string", name="HbVer");
     */
    private $hbVer;
    
    /**
     * @var string
     *
     * @ODM\Field(type="string", name="HwVersion");
     */
    private $hwVersion;
    
    /**
     * @var DateTime
     *
     * @ODM\Field(type="date", name="IMEI");
     */
    private $imei;
    
    /**
     * @var boolean
     *
     * @ODM\Field(type="boolean", name="KMStatus");
     */
    private $kmStatus;
    
    /**
     * @var boolean
     *
     * @ODM\Field(type="boolean", name="KSStatus");
     */
    private $ksStatus;
    
    /**
     * @var string
     *
     * @ODM\Field(type="string", name="Keytatus");
     */
    private $keyStatus;
    
    /**
     * @var integer
     *
     * @ODM\Field(type="integer", name="Km");
     */
    private $km;
    
    /**
     * @var boolean
     *
     * @ODM\Field(type="boolean", name="LcdOn");
     */
    private $lcdOn;
    
    /**
     * @var string
     *
     * @ODM\Field(type="string", name="MCU");
     */
    private $mcu;
    
    /**
     * @var string
     *
     * @ODM\Field(type="string", name="MCUModel");
     */
    private $mcuModel;
    
    /**
     * @var boolean
     *
     * @ODM\Field(type="boolean", name="MotFalut");
     */
    private $motFalut;
    
    /**
     * @var DateTime
     *
     * @ODM\Field(type="date", name="MotT");
     */
    private $motT;
    
    /**
     * @var boolean
     *
     * @ODM\Field(type="boolean", name="MotTempHigh");
     */
    private $motTempHigh;
    
    /**
     * @var string
     *
     * @ODM\Field(type="string", name="MotV");
     */
    private $motV;
    
    /**
     * @var boolean
     *
     * @ODM\Field(type="boolean", name="PPStatus");
     */
    private $ppStatus;
    
    /**
     * @var DateTime
     *
     * @ODM\Field(type="date", name="PackA");
     */
    private $packA;
    
    /**
     * @var string
     *
     * @ODM\Field(type="string", name="PackStatus");
     */
    private $packStatus;
    
    /**
     * @var DateTime
     *
     * @ODM\Field(type="date", name="PackV");
     */
    private $packV;
    
    /**
     * @var boolean
     *
     * @ODM\Field(type="boolean", name="PreChFault");
     */
    private $preChFault;
    
    /**
     * @var boolean
     *
     * @ODM\Field(type="boolean", name="ReadyOn");
     */
    private $readyOn;
    
    /**
     * @var string
     *
     * @ODM\Field(type="string", name="Release");
     */
    private $release;
    
    /**
     * @var string
     *
     * @ODM\Field(type="string", name="SDK");
     */
    private $sdk;
    
    /**
     * @var string
     *
     * @ODM\Field(type="string", name="SDKVer");
     */
    private $SDKVer;
    
    /**
     * @var DateTime
     *
     * @ODM\Field(type="date", name="SIM_SN");
     */
    private $sim_sn;
    
    /**
     * @var integer
     *
     * @ODM\Field(type="integer", name="SOC");
     */
    private $soc;
    
    /**
     * @var DateTime
     *
     * @ODM\Field(type="date", name="Service");
     */
    private $service;
    
    /**
     * @var integer
     *
     * @ODM\Field(type="integer", name="Speed");
     */
    private $speed;
    
    /**
     * @var string
     *
     * @ODM\Field(type="string", name="TBoxHw");
     */
    private $tBoxHw;
    
    /**
     * @var string
     *
     * @ODM\Field(type="string", name="TBoxSw");
     */
    private $tBoxsw;
    
    /**
     * @var string
     *
     * @ODM\Field(type="string", name="VER");
     */
    private $ver;
    
    /**
     * @var string
     *
     * @ODM\Field(type="string", name="VIN");
     */
    private $vin;
    
    /**
     * @var string
     *
     * @ODM\Field(type="string", name="VINCode");
     */
    private $vinCode;
    
    /**
     * @var string
     *
     * @ODM\Field(type="string", name="Versions");
     */
    private $versions;
    
    /**
     * @var DateTime
     *
     * @ODM\Field(type="date", name="WARN_INDEX");
     */
    private $warnIndex;
    
    /**
     * @var DateTime
     *
     * @ODM\Field(type="date", name="WARN_STATUS");
     */
    private $warnStatus;
    
    /**
     * @var boolean
     *
     * @ODM\Field(type="boolean", name="batterySafety");
     */
    private $batterySafety;
    
    /**
     * @var boolean
     *
     * @ODM\Field(type="boolean", name="charging");
     */
    private $charging;
    
    /**
     * @var string
     *
     * @ODM\Field(type="string", name="clock");
     */
    private $clock;
    
    /**
     * @var boolean
     *
     * @ODM\Field(type="boolean", name="closeEnabled");
     */
    private $closeEnabled;
    
    /**
     * @var integer
     *
     * @ODM\Field(type="integer", name="cputemp");
     */
    private $cputemp;
    
    /**
     * @var float
     *
     * @ODM\Field(type="float", name="ext_lat");
     */
    private $extLat;
    
    /**
     * @var float
     *
     * @ODM\Field(type="float", name="ext_lon");
     */
    private $extLon;
    
    /**
     * @var integer
     *
     * @ODM\Field(type="integer", name="ext_time");
     */
    private $extTime;
    
    /**
     * @var string
     *
     * @ODM\Field(type="string", name="fwVer");
     */
    private $fwVer;
    
    /**
     * @var string
     *
     * @ODM\Field(type="string", name="gps_box_acc");
     */
    private $gpsBoxAcc;
    
    /**
     * @var boolean
     *
     * @ODM\Field(type="boolean", name="gps_box_fix");
     */
    private $gpsBoxFix;
    
    /**
     * @var DateTime
     *
     * @ODM\Field(type="date", name="gps_box_head");
     */
    private $gpsBoxHead;
    
    /**
     * @var string
     *
     * @ODM\Field(type="string", name="gps_box_lat");
     */
    private $gpsBoxLat;
    
    /**
     * @var string
     *
     * @ODM\Field(type="string", name="gps_box_lon");
     */
    private $gpsBoxLon;
    
    /**
     * @var DateTime
     *
     * @ODM\Field(type="date", name="gps_box_spd");
     */
    private $gpsBoxSpd;
    
    /**
     * @var DateTime
     *
     * @ODM\Field(type="date", name="gps_box_ts");
     */
    private $gpsBoxTs;
    
    /**
     * @var string
     *
     * @ODM\Field(type="string", name="gps_data_accuracy");
     */
    private $gpsDataAccuracy;
    
    /**
     * @var DateTime
     *
     * @ODM\Field(type="date", name="gps_data_change_age");
     */
    private $gpsDataChangeAge;
    
    /**
     * @var DateTime
     *
     * @ODM\Field(type="date", name="gps_data_fix_age");
     */
    private $gpsDataFixAge;
    
    /**
     * @var DateTime
     *
     * @ODM\Field(type="date", name="gps_data_satellites");
     */
    private $gpsDataSatellites;
    
    /**
     * @var string
     *
     * @ODM\Field(type="string", name="gps_data_time");
     */
    private $gpsDataTime;
    
    /**
     * @var string
     *
     * @ODM\Field(type="string", name="gps_info");
     */
    private $gpsInfo;
    
    /**
     * @var string
     *
     * @ODM\Field(type="string", name="gsmVer");
     */
    private $gsmVer;
    
    /**
     * @var integer
     *
     * @ODM\Field(type="integer", name="gspeed");
     */
    private $gspeed;
    
    /**
     * @var string
     *
     * @ODM\Field(type="string", name="hwVer");
     */
    private $hwVer;
    
    /**
     * @var integer
     *
     * @ODM\Field(type="integer", name="id_trip");
     */
    private $idTrip;
    
    /**
     * @var float
     *
     * @ODM\Field(type="float", name="int_lat");
     */
    private $intLat;
    
    /**
     * @var float
     *
     * @ODM\Field(type="float", name="int_lon");
     */
    private $intLon;
    
    /**
     * @var float
     *
     * @ODM\Field(type="float", name="int_time");
     */
    private $intTime;
    
    /**
     * @var boolean
     *
     * @ODM\Field(type="boolean", name="keyOn");
     */
    private $keyOn;
    
    /**
     * @return string
     */
    private $lat;
    
    /**
     * @var DateTime
     *
     * @ODM\Field(type="date", name="log_time");
     */
    private $logTime;
    
    /**
     * @var DateTime
     *
     * @ODM\Field(type="date", name="log_tx_time");
     */
    private $logTxTime;
    
    /**
     * @return string
     */
    private $lon;
    
    /**
     * @var DateTime
     *
     * @ODM\Field(type="date", name="offLineTrips");
     */
    private $offLineTrips;
    
    /**
     * @var integer
     *
     * @ODM\Field(type="integer", name="on_trip");
     */
    private $onTrip;
    
    /**
     * @var DateTime
     *
     * @ODM\Field(type="date", name="openTrips");
     */
    private $openTrips;
    
    /**
     * @var boolean
     *
     * @ODM\Field(type="boolean", name="parkEnabled");
     */
    private $parkEnabled;
    
    /**
     * @var boolean
     *
     * @ODM\Field(type="boolean", name="parking");
     */
    private $parking;
    
    /**
     * @var string
     *
     * @ODM\Field(type="string", name="sdkVer");
     */
    private $sdkVer;
    
    /**
     * @var string
     *
     * @ODM\Field(type="string", name="swVer");
     */
    private $swVer;
    
    /**
     * @var integer
     *
     * @ODM\Field(type="integer", name="uptime");
     */
    private $uptime;
    
    /**
     * @var boolean
     *
     * @ODM\Field(type="boolean", name="vcuFault");
     */
    private $vcuFault;
    
    /**
     * @var string
     *
     * @ODM\Field(type="string", name="verisions_AndroidBuild");
     */
    private $verisionsAndroidBuild;
    
    /**
     * @var string
     *
     * @ODM\Field(type="string", name="verisions_AndroidDevice");
     */
    private $verisionsAndroidDevice;
    
    /**
     * @var DateTime
     *
     * @ODM\Field(type="date", name="verisions_AppCode");
     */
    private $verisionsAppCode;
    
    /**
     * @var string
     *
     * @ODM\Field(type="string", name="verisions_AppName");
     */
    private $verisionsAppName;
    
    /**
     * @var string
     *
     * @ODM\Field(type="string", name="verisions_DeviceSN");
     */
    private $verisionsDeviceSN;
    
    /**
     * @var string
     *
     * @ODM\Field(type="string", name="verisions_HbVer");
     */
    private $verisionsHbVer;
    
    /**
     * @var string
     *
     * @ODM\Field(type="string", name="verisions_HbVersion");
     */
    private $verisionsHbVersion;
    
    /**
     * @var string
     *
     * @ODM\Field(type="string", name="verisions_MCU");
     */
    private $verisionsMCU;
    
    /**
     * @var string
     *
     * @ODM\Field(type="string", name="verisions_MCUModel");
     */
    private $verisionsMCUModel;
    
    /**
     * @var string
     *
     * @ODM\Field(type="string", name="verisions_Realease");
     */
    private $verisionsRealease;
    
    /**
     * @var string
     *
     * @ODM\Field(type="string", name="verisions_SDK");
     */
    private $verisionsSdk;
    
    /**
     * @var DateTime
     *
     * @ODM\Field(type="date", name="verisions_Service");
     */
    private $verisionsService;
    
    /**
     * @var string
     *
     * @ODM\Field(type="string", name="verisions_TBoxHw");
     */
    private $verisionsTBoxHw;
    
    /**
     * @var string
     *
     * @ODM\Field(type="string", name="verisions_TBoxSw");
     */
    private $verisionsTBoxSw;
    
    /**
     * @var string
     *
     * @ODM\Field(type="string", name="verisions_VINCode");
     */
    private $verisionsVinCode;
    
    /**
     * @var string
     *
     * @ODM\Field(type="string", name="verisions_VehicleType");
     */
    private $verisionsVehicleType;
    
    /**
     * @var string
     *
     * @ODM\Field(type="string", name="verisions_wlsize");
     */
    private $verisionswlsize;
    
    
    
    

    /**
     * Get id
     *
     * @return integer
     */
    public function getId()
    {
        return $this->id;
    }
    
    /**
     * @return bool
     */
    function getAccStatus() {
        return $this->accStatus;
    }

    /**
     * @return string
     */
    function getAndroidBuild() {
        return $this->androidBuild;
    }

    /**
     * @return string
     */
    function getAndroidDevice() {
        return $this->androidDevice;
    }

    /**
     * @return \DateTime
     */
    function getAppCode() {
        return $this->appCode;
    }

    /**
     * @return string
     */
    function getAppName() {
        return $this->appName;
    }

    /**
     * @return bool
     */
    function getBsmFault() {
        return $this->bsmFault;
    }

    /**
     * @return bool
     */
    function getBrakesOn() {
        return $this->brakesOn;
    }

    /**
     * @return bool
     */
    function getChCommStatus() {
        return $this->chCommStatus;
    }

    /**
     * @return bool
     */
    function getChFault() {
        return $this->chFault;
    }

    /**
     * @return bool
     */
    function getChHeatFault() {
        return $this->chHeatFault;
    }

    /**
     * @return \DateTime
     */
    function getChStatus() {
        return $this->chStatus;
    }

    /**
     * @return bool
     */
    function getChargeCommStatus() {
        return $this->chargeCommStatus;
    }

    /**
     * @return string
     */
    function getDeviceSn() {
        return $this->deviceSn;
    }

    /**
     * @return string
     */
    function getGps() {
        return $this->gps;
    }

    /**
     * @return string
     */
    function getGearStatus() {
        return $this->gearStatus;
    }

    /**
     * @return string
     */
    function getHbVer() {
        return $this->hbVer;
    }

    /**
     * @return string
     */
    function getHwVersion() {
        return $this->hwVersion;
    }

    /**
     * @return \DateTime
     */
    function getImei() {
        return $this->imei;
    }

    /**
     * @return bool
     */
    function getKmStatus() {
        return $this->kmStatus;
    }

    /**
     * @return bool
     */
    function getKsStatus() {
        return $this->ksStatus;
    }

    /**
     * @return string
     */
    function getKeyStatus() {
        return $this->keyStatus;
    }

    /**
     * @return integer
     */
    function getKm() {
        return $this->km;
    }

    /**
     * @return bool
     */
    function getLcdOn() {
        return $this->lcdOn;
    }

    /**
     * @return string
     */
    function getMcu() {
        return $this->mcu;
    }

    /**
     * @return string
     */
    function getMcuModel() {
        return $this->mcuModel;
    }

    /**
     * @return bool
     */
    function getMotFalut() {
        return $this->motFalut;
    }

    /**
     * @return \DateTime
     */
    function getMotT() {
        return $this->motT;
    }

    /**
     * @return bool
     */
    function getMotTempHigh() {
        return $this->motTempHigh;
    }

    /**
     * @return string
     */
    function getMotV() {
        return $this->motV;
    }

    /**
     * @return bool
     */
    function getPpStatus() {
        return $this->ppStatus;
    }

    /**
     * @return \DateTime
     */
    function getPackA() {
        return $this->packA;
    }

    /**
     * @return string
     */
    function getPackStatus() {
        return $this->packStatus;
    }

    /**
     * @return \DateTime
     */
    function getPackV() {
        return $this->packV;
    }

    /**
     * @return bool
     */
    function getPreChFault() {
        return $this->preChFault;
    }

    /**
     * @return bool
     */
    function getReadyOn() {
        return $this->readyOn;
    }

    /**
     * @return string
     */
    function getRelease() {
        return $this->release;
    }
    
    /**
     * @return string
     */
    function getSdk() {
        return $this->sdk;
    }

    /**
     * @return string
     */
    function getSDKVer() {
        return $this->SDKVer;
    }

    /**
     * @return \DateTime
     */
    function getSim_sn() {
        return $this->sim_sn;
    }

    /**
     * @return integer
     */
    function getSoc() {
        return $this->soc;
    }

    /**
     * @return \DateTime
     */
    function getService() {
        return $this->service;
    }

    /**
     * @return integer
     */
    function getSpeed() {
        return $this->speed;
    }

    /**
     * @return string
     */
    function getTBoxHw() {
        return $this->tBoxHw;
    }

    /**
     * @return string
     */
    function getTBoxsw() {
        return $this->tBoxsw;
    }

    /**
     * @return string
     */
    function getVer() {
        return $this->ver;
    }

    /**
     * @return string
     */
    function getVin() {
        return $this->vin;
    }

    /**
     * @return string
     */
    function getVinCode() {
        return $this->vinCode;
    }

    /**
     * @return string
     */
    function getVersions() {
        return $this->versions;
    }

    /**
     * @return \DateTime
     */
    function getWarnIndex() {
        return $this->warnIndex;
    }

    /**
     * @return \DateTime
     */
    function getWarnStatus() {
        return $this->warnStatus;
    }

    /**
     * @return bool
     */
    function getBatterySafety() {
        return $this->batterySafety;
    }

    /**
     * @return bool
     */
    function getCharging() {
        return $this->charging;
    }

    /**
     * @return string
     */
    function getClock() {
        return $this->clock;
    }

    /**
     * @return bool
     */
    function getCloseEnabled() {
        return $this->closeEnabled;
    }

    /**
     * @return integer
     */
    function getCputemp() {
        return $this->cputemp;
    }

    /**
     * @return string
     */
    function getExtLat() {
        return $this->extLat;
    }

    /**
     * @return string
     */
    function getExtLon() {
        return $this->extLon;
    }

    /**
     * @return integer
     */
    function getExtTime() {
        return $this->extTime;
    }

    /**
     * @return string
     */
    function getFwVer() {
        return $this->fwVer;
    }
    
    /**
     * @return string
     */
    function getGpsBoxAcc() {
        return $this->gpsBoxAcc;
    }

    /**
     * @return bool
     */
    function getGpsBoxFix() {
        return $this->gpsBoxFix;
    }

    /**
     * @return \DateTime
     */
    function getGpsBoxHead() {
        return $this->gpsBoxHead;
    }
    
    /**
     * @return string
     */
    function getGpsBoxLat() {
        return $this->gpsBoxLat;
    }

    /**
     * @return string
     */
    function getGpsBoxLon() {
        return $this->gpsBoxLon;
    }

    /**
     * @return bool
     */
    function getGpsBoxSpd() {
        return $this->gpsBoxSpd;
    }

    /**
     * @return \DateTime
     */
    function getGpsBoxTs() {
        return $this->gpsBoxTs;
    }

    /**
     * @return string
     */
    function getGpsDataAccuracy() {
        return $this->gpsDataAccuracy;
    }

    /**
     * @return \DateTime
     */
    function getGpsDataChangeAge() {
        return $this->gpsDataChangeAge;
    }

    /**
     * @return \DateTime
     */
    function getGpsDataFixAge() {
        return $this->gpsDataFixAge;
    }

    /**
     * @return \DateTime
     */
    function getGpsDataSatellites() {
        return $this->gpsDataSatellites;
    }

    /**
     * @return string
     */
    function getGpsDataTime() {
        return $this->gpsDataTime;
    }

    /**
     * @return string
     */
    function getGpsInfo() {
        return $this->gpsInfo;
    }

    /**
     * @return string
     */
    function getGsmVer() {
        return $this->gsmVer;
    }

    /**
     * @return integer
     */
    function getGspeed() {
        return $this->gspeed;
    }

    /**
     * @return string
     */
    function getHwVer() {
        return $this->hwVer;
    }

    /**
     * @return integer
     */
    function getIdTrip() {
        return $this->idTrip;
    }

    /**
     * @return string
     */
    function getIntLat() {
        return $this->intLat;
    }

    /**
     * @return string
     */
    function getIntLon() {
        return $this->intLon;
    }

    /**
     * @return string
     */
    function getIntTime() {
        return $this->intTime;
    }

    /**
     * @return bool
     */
    function getKeyOn() {
        return $this->keyOn;
    }

    /**
     * @return string
     */
    function getLat() {
        return $this->lat;
    }

    /**
     * @return \DateTime
     */
    function getLogTime() {
        return $this->logTime;
    }

    /**
     * @return \DateTime
     */
    function getLogTxTime() {
        return $this->logTxTime;
    }

    /**
     * @return string
     */
    function getLon() {
        return $this->lon;
    }

    /**
     * @return \DateTime
     */
    function getOffLineTrips() {
        return $this->offLineTrips;
    }

    /**
     * @return integer
     */
    function getOnTrip() {
        return $this->onTrip;
    }

    /**
     * @return \DateTime
     */
    function getOpenTrips() {
        return $this->openTrips;
    }

    /**
     * @return bool
     */
    function getParkEnabled() {
        return $this->parkEnabled;
    }

    /**
     * @return bool
     */
    function getParking() {
        return $this->parking;
    }

    /**
     * @return string
     */
    function getSdkVer_2() {
        return $this->sdkVer;
    }

    /**
     * @return string
     */
    function getSwVer() {
        return $this->swVer;
    }

    /**
     * @return integer
     */
    function getUptime() {
        return $this->uptime;
    }

    /**
     * @return bool
     */
    function getVcuFault() {
        return $this->vcuFault;
    }

    /**
     * @return string
     */
    function getVerisionsAndroidBuild() {
        return $this->verisionsAndroidBuild;
    }

    /**
     * @return string
     */
    function getVerisionsAndroidDevice() {
        return $this->verisionsAndroidDevice;
    }

    /**
     * @return \DateTime
     */
    function getVerisionsAppCode() {
        return $this->verisionsAppCode;
    }

    /**
     * @return string
     */
    function getVerisionsAppName() {
        return $this->verisionsAppName;
    }

    /**
     * @return string
     */
    function getVerisionsDeviceSN() {
        return $this->verisionsDeviceSN;
    }

    /**
     * @return string
     */
    function getVerisionsHbVer() {
        return $this->verisionsHbVer;
    }

    /**
     * @return string
     */
    function getVerisionsHbVersion() {
        return $this->verisionsHbVersion;
    }

    /**
     * @return string
     */
    function getVerisionsMCU() {
        return $this->verisionsMCU;
    }

    /**
     * @return string
     */
    function getVerisionsMCUModel() {
        return $this->verisionsMCUModel;
    }

    /**
     * @return string
     */
    function getVerisionsRealease() {
        return $this->verisionsRealease;
    }

    /**
     * @return string
     */
    function getVerisionsSdk() {
        return $this->verisionsSdk;
    }

    /**
     * @return \DateTime
     */
    function getVerisionsService() {
        return $this->verisionsService;
    }

    /**
     * @return string
     */
    function getVerisionsTBoxHw() {
        return $this->verisionsTBoxHw;
    }

    /**
     * @return string
     */
    function getVerisionsTBoxSw() {
        return $this->verisionsTBoxSw;
    }

    /**
     * @return string
     */
    function getVerisionsVinCode() {
        return $this->verisionsVinCode;
    }

    /**
     * @return string
     */
    function getVerisionsVehicleType() {
        return $this->verisionsVehicleType;
    }

    /**
     * @return string
     */
    function getVerisionswlsize() {
        return $this->verisionswlsize;
    }

    
}