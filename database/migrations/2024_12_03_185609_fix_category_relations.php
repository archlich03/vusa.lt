<?php

use App\Models\Category;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        $calendars = DB::table('calendar')->get(['id', 'category']);
        $categories = Category::all(['id', 'alias']);

        Schema::table('calendar', function (Blueprint $table) {
            $table->dropForeign(['category']);
            $table->dropColumn('category');
            $table->unsignedInteger('category_id')->nullable();
            $table->foreign('category_id')->references('id')->on('categories');
        });

        foreach ($calendars as $calendar) {
            $category = $categories->firstWhere('alias', $calendar->category);
            if ($category) {
                DB::table('calendar')->where('id', $calendar->id)->update(['category_id' => $category->id]);
            }
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        //
    }
};
