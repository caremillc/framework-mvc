<?php 
namespace Careminate\Http\Controllers;

use Careminate\Http\Validations\Validate;

class AbstractController
{
     
    /**
     * @param array|object $requests
     * @param array $rules
     * @param array|null $attributes
     * 
     * @return Validate
     */
    public function validate(array|object $requests, array $rules, array|null $attributes = []){
        return Validate::make($requests, $rules, $attributes);
    }
    
}
