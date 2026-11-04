<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class SitePage extends Model
{
    protected $fillable = ['title', 'slug', 'template', 'is_home', 'draft_sections', 'published_revision_id'];
    protected $casts = ['is_home' => 'boolean', 'draft_sections' => 'array'];

    public function revisions(): HasMany { return $this->hasMany(SitePageRevision::class); }
    public function publishedRevision(): BelongsTo { return $this->belongsTo(SitePageRevision::class, 'published_revision_id'); }

    public function publishedSections(): array
    {
        return $this->publishedRevision?->sections ?? [];
    }
}
