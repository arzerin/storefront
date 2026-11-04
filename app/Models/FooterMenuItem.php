<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class FooterMenuItem extends Model
{
    public const SECTIONS = ['explore' => 'Explore', 'account' => 'Account', 'help' => 'Help'];
    public const AUDIENCES = ['all' => 'Everyone', 'guest' => 'Signed-out visitors', 'customer' => 'Signed-in customers'];

    protected $fillable = ['section', 'label', 'url', 'audience', 'sort_order', 'is_enabled'];
    protected $casts = ['is_enabled' => 'boolean', 'sort_order' => 'integer'];

    public function visibleToCurrentVisitor(): bool
    {
        return $this->audience === 'all'
            || ($this->audience === 'guest' && ! auth()->check())
            || ($this->audience === 'customer' && auth()->check());
    }
}
