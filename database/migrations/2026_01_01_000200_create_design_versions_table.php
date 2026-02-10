<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('design_versions', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('design_id')->constrained()->cascadeOnDelete();
            $table->unsignedInteger('version_number');
            $table->longText('canvas_json');
            $table->string('snapshot_path')->nullable();
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->string('change_note', 250)->nullable();
            $table->timestamps();

            $table->unique(['design_id', 'version_number']);
            $table->index(['design_id', 'created_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('design_versions');
    }
};
