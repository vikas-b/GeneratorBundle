<?php

namespace Admingenerator\GeneratorBundle\QueryFilter;

interface QueryFilterInterface
{
    /**
     * @param string $field Field name
     *
     * @api
     */
    public function getParamName($field);
    
    /**
     * @return the query object interface depend of the ORM
     *
     * @api
     */
    public function getQuery();

    /**
     * Add filter for Default db types (types, not found
     * by others add*Filter() methods
     *
     * @param string $field the db field
     * @param string $value the search value
     *
     * @api
     * @return void
     */
    public function addDefaultFilter($field, $value);

}
