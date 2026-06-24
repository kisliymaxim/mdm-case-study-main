<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('assets', function (Blueprint $table) {
            $table->id();
            $table->string('serial_code')->unique();
            $table->foreignId('employee_id')
                ->constrained('employees')
                ->restrictOnDelete()
                ->cascadeOnUpdate();
            $table->string('device_name');
            $table->string('provider')->default('jamf')->index();
            $table->json('specs')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('assets');
    }
};
