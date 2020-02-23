<?php

namespace App\Models;

class Topic extends Model
{
    protected $fillable = ['title', 'body',  'category_id','excerpt', 'slug'];

    // 一个话题属于一个分类，$topic->category
    public function category() {
        return $this->belongsTo(Category::class);
    }

    // 一个话题属于一个作者， $topic->user
    public function user() {
        return $this->belongsTo(User::class);
    }

    // 最新回复/发布 排序
    public function scopeWithOrder($query, $order) {
        switch ($order) {
            case 'recent':
                $query->recent();
                break;
            default:
                $query->recentReplied();
                break;
        }
    }
    public function scopeRecent($query) {
        return $query->orderBy('created_at', 'desc');
    }
    public function scopeRecentReplied($query) {
        return $query->orderBy('updated_at', 'desc');
    }

    // slug 新的跳转链接方法link()
    // 参数 $params 允许附加 URL 参数的设定。
    public function link($params = []) {
        return route('topics.show', array_merge([$this->id, $this->slug], $params));
    }
}
