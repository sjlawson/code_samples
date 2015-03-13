<?php

namespace IconicsInvoicing\DataAccess\Entities;

class OrderLineEntity extends AbstractEntity
{
    protected $dfillDate;
    protected $itemEsn;
    protected $itemLine;
    // protected $itemCode;

    public function getDfillDate()
    {
        return $this->dfillDate;
    }

    public function getItemEsn()
    {
        return $this->itemEsn;
    }

    public function getItemLine()
    {
        return $this->itemLine;
    }

    /* public function getItemCode() */
    /* { */
    /*     return $this->itemCode; */
    /* } */

}