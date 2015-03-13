<?php

namespace IconicsInvoicing\DependencyInjection\Pages;

use IconicsInvoicing\DataAccess\Repositories;
use IconicsInvoicing\Presenters\Pages as PresenterPages;

/**
 * Dependency injection container for "Iconic Dynamics Invoicing".
 *
 * @author Samuel Lawson <slawson@mooreheadcomm.com>
 * @date 2014-12-15
 */
class IconicDynamicsInvoicingDependencyContainer extends AbstractPageDependencyContainer
{
    /**
     * Constructor
     */
    public function __construct()
    {
        parent::__construct();

        //Repositories
        $this['Repository.OrderRepository'] = function ($container) {
            return new Repositories\OrderRepository();
        };

        $this['Repository.InvoiceRepository'] = function ($container) {
            return new Repositories\InvoiceRepository();
        };


        // Presenter
        $this['IconicDynamicsInvoicingPresenter'] = function ($container) {
            return new PresenterPages\IconicDynamicsInvoicingPresenter(
                $container['Repository.OrderRepository'],
                $container['Repository.InvoiceRepository']
            );
        };
    }
}
