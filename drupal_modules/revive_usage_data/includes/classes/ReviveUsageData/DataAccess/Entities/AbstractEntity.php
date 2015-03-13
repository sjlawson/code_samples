<?php

namespace ReviveUsageData\DataAccess\Entities;

use ReflectionClass;

/**
 * Abstract base class for database entities.
 *
 * The Entity class together with Connection and Table classes implement a very
 * simple ORM (Object-Relational Mapper).  This ORM pattern offers benefits of
 * code reuse and code simplification.  However, this is a simple implementation
 * and is not scalable.  Use this pattern judiciously!  Hydrating 10k records
 * will be insanely slow and you will likely run out of memory.  It is
 * recommended that you do not hydrate more than approximately 100 records at once.
 *
 * @author Samuel Lawson <slawson@mooreheadcomm.com>
 * @date 2014-05-06
 */
abstract class AbstractEntity implements EntityInterface
{
    /**
     * Will import data from an array.
     *
     * This function implements basic hydration from an associative array of
     * field-name:values.  Caution: this will overwrite current data.
     * This function is typically only used in Entity constructors.
     *
     * Using this function, an entity can be easily hydrated from a SELECT *
     * statement followed by PDO::FETCH_ASSOC fetch type.
     *
     * @param array $data An associative array of field-name:values.
     */
    public function fromArray(array $data)
    {
        $selfReflection = new ReflectionClass($this);
        $properties = $selfReflection->getProperties();

        foreach ($properties as $property) {
            $property->setAccessible(true);
            if (isset($data[$property->getName()])) {
                $property->setValue($this, $data[$property->getName()]);
            }
        }
    }

    /**
     * Will export entity parameters to an array.
     *
     * @param array $fields An optional array of the fields to output.
     *
     * @return array
     */
    public function toArray(array $fields = array())
    {
        // If no fields given, assume all fields desired.
        if (empty($fields)) {
            $fields = $this->getAllFieldNamesArray();
        }

        $selfReflection = new ReflectionClass($this);
        $properties = $selfReflection->getProperties();

        $propertyArray = array();
        foreach ($properties as $property) {
            $property->setAccessible(true);
            $docComment = $property->getDocComment();
            if (preg_match("/\@field (\w+)/", $docComment, $matches) === 1) {
                if (isset($matches[1]) && array_search($matches[1], $fields) !== false) {
                    $propertyArray[$matches[1]] = $property->getValue($this);
                }
            }
        }

        return $propertyArray;
    }

    /**
     * Helper function to get an array of all fields names.
     *
     * @return array
     */
    public function getAllFieldNamesArray()
    {
        $selfReflection = new ReflectionClass($this);
        $properties = $selfReflection->getProperties();

        $fieldArray = array();
        foreach ($properties as $property) {
            $property->setAccessible(true);
            $docComment = $property->getDocComment();
            if (preg_match("/\@field (\w+)/", $docComment, $matches) === 1) {
                if (isset($matches[1])) {
                    $fieldArray[] = $property->getName();
                }
            }
        }

        return $fieldArray;
    }

    /**
     * Helper function to get an array of all fields names with non-null values.
     *
     * @return array
     */
    public function getNonNullFieldNamesArray()
    {
        $selfReflection = new ReflectionClass($this);
        $properties = $selfReflection->getProperties();

        $fieldArray = array();
        foreach ($properties as $property) {
            $property->setAccessible(true);
            $docComment = $property->getDocComment();
            if (preg_match("/\@field (\w+)/", $docComment, $matches) === 1) {
                if (isset($matches[1])) {
                    $value = $property->getValue($this);
                    if ($value !== null) {
                        $fieldArray[] = $property->getName();
                    }
                }
            }
        }

        return $fieldArray;
    }

    /**
     * Helper function to get an array of all fields names with either non-null
     * values or the @nullable annotation.
     *
     * @return array
     */
    public function getNonNullAndNullableFieldNamesArray()
    {
        $selfReflection = new ReflectionClass($this);
        $properties = $selfReflection->getProperties();

        $fieldArray = array();
        foreach ($properties as $property) {
            $property->setAccessible(true);
            $docComment = $property->getDocComment();
            if (preg_match("/\@field (\w+)/", $docComment, $matches) === 1) {
                if (isset($matches[1])) {
                    $nullable = false;
                    if (preg_match("/\@nullable/", $docComment) === 1) {
                        $nullable = true;
                    }

                    $value = $property->getValue($this);
                    if ($value !== null) {
                        $fieldArray[] = $property->getName();
                    } elseif ($nullable) {
                        $fieldArray[] = $property->getName();
                    }
                }
            }
        }

        return $fieldArray;
    }

    /**
     * Helper function to get an array of all primary key fields names.
     *
     * Primary key fields have the "@key primary" annotation.
     *
     * @return array
     */
    public function getPrimaryKeyFieldNamesArray()
    {
        $selfReflection = new ReflectionClass($this);
        $properties = $selfReflection->getProperties();

        $fieldArray = array();
        foreach ($properties as $property) {
            $property->setAccessible(true);
            $docComment = $property->getDocComment();
            if (preg_match("/\@key (\w+)/", $docComment, $matches) === 1) {
                if (isset($matches[1]) && $matches[1] == 'primary') {
                    $fieldArray[] = $property->getName();
                }
            }
        }

        return $fieldArray;
    }
}
