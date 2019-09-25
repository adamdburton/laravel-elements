<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateEntityPropertiesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('elements_entity_properties', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->unsignedBigInteger('entity_id');
            $table->unsignedBigInteger('property_id');

            $table->boolean('boolean_value')->nullable();
            $table->integer('integer_value', false, false)->nullable();
            $table->double('double_value', 8, 2)->nullable();
            $table->string('string_value', 255)->nullable();
            $table->text('text_value')->nullable();
            $table->json('json_value')->nullable();
            $table->timestamp('timestamp_value', 0)->nullable();

            $table->timestamps();

            $table->index(['entity_id', 'property_id'], 'fk_index');
            $table->index(['entity_id', 'property_id', 'boolean_value'], 'fk_boolean_index');
            $table->index(['entity_id', 'property_id', 'integer_value'], 'fk_integer_index');
            $table->index(['entity_id', 'property_id', 'double_value'], 'fk_double_index');
            $table->index(['entity_id', 'property_id', 'string_value'], 'fk_string_index');
            $table->index(['entity_id', 'property_id', 'timestamp_value'], 'fk_timestamp_index');
//            $table->index(['entity_id', 'property_id', 'text_value'], 'text_index');
//            $table->index(['entity_id', 'property_id', 'json_value'], 'json_index');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('elements_entity_properties');
    }
}
