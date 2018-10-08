<?php

namespace SharengoCore\Service;

use SharengoCore\Entity\ServerScripts;
use SharengoCore\Entity\Repository\ServerScriptsRepository;

use Doctrine\ORM\EntityManager;

class ServerScriptsService
{
    /**
     * @var EntityManager
     */
    private $entityManager;
    
    /**
     * @var ServerScriptsRepository
     */
    private $serverScriptsRepository;

    /**
     * @param EntityManager $entityManager
     * @param ServerScriptsService $serverScriptsService
     */
    public function __construct(EntityManager $entityManager)
    {
        $this->entityManager = $entityManager;
        $this->serverScriptsRepository = $this->entityManager->getRepository('\SharengoCore\Entity\ServerScripts');
    }
    
    public function writeEndServerScript(ServerScripts $serverScript) {
        $serverScript->setEndTs(new \DateTime());
        $serverScript->setNote(null);
        $this->writeRow($serverScript);
    }
    
    public function writeStartServerScript(ServerScripts $serverScript){
        $serverScript->setStartTs(new \DateTime());
        $serverScript->setNote("RUNNING");
        $this->writeRow($serverScript);
    }
    
    public function writeRow(ServerScripts $serverScript) {
        $this->entityManager->persist($serverScript);
        $this->entityManager->flush();
    }
    
    public function getOldServerScript($dateStart) {
        $dateEnd = new \DateTime($dateStart);
        $dateEnd = $dateEnd->modify("+1 day");
        $dateEnd = $dateEnd->format("Y-m-d");
        return $this->serverScriptsRepository->getOldServerScript($dateStart, $dateEnd);
    }

    public function isOpen($name, $fullPath = null, $period = null) {
        return $this->serverScriptsRepository->isOpen($name, $fullPath, $period);
    }

    /**
     * @param $name
     * @param string $fullPath
     * @param array $param
     * @param string $infoScript
     * @param string $error
     * @param string $note
     * @return ServerScripts
     */
    public function open ($name, $fullPath = null, $param = null, $infoScript = null, $error = null, $note = null) {
        $serverScript = new ServerScripts();

        try {

            $serverScript->setName($name);
            $serverScript->setFullPath($fullPath);
            $serverScript->setParam($param);
            $serverScript->setInfoScript($infoScript);
            $serverScript->setError($error);
            $serverScript->setNote($note);

            $this->entityManager->persist($serverScript);
            $this->entityManager->flush();

        } catch(\Exception $e) {

        }
        return $serverScript;

    }

    /**
     * @param ServerScripts $serverScript
     * @param null $param
     * @param null $infoScript
     * @param null $error
     * @param null $note
     * @return ServerScripts
     */
    public function close (ServerScripts $serverScript, $param = null, $infoScript = null, $error = null, $note = null) {

        try {
            $serverScript->setEndTs(date_create());

            if(!is_null($param)) {
                $serverScript->setParam($param);
            }

            if(!is_null($infoScript)) {
                $serverScript->setInfoScript($infoScript);
            }

            if(!is_null($error)) {
                $serverScript->setError($error);
            }

            if(!is_null($note)) {
                $serverScript->setNote($note);
            }

            $param = $serverScript->getParam();
            unset($param["lock_entity"]);
            unset($param["lock_id"]);
            $serverScript->setParam($param);

            $this->entityManager->merge($serverScript);
            $this->entityManager->flush();

        } catch (\Exception $e) {

        }
        return $serverScript;
    }

    /**
     * Set a pair name/value of Json param
     *
     * @param ServerScripts $serverScript
     * @param $name
     * @param $value
     * @return ServerScripts
     * @throws \Doctrine\ORM\OptimisticLockException
     */
    public function setParam(ServerScripts $serverScript, $name, $value) {
        $param = $serverScript->getParam();

        $param[$name] = $value;
        $param["last_update"] = date_create()->format("Y-m-d H:i:s");
        $serverScript->setParam($param);

        $this->entityManager->persist($serverScript);
        $this->entityManager->flush();
        return $serverScript;
    }

    /**
     * Set the parm field width the id of entity (TripPayments or ExtraPayments) thah must be locked
     * @param ServerScripts $serverScript
     * @param $arrayOfPayments
     * @return ServerScripts
     * @throws \Doctrine\ORM\OptimisticLockException
     */
    public function setParamByArray(ServerScripts $serverScript, $arrayOfPayments) {
        $name="";
        $lockArray = array();

        if(count($arrayOfPayments)== 0) {
            return $serverScript;
        }

        $lockEnrity = get_class($arrayOfPayments[0]);

        foreach ($arrayOfPayments as $payment) {
            if ($lockEnrity === "SharengoCore\Entity\TripPayments"){
                $lockEnrity = "SharengoCore\Entity\Trips";
                array_push($lockArray, $payment->getTrip()->getId());
            } else if ($lockEnrity === "SharengoCore\Entity\ExtraPayments" ){
                array_push($lockArray, $payment->getId());
            }
        }

        if(count($lockArray) > 0) {
            $param = $serverScript->getParam();
            $param["lock_entity"] = $lockEnrity;
            $param["lock_id"] = $lockArray;
            $param["last_update"] = date_create()->format("Y-m-d H:i:s");
            $serverScript->setParam($param);
            $this->entityManager->persist($serverScript);
            $this->entityManager->flush();
        }

        return $serverScript;
    }

    /**
     * Remune a key inside of param.
     *
     * @param ServerScripts $serverScript
     * @param string $name
     * @return ServerScripts
     * @throws \Doctrine\ORM\OptimisticLockException
     */
    public function removeParam(ServerScripts $serverScript, $name) {
        $param = $serverScript->getParam();

        unset($param[$name]);
        $serverScript->setParam($param);

        $this->entityManager->persist($serverScript);
        $this->entityManager->flush();
        return $serverScript;
    }

    /**
     * Remove the lock key from params
     *
     * @param ServerScripts $serverScript
     * @param $name
     * @return ServerScripts
     * @throws \Doctrine\ORM\OptimisticLockException
     */
    public function removeParamLock(ServerScripts $serverScript, $name) {
        $param = $serverScript->getParam();

        unset($param["lock_entity"]);
        unset($param["lock_id"]);
        $param["last_update"] = date_create()->format("Y-m-d H:i:s");
        $serverScript->setParam($param);

        $this->entityManager->persist($serverScript);
        $this->entityManager->flush();
        return $serverScript;
    }

    /**
     * @param string $name
     * @param string $fullPath
     * @return array
     */
    public function findOpen($name = null, $fullPath = null) {
        return $this->serverScriptsRepository->findOpen($name, $fullPath);
    }

    /**
     * Check if there is a entity (Trips o ExtraPayments) lock from ascript batch.
     *
     * @param $entity
     * @return bool
     */
    public function isLock($entity) {
        return $this->serverScriptsRepository->isLock($entity);
    }
}
