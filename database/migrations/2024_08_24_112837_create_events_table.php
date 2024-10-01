<?php

use App\Constants\Event\Constants;
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
        Schema::create('events', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->enum('event_type', array_values(Constants::EVENT_TYPES));
            $table->text('description'); // Long HTML content
            $table->string('image_url')->nullable();
            $table->integer('capacity_limit');
            $table->integer('waiting_list_size')->default(0);
            $table->boolean('automatic_ticket_upgrade')->default(true);
            $table->datetime('start_date');
            $table->datetime('end_date');
            $table->string('location')->nullable();
            $table->enum('status', array_values(Constants::EVENT_STATUSES))->default(Constants::EVENT_STATUSES['DRAFT']);
            $table->text('cancellation_policy')->nullable();
            $table->foreignId('created_by')->constrained('users')->onDelete('cascade'); // Event creator (single user)
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('events');
    }
};
