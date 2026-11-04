<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SitePageRevision extends Model
{
    protected $fillable = ['site_page_id', 'title', 'template', 'sections', 'published_at'];
    protected $casts = ['sections' => 'array', 'published_at' => 'datetime'];
}
