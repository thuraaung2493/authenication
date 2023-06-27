<?php

declare(strict_types=1);

use App\Enums\Language;
use App\Enums\LoginType;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class () extends Migration {
    public function up(): void
    {
        Schema::create('users', static function (Blueprint $table): void {
            $table->uuid('id')->primary();

            $table->string('name');
            $table->string('email')->nullable();
            $table->timestamp('email_verified_at')->nullable();
            $table->string('phone')->nullable();
            $table->string('password')->nullable();
            $table->string('profile')->nullable();

            $table->enum('login_type', LoginType::values())
                ->default(LoginType::GMAIL->value);

            $table->string('login_id')->nullable();
            $table->string('device_token')->nullable();

            $table->enum('language', Language::values())
                ->default(Language::EN->value);

            $table->timestamps();
            $table->softDeletes();

            $table->index(['login_type', 'login_id']);
            $table->unique(['email', 'login_type']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('users');
    }
};
