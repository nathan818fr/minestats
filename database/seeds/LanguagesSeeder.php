<?php

use Illuminate\Database\Seeder;

class LanguagesSeeder extends Seeder
{
    private $languages = [
        'de', // German
        'en', // English
        'fr', // French
    ];

    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        foreach ($this->languages as $language) {
            try {
                DB::table('languages')->insert([
                    'id' => $language,
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
