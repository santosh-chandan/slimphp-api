<?php
/**
 * Chronological order: Migrations must be applied in the correct sequence.
 * No duplicates: If create multiple migration with the same base name, the prefix avoids collisions.
 */
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Capsule\Manager as Capsule;

class CreateTasksTable
{
    public function up()
    {
        Capsule::schema()->create('tasks', function (Blueprint $table) {
            $table->id();
            $table->string('title');
            $table->text('description')->nullable();
            $table->boolean('completed')->default(false);
            $table->timestamps();
        });
    }

    public function down()
    {
        Capsule::schema()->dropIfExists('tasks');
    }
}
