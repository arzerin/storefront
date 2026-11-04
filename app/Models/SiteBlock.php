<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SiteBlock extends Model
{
    public const REGIONS = [
        'home_benefits' => 'Homepage benefits strip',
        'home_before_collections' => 'Homepage before collections',
        'home_after_collections' => 'Homepage after collections',
        'home_after_products' => 'Homepage after products',
        'footer' => 'Footer above copyright',
    ];

    protected $fillable = ['title', 'body', 'region', 'sort_order', 'is_enabled', 'media_asset_id'];
    public function mediaAsset(): \Illuminate\Database\Eloquent\Relations\BelongsTo { return $this->belongsTo(MediaAsset::class); }
    protected $casts = ['is_enabled' => 'boolean', 'sort_order' => 'integer'];
}
