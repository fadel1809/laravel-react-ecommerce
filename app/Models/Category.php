<?php

namespace App\Models;

use Doctrine\DBAL\Schema\Schema;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema as FacadesSchema;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
class Category extends Model
{
   /**
    * Get the user that owns the Category
    *
    * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
    */
   public function parent(): BelongsTo
   {
       return $this->belongsTo(Category::class,  'parent_id');
   }
}
