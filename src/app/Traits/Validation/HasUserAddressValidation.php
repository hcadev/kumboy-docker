<?php
namespace App\Traits\Validation;

trait HasUserAddressValidation
{
    private $userAddressRules = [
        'label' => 'required',
        'contact_person' => 'required',
        'contact_number' => 'required|contact_number',
        'address' => 'required',
        'map_coordinates' => 'required',
        'map_address' => 'required',
    ];

    public function getUserAddressRules(array $fields = [])
    {
        return empty($fields)
            ? $this->userAddressRules
            : array_intersect_key($this->userAddressRules, array_flip($fields));
    }
}