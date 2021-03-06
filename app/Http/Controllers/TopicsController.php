<?php

namespace App\Http\Controllers;

use App\Models\Category;
use App\Models\Topic;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Http\Requests\TopicRequest;
use Auth;
use App\Handlers\ImageUploadHandler;

class TopicsController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth', ['except' => ['index', 'show']]);
    }

	public function index(Request $request, Topic $topic)
	{
		$topics = $topic->withOrder($request->order)
        ->with('user', 'category')
        ->paginate(12);

		return view('topics.index', compact('topics'));
	}

    public function show(Request $request, Topic $topic)
    {
        // 如果话题的 Slug 字段不为空  并且话题 Slug 不等于请求的路由参数 Slug；
        if (!empty($topic->slug) && $request->slug != $topic->slug) {
            // 301 永久重定向到正确的 URL 上。
            return redirect($topic->link(), 301);
        }

        return view('topics.show', compact('topic'));
    }

	public function create(Topic $topic)
	{
	    $categories = Category::all();

		return view('topics.create_and_edit', compact('topic', 'categories'));
	}

	public function store(TopicRequest $request, Topic $topic)
	{
	    // $request->all() 获取所有用户的请求数据数组，如 ['title' => '标题', 'body' => '内容', ... ]
        // fill 方法会将传参的键值数组填充到模型的属性中，如以上数组，$topic->title 的值为 标题
		$topic->fill($request->all());

		$topic->user_id = Auth::id();
		$topic->save();

		return redirect()->to($topic->link())->with('success', '帖子创建成功！');
	}

	public function edit(Topic $topic)
	{
        $this->authorize('update', $topic);

        $categories = Category::all();

		return view('topics.create_and_edit', compact('topic', 'categories'));
	}

	public function update(TopicRequest $request, Topic $topic)
	{
		$this->authorize('update', $topic);
		$topic->update($request->all());

		return redirect()->to($topic->link())->with('success', 'Updated successfully.');
	}

	public function destroy(Topic $topic)
	{
		$this->authorize('destroy', $topic);
		$topic->delete();

		return redirect()->route('topics.index')->with('success', 'Deleted successfully.');
	}

	public function uploadImage(Request $request, ImageUploadHandler $uploader) {
        $data = [
            'success' => false,
            'msg' => '上传失败！',
            'file_path' => ''
        ];

        if ($file = $request->upload_file) {
            $result = $uploader->save($file, 'topics', \Auth::id(), 1024);

            if ($result) {
                $data['file_path'] = $result['path'];
                $data['msg'] = '上传成功！';
                $data['success'] = true;
            }
        }

        return $data;
    }
}
