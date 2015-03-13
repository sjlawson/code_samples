<?php

namespace IconicsInvoicing\DataAccess\Entities;

class InvoiceLineEntity extends AbstractEntity
{
    protected $invoiceNumber; // SOPNUMBE
    protected $itemNumber; // ITEMNMBR
    protected $extendedPrice; // XTNDPRCE
    protected $unitPrice; // UNITPRCE
    protected $itemQuantity; // QUANTITY

    public function getInvoiceNumber()
    {
        return $this->invoiceNumber;
    }

    public function getItemNumber()
    {
        return $this->itemNumber;
    }

    public function getExtendedPrice()
    {
        return $this->extendedPrice;
    }

    public function getUnitPrice()
    {
        return $this->unitPrice;
    }

    public function getItemQuantity()
    {
        return $this->itemQuantity;
    }

}