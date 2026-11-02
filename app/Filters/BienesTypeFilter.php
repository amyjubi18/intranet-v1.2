<?php

namespace App\Filters;

use Illuminate\Database\Eloquent\Builder;
use LaravelViews\Filters\BooleanFilter;

class BienesTypeFilter extends BooleanFilter
{
    /**
     * Modify the current query when the filter is used
     *
     * @param Builder $query Current query
     * @param Array $value Associative array with the boolean value for each of the options
     * @return Builder Query modified
     */
    public function apply(Builder $query, $value, $request)
    {
        if($value ['sede']){
        $query->where('sede', true);
        }
        if($value ['unidad_administrativas->name']){
            $query->where('name', true);
            }

            return $query;
    }

    /**
     * Defines the title and value for each option
     *
     * @return Array associative array with the title and values
     */
    public function options(): Array
    {
        return [
            'Sede' => 'sede',
            'Unidad Administrativa' => 'unidad_administrativas->name'
        ];
    }
}
