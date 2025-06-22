<?php

namespace Database\Seeders;

use App\Models\District;
use App\Models\Division;
use App\Models\Thana;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class LocationSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        DB::transaction(function () {
            $rajshahi = Division::create(['name' => 'Rajshahi']);
            $dhaka = Division::create(['name' => 'Dhaka']);
            $chittagong = Division::create(['name' => 'Chittagong']);
            $khulna = Division::create(['name' => 'Khulna']);

            // Rajshahi Division (Max 2 Districts)
            $bogra = District::create(['division_id' => $rajshahi->id, 'name' => 'Bogura']);
            $naogaon = District::create(['division_id' => $rajshahi->id, 'name' => 'Naogaon']);

            // Bogura Thanas (4-5)
            Thana::create(['district_id' => $bogra->id, 'name' => 'Bogra Sadar']);
            Thana::create(['district_id' => $bogra->id, 'name' => 'Shibganj']);
            Thana::create(['district_id' => $bogra->id, 'name' => 'Sonatola']);
            Thana::create(['district_id' => $bogra->id, 'name' => 'Gabtoli']);
            Thana::create(['district_id' => $bogra->id, 'name' => 'Kahalu']);

            // Naogaon Thanas (4-5)
            // Note: Actual thana list needs to be verified and may be more than 2 districts to fulfill the 4-5 thana rule per district
            Thana::create(['district_id' => $naogaon->id, 'name' => 'Naogaon Sadar']);
            Thana::create(['district_id' => $naogaon->id, 'name' => 'Raninagar']);
            Thana::create(['district_id' => $naogaon->id, 'name' => 'Atrai']);
            Thana::create(['district_id' => $naogaon->id, 'name' => 'Badalgachhi']);

            // Dhaka Division (Max 2 Districts)
            $dhaka_district = District::create(['division_id' => $dhaka->id, 'name' => 'Dhaka']);
            $gazipur = District::create(['division_id' => $dhaka->id, 'name' => 'Gazipur']);

            // Dhaka Thanas (4-5) - Using generic names as specific list was short
            Thana::create(['district_id' => $dhaka_district->id, 'name' => 'Dhaka North Thana 1']);
            Thana::create(['district_id' => $dhaka_district->id, 'name' => 'Dhaka South Thana 1']);
            Thana::create(['district_id' => $dhaka_district->id, 'name' => 'Dhaka East Thana']);
            Thana::create(['district_id' => $dhaka_district->id, 'name' => 'Dhaka West Thana']);

            // Gazipur Thanas (4-5) - Using generic names as specific list was short
            Thana::create(['district_id' => $gazipur->id, 'name' => 'Gazipur Sadar Thana']);
            Thana::create(['district_id' => $gazipur->id, 'name' => 'Kaliakair Thana']);
            Thana::create(['district_id' => $gazipur->id, 'name' => 'Kapasia Thana']);
            Thana::create(['district_id' => $gazipur->id, 'name' => 'Sreepur Thana']);

            // Chittagong Division (Max 2 Districts)
            $chittagong_district = District::create(['division_id' => $chittagong->id, 'name' => 'Chattogram']);
            $comilla = District::create(['division_id' => $chittagong->id, 'name' => 'Cumilla']);

            // Chattogram Thanas (4-5) - Using generic names
            Thana::create(['district_id' => $chittagong_district->id, 'name' => 'Chattogram North Thana']);
            Thana::create(['district_id' => $chittagong_district->id, 'name' => 'Chattogram South Thana']);
            Thana::create(['district_id' => $chittagong_district->id, 'name' => 'Patenga Thana']);
            Thana::create(['district_id' => $chittagong_district->id, 'name' => 'Kotwali Thana']);

            // Cumilla Thanas (4-5) - Using generic names
            Thana::create(['district_id' => $comilla->id, 'name' => 'Cumilla Sadar Thana']);
            Thana::create(['district_id' => $comilla->id, 'name' => 'Daudkandi Thana']);
            Thana::create(['district_id' => $comilla->id, 'name' => 'Barura Thana']);
            Thana::create(['district_id' => $comilla->id, 'name' => 'Chandina Thana']);
        });
    }
}