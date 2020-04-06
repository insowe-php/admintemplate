<?php

namespace Insowe\AdminTemplate\Models;

use Illuminate\Database\Eloquent\Model as BaseModel;
use Illuminate\Database\Eloquent\SoftDeletes;

/**
 * 文章標籤
 */
class ArticleTag extends BaseModel
{
    use SoftDeletes;
    
    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'name', 'slug', 'display_order',
    ];

    /**
     * The attributes that should be hidden for arrays.
     *
     * @var array
     */
    protected $hidden = [
        'created_at', 'updated_at', 'deleted_at',
        'pivot',
    ];

    public function articles()
    {
        return $this->belongsToMany(Article::class, 'articles_tags', 'article_tag_id', 'article_id');
    }
}
