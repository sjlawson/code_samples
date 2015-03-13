<?php

namespace MHCCcrsManager\DataAccess\Entities;

interface EntityInterface
{
    public function fromArray(array $data);
    public function toArray(array $fields = array());
}
