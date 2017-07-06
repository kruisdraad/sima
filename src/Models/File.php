<?php
namespace Sima\Console\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Log;

class File extends Model
{

    protected $table = 'files';

    protected $fillable = [
        'parent_id',
        'first_seen',
        'last_seen',
        'name',
        'extension',
        'hash',
        'mime_extension',
        'mime_detected',
        'size',
        'whitelisted',
        'blacklisted',
        'count',
    ];

}
