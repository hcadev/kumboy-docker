<?php
namespace App\Traits\Validation;

trait HasStoreTransferValidation
{
    private $storeTransferRules = [
        'email' => 'required|email|exists:users',
        'attachment' => 'required|file|mimetypes:application/pdf'
    ];

    public function getStoreTransferRules(array $fields = [])
    {
        return empty($fields)
            ? $this->storeTransferRules
            : array_intersect_key($this->storeTransferRules, array_flip($fields));
    }
}