<?php

use Illuminate\Database\Seeder;

class LanguagesSeeder extends Seeder
{
    /*
     * I know very well that the flags do not represent languages! But we need a simple and small way to display the
     * languages of servers and a flag is the best solution I have.
     */

    private $languages = [
        'de', // German
        'en', // English
        'fr', // French
        'es', // Spain
        'nl', // Dutch
        'cn', // Chinese
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
