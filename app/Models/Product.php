<?php

namespace App\Models;

use App\Enums\ProductStatusEnum;
use Illuminate\Contracts\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Spatie\MediaLibrary\HasMedia;
use Spatie\MediaLibrary\InteractsWithMedia;
use Spatie\MediaLibrary\MediaCollections\Models\Media;

/**
 * Class Product
 *
 * Represents a product in the system.
 * A product can belong to a department, category, and user,
 * and can have multiple variation types and variations.
 */
class Product extends Model implements HasMedia
{
    use InteractsWithMedia;

    /**
     * Cast attributes to native types.
     * - 'variation' is stored as JSON in the database and cast to an array.
     */
    protected $casts = [
        'variation' => 'array'
    ];

    /**
     * Register image conversions for this model using Spatie Media Library.
     * - thumb: 100px width
     * - small: 480px width
     * - large: 1200px width
     */
    public function registerMediaConversions(?Media $media = null): void
    {
        $this->addMediaConversion('thumb')
            ->width(100);

        $this->addMediaConversion('small')
            ->width(480);

        $this->addMediaConversion('large')
            ->width(1200);
    }

    /**
     * A product belongs to a department.
     */
    public function department(): BelongsTo
    {
        return $this->belongsTo(Department::class);
    }

    /**
     * A product belongs to a category.
     */
    public function category(): BelongsTo
    {
        return $this->belongsTo(Category::class);
    }

    /**
     * A product is created by a user.
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    /**
     * A product has many variation types (e.g., Size, Color).
     */
    public function variationTypes(): HasMany
    {
        return $this->hasMany(VariationType::class);
    }

    /**
     * A product has many variations (specific combinations of variation options).
     */
    public function variations(): HasMany
    {
        return $this->hasMany(ProductVariation::class, 'product_id');
    }

    /**
     * Get the product price for a given set of variation option IDs.
     *
     * @param array $optionIds Array of selected variation option IDs.
     * @return float|int|null Price for the matched variation, or the base product price if no match is found.
     */
    public function getPriceForOptions($optionIds = [])
    {
        // Normalize option IDs (sort for comparison)
        $optionIds = array_values($optionIds);
        sort($optionIds);

        // Check each variation for a match
        foreach ($this->variations as $variation) {
            $a = $variation->variation_type_option_ids;
            sort($a);

            if ($optionIds == $a) {
                return $variation->price !== null
                    ? $variation->price
                    : $this->price;
            }
        }

        // Fallback: return base product price
        return $this->price;
    }

    /**
     * Scope: Filter products created by the currently authenticated vendor.
     */
    public function scopeForVendor(Builder $query): Builder
    {
        return $query->where('created_by', auth()->user()->id);
    }

    /**
     * Scope: Filter products with published status.
     */
    public function scopePublished(Builder $query): Builder
    {
        return $query->where('status', ProductStatusEnum::Published);
    }

    /**
     * Scope: Products that are published and visible on the website.
     */
    public function scopeForWebsite(Builder $query): Builder
    {
        return $query->published();
    }
}
