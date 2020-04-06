<?php

namespace Insowe\AdminTemplate\Models;

use Illuminate\Database\Eloquent\Model as BaseModel;
use Illuminate\Database\Eloquent\SoftDeletes;

/**
 * 文章內容
 */
class Article extends BaseModel
{
    use SoftDeletes;
    
    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'category_id', 'title', 'content',
        'pictures', 'slug', 'display_order',
        'publish_at', 'online_at', 'offline_at',
    ];

    /**
     * The attributes that should be cast to native types.
     *
     * @var array
     */
    protected $casts = [
        'pictures' => 'array',
        'publish_at' => 'datetime',
        'online_at' => 'datetime',
        'offline_at' => 'datetime',
    ];
    
    public function category()
    {
        return $this->belongsTo(ArticleCategory::class, 'category_id', 'id');
    }

    public function tags()
    {
        return $this->belongsToMany(ArticleTag::class, 'articles_tags', 'article_id', 'article_tag_id')
                ->withTimestamps('created_at', 'created_at');
    }
    
    public function getCoverImage()
    {
        return (is_array($this->pictures) && count($this->pictures) > 0)
            ? $this->pictures[0]
            : 'https://fakeimg.pl/150x150/?text=Article';
    }
}
