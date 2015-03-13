<?php

namespace IconicsInvoicing\DataAccess\Entities;

class InvoiceEntity extends AbstractEntity
{
    /* SOP10100 */

    protected $batchID; //BACHNUMB
    protected $invoiceDate; // INVODATE
    protected $invoiceNotes; // ?
    protected $orderNumber; // SOPNUMBE

    protected $siteID; // LOCNCODE 0201

    public function getInvoiceNumber()
    {
        return $this->orderNumber;
    }

    public function getInvoiceDate()
    {
        return $this->invoiceDate;
    }

    public function getBatchID()
    {
        return $this->batchID;
    }

    public function getInvoiceNotes()
    {
        return $this->invoiceNotes;
    }

    public function getSiteID()
    {
        return $this->siteID;
    }
}