<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('documents', function (Blueprint $table) {
            $table->id();
            $table->string('name', 200);
            $table->string('title', 200)->comment('Title of the document, which sometimes is different');
            $table->string('sharepoint_id', 50)->unique();
            $table->string('eTag')->nullable();
            $table->date('document_date')->nullable();
            $table->string('language')->nullable()->index();
            $table->string('content_type')->nullable()->index();
            $table->ulid('institution_id')->nullable();
            $table->foreign('institution_id')->references('id')->on('institutions')->nullOnDelete();
            $table->text('public_url')->nullable();
            $table->date('public_url_created_at')->nullable();
            $table->text('thumbnail_url')->nullable();
            $table->boolean('is_active')->default(true);
            $table->string('sharepoint_site_id', 50);
            $table->string('sharepoint_list_id', 50);

            $table->timestamp('created_at')->useCurrent();
            $table->timestamp('updated_at')->useCurrent()->useCurrentOnUpdate();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('documents');
    }
};
