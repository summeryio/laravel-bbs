<?php

namespace App\Observers;

use App\Handlers\SlugTranslateHandler;
use App\Http\Requests\Request;
use App\Models\Topic;

// creating, created, updating, updated, saving,
// saved,  deleting, deleted, restoring, restored

class TopicObserver
{
    public function saving(Topic $topic)
    {
        $topic->body = clean($topic->body, 'user_topic_body'); // XSS 过滤
        $topic->excerpt = make_excerpt($topic->body); // 生成话题摘录

        // 如 slug 字段无内容，即使用翻译器对 title 进行翻译
        if (!$topic->slug) {
            $topic->slug = app(SlugTranslateHandler::class)->translate($topic->title);
        }
    }

    public function updating(Topic $topic)
    {
        // 标题更新， 相应更新slug
        if ($topic->isDirty('title')) {
            $topic->slug = app(SlugTranslateHandler::class)->translate($topic->title);
        }
    }
}
