<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('business_profiles', function (Blueprint $table) {
            $table->string('address')->nullable()->after('category');
            $table->string('business_type')->nullable()->after('address');
            $table->string('store_photo')->nullable()->after('business_type');
        });
    }

    public function down(): void
    {
        Schema::table('business_profiles', function (Blueprint $table) {
            $table->dropColumn(['store_photo', 'business_type', 'address']);
        });
    }
};