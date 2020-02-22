<?php

use Illuminate\Database\Seeder;
use App\Models\Topic;
use App\Models\User;
use App\Models\Category;

class TopicsTableSeeder extends Seeder
{
    public function run()
    {
        $user_ids = User::all()->pluck('id')->toArray(); // 所有用户 ID 数组
        $category_ids = Category::all()->pluck('id')->toArray(); // 所有分类 ID 数组
        $faker = app(\Faker\Generator::class);

        $topics = factory(Topic::class)
            ->times(100)
            ->make()
            ->each(function ($topic, $index) use ($user_ids, $category_ids, $faker) {
            $topic->user_id = $faker->randomElement($user_ids);
            $topic->category_id = $faker->randomElement($category_ids);
        });

        // 将数据集合转换为数组，并插入到数据库中
        Topic::insert($topics->toArray());
    }

}

