<?php

use Illuminate\Database\Seeder;

class MinecraftVersionsSeeder extends Seeder
{
    private $versionsByType = [
        'PC' => [
            5   => '1.7.10',
            47  => '1.8',
            107 => '1.9',
            210 => '1.10',
            315 => '1.11',
            335 => '1.12',
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
        foreach ($this->versionsByType as $type => $versions) {
            foreach ($versions as $protocol_id => $name) {
                $exists = DB::table('versions')->where('type', $type)->where('name', $name)->exists();
                if (!$exists) {
                    try {
                        DB::table('versions')->insert([
                            'type'        => $type,
                            'protocol_id' => $protocol_id,
                            'name'        => $name,
                        ]);
                    } catch (\Illuminate\Database\QueryException $e) {
                        /*
                         * Ignore duplicate entry exception
                         */
                        if ($e->getCode() != 23000 /* ER_DUP_CODE */) {
                            throw $e;
                        }
                    }
                }
            }
        }
    }
}
