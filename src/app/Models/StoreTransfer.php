<?php

namespace App\Models;

use App\Traits\Model\WithoutTimestamps;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class StoreTransfer extends Model
{
    use HasFactory, WithoutTimestamps;

    protected $table = 'store_transfer_requests';

    protected $fillable = [
        'request_code',
        'uuid',
        'target_uuid',
        'attachment',
    ];

    public function storeRequest()
    {
        return $this->belongsTo(StoreRequest::class);
    }
}
