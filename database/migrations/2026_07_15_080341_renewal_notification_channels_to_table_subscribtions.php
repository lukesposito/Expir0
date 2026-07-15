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
        Schema::table('subscriptions', function (Blueprint $table) {
            $table->unsignedSmallInteger('renewal_interval_months')
                ->nullable()
                ->after('rinnovo_automatico');

            $table->json('renewal_notification_channels')
                ->nullable()
                ->after('renewal_interval_months');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('subscriptions', function (Blueprint $table) {
            $table->dropColumn([
                'renewal_interval_months',
                'renewal_notification_channels',
            ]);
        });
    }
};
