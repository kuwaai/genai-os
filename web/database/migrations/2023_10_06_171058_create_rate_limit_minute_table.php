<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

class CreateRateLimitMinuteTable extends Migration
{
    /**
     * Run the migrations.
     */
    public function up()
    {
        // Create the main partitioned table
        DB::statement("
            CREATE TABLE rate_limit_minute (
                email VARCHAR(255) NOT NULL,
                date DATE NOT NULL,
                timestamp_minute BIGINT NOT NULL,
                count_minute INTEGER DEFAULT 0,
                PRIMARY KEY (email, date, timestamp_minute)
            ) PARTITION BY RANGE (date)
        ");

        // Create the partition for October 2023
        DB::statement("CREATE TABLE rate_limit_minute_202310 PARTITION OF rate_limit_minute
                       FOR VALUES FROM ('2023-10-01') TO ('2023-10-31')");
    }

    /**
     * Reverse the migrations.
     */
    public function down()
    {
        // Drop the partition
        Schema::dropIfExists('rate_limit_minute_202310');
        
        // Drop the main table
        Schema::dropIfExists('rate_limit_minute');
    }
}