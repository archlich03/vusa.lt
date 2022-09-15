<?php

namespace Database\Seeders;

use App\Models\Calendar;
use App\Models\Duty;
use App\Models\DutyInstitution;
use App\Models\MainPage;
use App\Models\News;
use App\Models\Page;
use App\Models\SaziningaiExam;
use App\Models\SaziningaiExamFlow;
use App\Models\SaziningaiExamObserver;
use Illuminate\Database\Seeder;
use App\Models\User;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     *
     * @return void
     */
    public function run()
    {  
        $this->call(CategoriesSeeder::class);
        $this->call(DutyInstitutionTypesSeeder::class);
        $this->call(DutyTypesSeeder::class);
        $this->call(RoleSeeder::class);
        $this->call(RegistrationFormsSeeder::class);
        $this->call(UserSeeder::class);
        $this->call(PadaliniaiSeeder::class);

        DutyInstitution::factory(10)->create();
        
        User::factory()->has(Duty::factory()->count(3))->count(10)->create();
        
        $this->call(MenuSeeder::class);
        
        Calendar::factory()->count(50)->create();
        MainPage::factory()->count(30)->create();
        News::factory()->count(75)->create();
        Page::factory()->count(40)->create();
        SaziningaiExam::factory()->count(15)->create();
        SaziningaiExamFlow::factory()->count(20)->create();
        SaziningaiExamObserver::factory()->count(10)->create();
    }
}