<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! $this->subscriptionForeignKeyExists()) {
            Schema::table('transactions', function (Blueprint $table): void {
                $table->foreign('subscription_id')
                    ->references('id')
                    ->on('subscriptions')
                    ->cascadeOnDelete();
            });
        }
    }

    public function down(): void
    {
        if ($this->subscriptionForeignKeyExists()) {
            Schema::table('transactions', function (Blueprint $table): void {
                $table->dropForeign(['subscription_id']);
            });
        }
    }

    private function subscriptionForeignKeyExists(): bool
    {
        return DB::table('information_schema.KEY_COLUMN_USAGE')
            ->where('CONSTRAINT_SCHEMA', DB::getDatabaseName())
            ->where('TABLE_NAME', 'transactions')
            ->where('COLUMN_NAME', 'subscription_id')
            ->whereNotNull('REFERENCED_TABLE_NAME')
            ->exists();
    }
};
