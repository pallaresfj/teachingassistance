<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        $dropIp = Schema::hasColumn('attendances', 'ip_address');
        $dropUserAgent = Schema::hasColumn('attendances', 'user_agent');

        if (!($dropIp || $dropUserAgent)) {
            return;
        }

        Schema::table('attendances', function (Blueprint $table) use ($dropIp, $dropUserAgent) {
            if ($dropIp) {
                $table->dropColumn('ip_address');
            }
            if ($dropUserAgent) {
                $table->dropColumn('user_agent');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        $addIp = !Schema::hasColumn('attendances', 'ip_address');
        $addUserAgent = !Schema::hasColumn('attendances', 'user_agent');

        if (!($addIp || $addUserAgent)) {
            return;
        }

        Schema::table('attendances', function (Blueprint $table) use ($addIp, $addUserAgent) {
            if ($addIp) {
                $table->string('ip_address', 45)->nullable()->after('device_info');
            }
            if ($addUserAgent) {
                $table->text('user_agent')->nullable()->after('ip_address');
            }
        });
    }
};
