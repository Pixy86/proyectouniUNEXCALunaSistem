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
        Schema::table('users', function (Blueprint $table) {
            $table->string('security_answer_1')->nullable()->after('password');
            $table->string('security_answer_2')->nullable()->after('security_answer_1');
            $table->string('security_answer_3')->nullable()->after('security_answer_2');
            // Stored password (encrypted) to show when recovery is successful
            $table->text('plain_password_encrypted')->nullable()->after('security_answer_3');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn([
                'security_answer_1',
                'security_answer_2',
                'security_answer_3',
                'plain_password_encrypted',
            ]);
        });
    }
};
