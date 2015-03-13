<?php

namespace IconicsInvoicing\DataAccess\Entities;

use ReflectionClass;

/**
 * Abstract base class for database entities.
 *
 * This entity base class just supplies functionality to hydrate new entities.
 * This is a simple implementation and is not scalable.  Use these entities
 * judiciously!  Hydrating 10k records will be insanely slow and you will
 * likely run out of memory.  It is recommended that you do not hydrate more
 * than approximately 100 records at once.
 *
 * @date 2014-12-15
 * @author Samuel Lawson <slawson@mooreheadcomm.com>
 */
abstract class AbstractEntity
{
    /**
     * Will construct an entity and hydrate the values as possible.
     *
     * Using this function, an entity can be easily created and hydrated
     * from a SELECT * statement followed by PDO::FETCH_ASSOC fetch type.
     *
     * Note: This function will not call the entity constructor.  The entity
     * constructor should be used for creating entities not in the database.
     *
     * @param array $data An associative array of field-name:values.
     */
    public static function create(array $data = array())
    {
        // Hack from Doctrine 1 to instantiate a class without calling
        // the constructor.  PHP 5.4 has a nice reflection method to do
        // this, but we are currently on 5.3.10 or so.
        $serialized = sprintf(
            'O:%u:"%s":0:{}',
            strlen(get_called_class()),
            get_called_class()
        );
        $object = unserialize($serialized);
        $object->hydrate($data);

        return $object;
    }

    /**
     * Will import data from an array.
     *
     * A field named "time_period_bonuses_id" will hydrate into a member
     * variable named "time_period_bonuses_id" or "timePeriodBonusesId".
     *
     * Caution: This will overwrite current data.
     *
     * Using this function, an entity can be easily hydrated from a SELECT *
     * statement followed by PDO::FETCH_ASSOC fetch type.
     *
     * @param array $data An associative array of field-name:values.
     */
    public function hydrate(array $data = array())
    {
        $selfReflection = new ReflectionClass($this);
        $properties = $selfReflection->getProperties();
        foreach ($properties as $property) {
            $property->setAccessible(true);
            $neutralPropertyName = strtolower($property->getName());
            $underscorePropertyName = strtolower(preg_replace('/([a-z])([A-Z])/', '$1_$2', $property->getName()));
            foreach ($data as $key => $value) {
                if ($neutralPropertyName == strtolower($key) ||
                    $underscorePropertyName == strtolower($key)) {
                    $property->setValue($this, $value);
                    break;
                }
            }
        }
    }

    public function toArray()
    {
        return get_object_vars($this);
    }

}
