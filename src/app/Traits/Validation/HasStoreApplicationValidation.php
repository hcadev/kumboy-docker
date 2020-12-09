<?php
namespace App\Traits\Validation;

trait HasStoreApplicationValidation
{
    private $storeApplicationRules = [
        'name' => 'required|store_application',
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
            ? $this->storeApplicationRules
            : array_intersect_key($this->storeApplicationRules, array_flip($fields));
    }
}