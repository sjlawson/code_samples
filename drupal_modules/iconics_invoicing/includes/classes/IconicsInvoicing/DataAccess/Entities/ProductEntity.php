<?php

namespace IconicsInvoicing\DataAccess\Entities;

class ProductEntity extends AbstractEntity
{
    private $itemCode;
    private $itemNumber;
    private $unitPrice;

    public function getItemCode()
    {
        return $this->itemCode;
    }

    public function getItemNumber()
    {
        return $this->itemNumber;
    }

    public function getUnitPrice()
    {
        return $this->unitPrice;
    }

}