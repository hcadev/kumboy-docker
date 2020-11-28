<?php

namespace App\Models;

use App\Traits\Model\WithoutTimestamps;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class StoreApplication extends Model
{
    use HasFactory, WithoutTimestamps;

    protected $table = 'user_request_store_applications';

    protected $fillable = [
        'user_request_code',
        'uuid',
        'name',
        'contact_number',
        'address',
        'map_coordinates',
        'map_address',
        'open_until',
        'attachment',
    ];

    public function userRequest()
    {
        return $this->belongsTo(UserRequest::class);
    }
}
