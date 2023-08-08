<?php

namespace Database\Seeders;

use App\Models\Banner;
use App\Models\Calendar;
use App\Models\Comment;
use App\Models\Doing;
use App\Models\Duty;
use App\Models\Institution;
use App\Models\MainPage;
use App\Models\Matter;
use App\Models\News;
use App\Models\Page;
use App\Models\SaziningaiExam;
use App\Models\SaziningaiExamFlow;
use App\Models\SaziningaiExamObserver;
use App\Models\User;
use Illuminate\Database\Seeder;

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
        $this->call(RegistrationFormsSeeder::class);
        $this->call(AdminSeeder::class);
        $this->call(PadaliniaiSeeder::class);

        Institution::factory(10)
            ->has(Matter::factory()->count(3))
            ->create();

        User::factory(10)
            ->has(Doing::factory(5))->hasAttached(Duty::factory(3), ['start_date' => now()]
        )->create();

        $this->call(MenuSeeder::class);
        $this->call(DeleteAndSeedPermissions::class);

        Banner::factory(20)->create();
        Calendar::factory(50)->create();
        MainPage::factory(50)->create();
        News::factory(75)->create();
        Page::factory(75)->create();
        SaziningaiExam::factory(15)->create();
        SaziningaiExamFlow::factory(20)->create();
        SaziningaiExamObserver::factory(10)->create();

        $this->call(RoleStudentRepresentativeSeeder::class);
        $this->call(RoleStudentRepresentativeCoordinatorSeeder::class);
    }
}
