<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Category_product extends Model
{
protected $table = 'category_product'; 

    /**
     * get_category_product
     *
     * @return Illuminate\Database\Eloquent\Builder
     */
    public function get_category_product()
    {
        $sql = $this->select("*");
        
        return $sql;
    }
}
