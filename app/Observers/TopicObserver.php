<?php

namespace App\Observers;

use App\Handlers\SlugTranslateHandler;
use App\Http\Requests\Request;
use App\Models\Topic;
use App\Jobs\TranslateSlug;

// creating, created, updating, updated, saving,
// saved,  deleting, deleted, restoring, restored

class TopicObserver
{
    public function saving(Topic $topic)
    {
        $topic->body = clean($topic->body, 'user_topic_body'); // XSS 过滤
        $topic->excerpt = make_excerpt($topic->body); // 生成话题摘录
    }

    public function saved(Topic $topic) {
        // 如 slug 字段无内容，即使用翻译器对 title 进行翻译
        if (!$topic->slug) {
            // $topic->slug = app(SlugTranslateHandler::class)->translate($topic->title);
            dispatch(new TranslateSlug($topic));
        }
    }

    public function updating(Topic $topic)
    {
        // 标题更新， 相应更新slug
        if ($topic->isDirty('title')) {
            // $topic->slug = app(SlugTranslateHandler::class)->translate($topic->title);
            dispatch(new TranslateSlug($topic));
        }
    }

    // 监听话题删除成功的事件，在此事件发生时，删除此话题下所有的回复
    public function deleted(Topic $topic) {
        \DB::table('replies')->where('topic_id', $topic->id)->delete();
    }
}
