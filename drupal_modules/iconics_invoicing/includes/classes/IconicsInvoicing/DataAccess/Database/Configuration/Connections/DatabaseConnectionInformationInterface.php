<?php

namespace IconicsInvoicing\DataAccess\Database\Configuration\Connections;

interface DatabaseConnectionInformationInterface
{
    public function getDsn();
    public function getOptions();
    public function getPassword();
    public function getUsername();
}
