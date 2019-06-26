<?php

use Illuminate\Database\Seeder;

class MinecraftVersionsSeeder extends Seeder
{
    private $versionsByType = [
        'PC' => [
            5 => '1.7', // 1.7.10 (not 1.7.2)
            47 => '1.8',
            107 => '1.9',
            210 => '1.10',
            315 => '1.11',
            335 => '1.12',
            393 => '1.13',
            477 => '1.14',
        ],
        'PE' => [
        ]
    ];

    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $ids = [];
        foreach ($this->versionsByType as $type => $versions) {
            foreach ($versions as $protocol_id => $name) {
                $id = DB::table('versions')->select('id')->where('type', $type)->where('name', $name)->first();
                if ($id !== null) {
                    $id = $id->id;
                } else {
                    $id = DB::table('versions')->insertGetId([
                        'type' => $type,
                        'protocol_id' => $protocol_id,
                        'name' => $name,
                    ]);
                }
                $ids[] = $id;
            }
        }
        DB::table('versions')->whereNotIn('id', $ids)->delete();
    }
}
