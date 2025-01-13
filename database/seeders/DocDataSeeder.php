<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class DocDataSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        // the list from videoportal
        $list = collect([
            '0' => '--- Ohne Titel---',
            '1' => 'Dr.',
            '2' => 'Prof.',
            '3' => 'Prof. Dr.',
            '4' => 'MA',

            '5' => 'Dipl.-Ing.',
            '6' => 'Dipl.-Inf.',
            '7' => 'PD Dr.',
            '8' => 'Dr. med.',
            '9' => 'Dipl.-Psych.',
            '10' => 'Dipl.-Kffr.',
            '11' => 'Dipl.-Kfm.',
            '12' => 'Dipl.-Math.',
            '13' => 'M. Sc.',
            '14' => 'Dr.-Ing.',
            '15' => 'M. Eng',
            '16' => 'Dipl.-Biol.',
            '17' => 'Prof. Dr.-Ing.',
            '18' => 'Prof. Dr. med. dent. ',
            '19' => 'Dipl.-Päd.',
            '20' => 'Dr. phil.',
            '21' => 'Prof. Dr. med.',
            '22' => 'Dr. rer. nat.',
            '23' => 'Prof. Dr. rer. nat.',
            '24' => 'Dr. med.',
            '25' => 'PD Dr. med.',
            '26' => 'Prof. Dr.-Ing. habil.',
            '27' => 'Dr. rer. biol. hum.',
            '28' => 'Dr. rer. pol.',
            '29' => 'AKD Dr. iur. Dr. phil.',
            '30' => 'Hon.-Prof. Dr.-Ing.',
            '31' => 'B. Sc.',
            '32' => 'OStRin',
            '33' => 'PD Dr.-Ing. habil.',
        ]);

        // all possible combinations
        $allItems = [
            'B. Sc.',
            'B.Eng.',
            'B.Sc.',
            'Dipl.',
            'Dipl. Ökotrophol.',
            'Dipl.-Chem.',
            'Dipl.-Inf.',
            'Dipl.-Inform. (FH)',
            'Dipl.-Ing.',
            'Dipl.-Phys.',
            'Dipl.-Phys. Dr.',
            'Dipl.-Sozialw.',
            'Doctor medic',
            'Dr.',
            'Dr. (N.-P.-Bockov-Forschungszentrum)',
            'Dr. Dr.',
            'Dr. Dr. med.',
            'Dr. Dr. rer. biol. hum.',
            'Dr. Ing.',
            'Dr. PH',
            'Dr. des.',
            'Dr. habil.',
            'Dr. jur.',
            'Dr. med.',
            'Dr. med. ',
            'Dr. med. Dr. med. dent.',
            'Dr. med. MHBA',
            'Dr. med. dent.',
            'Dr. med. dent. Dr. med',
            'Dr. med. dent. Dr. med.',
            'Dr. med. sci.',
            'Dr. med. univ.',
            'Dr. med. vet.',
            'Dr. phil.',
            'Dr. phil. ',
            'Dr. phil. nat.',
            'Dr. rer. biol. hum.',
            'Dr. rer. medic.',
            'Dr. rer. nat.',
            'Dr. rer. nat. ',
            'Dr. rer. pol.',
            'Dr. sc. nat.',
            'Dr. techn.',
            'Dr.-Ing.',
            'Dr.-Ing. ',
            'Dr.Ing.',
            'Dr.des.',
            'Dr.jur.',
            'Dr.med.',
            'Dr.phil.',
            'Dr.rer.nat.',
            'Dr.rer.pol.',
            'Dr.sc.ETHZürich',
            'M. Sc.',
            'M.A.',
            'M.Sc.',
            'MUDr.',
            'MUDr. (Univ. Bratislava)',
            'Mag. iur.',
            'OA Dr. med.',
            'PD Dr.',
            'PD Dr. Dr.',
            'PD Dr. habil.',
            'PD Dr. med.',
            'PD Dr. med. ',
            'PD Dr. med. Dr. med. dent. habil.',
            'PD Dr. med. dent. habil.',
            'PD Dr. med. habil.',
            'PD Dr. phil.',
            'PD Dr. rer. nat.',
            'PD Dr. rer. nat. Dr. habil. med.',
            'PD Dr. rer. nat. habil.',
            'PD Dr.-Ing.',
            'PD Dr.med.',
            'PD Dr.phil.',
            'PHD',
            'Pfarrerin',
            'Ph.D.',
            'Prof.',
            'Prof. Dr.',
            'Prof. Dr. Dr.',
            'Prof. Dr. Dr. h. c.',
            'Prof. Dr. Ing.',
            'Prof. Dr. med.',
            'Prof. Dr. med. ',
            'Prof. Dr. rer. biol. hum.',
            'Prof. Dr. rer. nat',
            'Prof. Dr.-Ing.',
            'Prof. Dr.-Ing. ',
            'Prof. MUDr. (Univ. Brünn) Dr. med. habil.',
            'Prof. Ph.D.',
            'Prof. apl.',
            'Prof.Dr.',
            'Prof.Dr.Dr.',
            'Prof.Dr.Dr.Dr.',
            'Prof.Dr.med.',
            'Univ.Prof.Dr.Dr',
            'apl. Prof.',
            'apl. Prof. Dr.',
            'apl. Prof. Dr. Dr.',
            'apl. Prof. Dr. med.',
            'apl. Prof. Dr. med. habil.',
            'apl.Prof.Dr.',
            'apl.Prof.Dr.Dr.',
            'dr. med.',
            'dr. med. (Univ. Pecs)',
            'dr. med. (Univ. Semmelweis)',
            'dr. med. (Univ. Szeged)',
        ];

        // Convert the existing collection to an array
        $existingList = $list->toArray();

        // Create a new list starting with the existing items
        $newList = $existingList;

        // Get the highest existing ID (which is 33)
        $newId = 34;

        // Loop through the new items and add them to the new list with new IDs
        foreach ($allItems as $item) {
            if (! in_array($item, $newList)) {
                // Avoid duplicating existing items
                $newList[(string) $newId] = $item;
                $newId++;
            }
        }

        // Convert the new list back to a collection if needed
        $newCollection = collect($newList);

        $newCollection->each(function ($value, $key) {
            DB::table('academic_degrees')->insert([
                'id' => $key,
                'title' => $value,
                'created_at' => now(),
                'updated_at' => null,
            ]);
        });
    }
}
