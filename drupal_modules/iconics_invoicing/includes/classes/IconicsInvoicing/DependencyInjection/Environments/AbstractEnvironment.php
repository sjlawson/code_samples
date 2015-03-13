<?php

namespace IconicsInvoicing\DependencyInjection\Environments;

use IconicsInvoicing\DependencyInjection\AbstractDependencyContainer;

abstract class AbstractEnvironment extends AbstractDependencyContainer
{
    /**
     * Constructor
     */
    public function __construct($environmentName, $environmentDomain,
        $drupalConnName, $drupalConnTarget,
        $mssqlConnName, $mssqlConnTarget
    )
    {
        parent::__construct();

        $this['Drupal.Connection.Name']   = $drupalConnName;
        $this['Drupal.Connection.Target'] = $drupalConnTarget;
        $this['Environment.Domain']       = $environmentDomain;
        $this['Environment.Name']         = $environmentName;
        $this['MSSQL.Connection.Name']    = $mssqlConnName;
        $this['MSSQL.Connection.Target']  = $mssqlConnTarget;
    }
}
