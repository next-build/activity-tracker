<?php

namespace NextBuild\ActivityTracker\Storage;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use NextBuild\ActivityTracker\Storage\VisitorIpModel;

class VisitorRequestModel extends Model
{
    use HasFactory;

    protected $table = 'activity_tracker_visitor_requests';

    protected $fillable = [
        'uuid',
        'visitor_ip_uuid',
        'type',
        'content',
        'created_at',
    ];

    protected $casts = [
        'content' => 'json',
        'created_at' => 'datetime',
    ];

    public $timestamps = false;

    protected $primaryKey = 'uuid';

    public $incrementing = false;

    public function visitorIp()
    {
        return $this->belongsTo(VisitorIpModel::class, 'visitor_ip_uuid', 'uuid');
    }
}
