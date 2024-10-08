<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class MakeNotificationTypeUidNullableInGeneralNotificationsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('general_notifications', function (Blueprint $table) {
            $table->uuid('notification_type_uid', 36)->nullable()->change();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('general_notifications', function (Blueprint $table) {
            // Assuming the default value for 'notification_type_uid' was an empty string ('')
            $table->uuid('notification_type_uid', 36)->nullable(false)->change();
        });
    }
}
