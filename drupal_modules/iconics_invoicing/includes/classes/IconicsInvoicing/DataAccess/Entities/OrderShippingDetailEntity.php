<?php
namespace IconicsInvoicing\DataAccess\Entities;

class OrderShippingDetailEntity extends AbstractEntity
{

    protected $carrier;
    protected $trackingNumber;

    public function getCarrier()
    {
        return $this->carrier;
    }

    public function getTrackingNumber()
    {
        return $this->trackingNumber;
    }

}