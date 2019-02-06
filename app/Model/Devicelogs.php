<?php

namespace App\Model;

use Illuminate\Database\Eloquent\Model;

class Devicelogs extends Model
{
    protected $fillable = [
        'DeviceLogId','DownloadDate','DeviceId','UserId','LogDate'
    ];
    public function reviews(){
        return $this->hasMany(Review::class);
    }
}
