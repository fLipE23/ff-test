<?php

use App\Domain\Account\Enum\OperationType;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {

    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('balances', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('user_id')->index();
            $table->bigInteger('amount');
            $table->bigInteger('blocked_amount');
            $table->string('currency', 3)->index();
        });

        Schema::create('operations', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('user_id')->index();
            $table->bigInteger('amount');
            $table->string('currency', 3);
            $table->enum('type', [
                OperationType::TYPE_DEBIT->value,
                OperationType::TYPE_CREDIT->value,
                OperationType::TYPE_BLOCK->value,
                OperationType::TYPE_RELEASE->value,
            ]);
            $table->string('reason', 255)->nullable();

            $table->timestamps();
        });

        DB::statement('
            CREATE UNIQUE INDEX idx_unique_user_currency
            ON balances (user_id, currency);
        ');
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('balances');
        Schema::dropIfExists('operations');
    }
};
