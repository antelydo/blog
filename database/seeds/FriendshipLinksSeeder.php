<?php
declare (strict_types = 1);

use think\migration\Seeder;

class FriendshipLinksSeeder extends Seeder
{
    public function run(): void
    {
        $faker = Faker\Factory::create('zh_CN');
        $data = [];

        // 生成100条友情链接数据
        for ($i = 1; $i <= 100; $i++) {
            $data[] = [
                'title' => $faker->company . '官网',
                'logo' => 'https://picsum.photos/100/100?random=' . $i,
                'url' => $faker->url,
                'sort' => $i,
                'user_id' => 1,
                'status' => 1,
                'type' => 1,
                'description' => $faker->sentence,
                'email' => $faker->email,
                'user_type' =>'admin',
                'creator' => $faker->randomElement(['admin', 'system']),
                'creator_type' => $faker->randomElement(['admin', 'system']),
                'created_at' => $faker->dateTimeBetween('-1 year', 'now')->format('Y-m-d H:i:s'),
                'updated_at' => $faker->dateTimeBetween('-1 year', 'now')->format('Y-m-d H:i:s')
            ];
        }

        $this->table('friendship_links')->insert($data)->save();
    }
} 