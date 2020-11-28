<?php
namespace App\Traits\Validation;

trait HasStoreApplicationValidation
{
    private $storeRules = [
        'name' => 'required|unique:stores|store_application',
        'contact_number' => 'required|contact_number',
        'address' => 'required',
        'map_coordinates' => 'required',
        'map_address' => 'required',
        'open_until' => 'required|date|after:today',
        'attachment' => 'required|file|mimetypes:application/pdf'
    ];

    public function getStoreApplicationRules(array $fields = [])
    {
        return empty($fields)
            ? $this->storeRules
            : array_intersect_key($this->storeRules, array_flip($fields));
    }
}