<?php

namespace NextBuild\ActivityTracker\Storage;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use NextBuild\ActivityTracker\Storage\VisitorRequestModel;

class VisitorIpModel extends Model
{
    use HasFactory;

    protected $table = 'activity_tracker_visitor_ips';

    protected $fillable = [
        'uuid',
        'ip_address',
        'timezone',
        'country_code',
        'content',
    ];

    protected $casts = [
        'content' => 'json',
    ];

    public $timestamps = false;

    protected $primaryKey = 'uuid';

    public $incrementing = false;

    public function requests()
    {
        return $this->hasMany(VisitorRequestModel::class, 'visitor_ip_uuid', 'uuid');
    }
}
