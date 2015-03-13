<?php

namespace IconicsInvoicing\DependencyInjection;

use IconicsInvoicing\Environments\EnvironmentAwareInterface;
use Pimple\Container;

abstract class AbstractDependencyContainer extends Container
{
    /**
     * Extending Pimple::offsetGet in order to set the environment.
     */
    public function offsetGet($id)
    {
        $service = parent::offsetGet($id);

        return $this->injectOptionalDependencies($service);
    }

    /**
     * Will inject optional dependencies into services.
     *
     * @param mixed $service
     *
     * @return mixed
     */
    protected function injectOptionalDependencies($service)
    {
        if ($this->offsetExists('Environment') && $service instanceof EnvironmentAwareInterface) {
            $service->setEnvironment($this['Environment']);
        }

        return $service;
    }
}
