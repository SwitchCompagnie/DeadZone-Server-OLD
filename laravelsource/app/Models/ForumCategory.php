<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Str;

class ForumCategory extends Model
{
    protected $fillable = [
        'name',
        'description',
        'slug',
        'parent_id',
        'order',
        'is_active',
    ];

    protected $casts = [
        'is_active' => 'boolean',
    ];

    protected static function boot()
    {
        parent::boot();
        
        static::creating(function ($category) {
            if (empty($category->slug)) {
                $category->slug = Str::slug($category->name);
            }
        });
    }

    public function parent(): BelongsTo
    {
        return $this->belongsTo(ForumCategory::class, 'parent_id');
    }

    public function children(): HasMany
    {
        return $this->hasMany(ForumCategory::class, 'parent_id')->orderBy('order');
    }

    public function threads(): HasMany
    {
        return $this->hasMany(ForumThread::class, 'category_id');
    }

    public function latestThreads(int $limit = 5): HasMany
    {
        return $this->threads()->latest()->limit($limit);
    }
}
