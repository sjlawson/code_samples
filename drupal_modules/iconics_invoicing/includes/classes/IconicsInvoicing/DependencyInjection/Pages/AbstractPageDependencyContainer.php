<?php

namespace IconicsInvoicing\DependencyInjection\Pages;

use IconicsInvoicing\DataAccess\Repositories;
use IconicsInvoicing\DependencyInjection\AbstractDependencyContainer;
use IconicsInvoicing\Environments\EnvironmentFactory;
use IconicsInvoicing\Environments\Environments;

abstract class AbstractPageDependencyContainer extends AbstractDependencyContainer
{
    /**
     * Constructor
     */
    public function __construct()
    {
        parent::__construct();

        // Parameters
        $this['BaseUrl']         = \IconicsInvoicing\get_base_url();
        $this['Host']            = \IconicsInvoicing\get_host_name();
        $this['QueryParameters'] = \IconicsInvoicing\get_query_parameters();
        $this['Username']        = \IconicsInvoicing\get_username();

        // Environment
        $environmentName = \IconicsInvoicing\get_environment_name();
        if (!Environments::isEnvironmentNameValid($environmentName)) {
            throw new \InvalidArgumentException('Invalid environment name: ' . $environmentName);
        }

        $this['Environment.Name'] = $environmentName;
        $this['Environment'] = function ($container) {
            return EnvironmentFactory::createDependencyContainer(
                $container['Host'],
                $container['Environment.Name']
            );
        };

    }
}