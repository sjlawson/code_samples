<?php
namespace IconicsInvoicing\DataAccess\Entities;

class CustomerEntity extends AbstractEntity
{
    protected $storeNumber; // customerID / customerNumber
    protected $locationCode; // only orders have this
    protected $name;
    protected $address;
    protected $city;
    protected $state;
    protected $zipcode;

    // CSTPONMB is DF+mhcOrdernumb

    public function getStoreNumber()
    {
        return $this->storeNumber;
    }

    public function getLocationCode()
    {
        return $this->locationCode;
    }

    public function getName()
    {
        return $this->name;
    }

    public function getAddress()
    {
        return $this->address;
    }

    public function getCity()
    {
        return $this->city;
    }

    public function getState()
    {
        return $this->state;
    }

    public function getZipcode()
    {
        return $this->zipcode;
    }

}