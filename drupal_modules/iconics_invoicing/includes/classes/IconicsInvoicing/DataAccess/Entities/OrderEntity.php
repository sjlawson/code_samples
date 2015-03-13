<?php

namespace IconicsInvoicing\DataAccess\Entities;

class OrderEntity extends AbstractEntity
{
    protected $approvalNum;
    protected $orderNum;
    protected $itemsOrdered;
    protected $itemsShipped;
    protected $verizonIconicLocationCode;

    /*
    public function calculateItemsOrdered()
    {

    }

    public function calculateItemsShipped()
    {

    }
    */

    public function getApprovalNum()
    {
        return $this->approvalNum;
    }

    public function getOrderNum()
    {
        return $this->orderNum;
    }

    public function getItemsOrdered()
    {
        return $this->itemsOrdered;
    }

    public function getIemsShipped()
    {
        return $this->itemsShipped;
    }

    public function getVerizonIconicLocationCode()
    {
        return $this->verizonIconicLocationCode;
    }

}
