<?php

use App\Enums\CommentStatus;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('comments', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('post_id')->constrained()->cascadeOnDelete();
            $table->string('name');
            $table->string('email');
            $table->string('phone', 20)->nullable();
            $table->text('content');
            $table->string('status')->default(CommentStatus::Review);
            $table->timestamps();
            $table->softDeletes();

            $table->index('status');
            $table->index('post_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('comments');
    }
};
