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
        Schema::table('parents', function (Blueprint $table) {
            $table->string('ic_number', 20)->change();
            $table->string('password_view')->nullable()->after('password');
        });

        // Clean existing IC numbers - remove any hyphens or spaces
        DB::table('parents')->get()->each(function ($parent) {
            $cleanIc = preg_replace('/[^0-9]/', '', $parent->ic_number);
            if (strlen($cleanIc) === 12) {
                DB::table('parents')
                    ->where('id', $parent->id)
                    ->update(['ic_number' => $cleanIc]);
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('parents', function (Blueprint $table) {
            // Revert to VARCHAR(20)
            $table->string('ic_number', 20)->change();
            $table->string('password_view')->nullable()->after('password');
        });
    }
};
