<?php

namespace SharengoCore\Entity;

use ScnSocialAuth\Entity\UserProviderInterface;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity
 * @ORM\Table(name="customer_providers")
 */
class CustomerProvider implements UserProviderInterface
{
    /**
     * @ORM\Id
     * @ORM\Column(type="integer",name="customer_id")
     */
    protected $customerId;

    /**
     * @ORM\Id
     * @ORM\Column(type="string",length=50, name="provider_id")
     */
    protected $providerId;

    /**
     * @ORM\Column(type="string")
     */
    protected $provider;

    /**
     * @return the $userId
     */
    public function getUserId()
    {
        return $this->customerId;
    }

    /**
     * @param  integer      $customerId
     * @return UserProvider
     */
    public function setUserId($customerId)
    {
        $this->customerId = $customerId;

        return $this;
    }

    /**
     * @return the $providerId
     */
    public function getProviderId()
    {
        return $this->providerId;
    }

    /**
     * @param  integer      $providerId
     * @return UserProvider
     */
    public function setProviderId($providerId)
    {
        $this->providerId = $providerId;

        return $this;
    }

    /**
     * @return the $provider
     */
    public function getProvider()
    {
        return $this->provider;
    }

    /**
     * @param  string       $provider
     * @return UserProvider
     */
    public function setProvider($provider)
    {
        $this->provider = $provider;

        return $this;
    }
}
