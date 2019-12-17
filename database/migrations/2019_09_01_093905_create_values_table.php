<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateValuesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('elements_values', function (Blueprint $table) {
            $table->unsignedBigInteger('entity_id');
            $table->unsignedBigInteger('attribute_id');

            $table->unsignedBigInteger('unsigned_integer_value', false)->nullable();
            $table->bigInteger('integer_value', false)->nullable();
            $table->boolean('boolean_value')->nullable();
            $table->double('double_value', 8, 2)->nullable();
            $table->string('string_value', 255)->nullable();
            $table->text('text_value')->nullable();
            $table->json('json_value')->nullable();
            $table->timestamp('timestamp_value', 0)->nullable();

            $table->index(['entity_id', 'attribute_id'], 'fk_index');
            $table->index(['entity_id', 'attribute_id', 'unsigned_integer_value'], 'fk_unsigned_integer_index');
            $table->index(['entity_id', 'attribute_id', 'integer_value'], 'fk_integer_index');
            $table->index(['entity_id', 'attribute_id', 'boolean_value'], 'fk_boolean_index');
            $table->index(['entity_id', 'attribute_id', 'double_value'], 'fk_double_index');
            $table->index(['entity_id', 'attribute_id', 'string_value'], 'fk_string_index');
//            $table->index(['entity_id', 'attribute_id', 'text_value'], 'text_index');
//            $table->index(['entity_id', 'attribute_id', 'json_value'], 'json_index');
            $table->index(['entity_id', 'attribute_id', 'timestamp_value'], 'fk_timestamp_index');

            $table->foreign('entity_id')->references('id')->on('elements_entities')->onDelete('cascade');
            $table->foreign('attribute_id')->references('id')->on('elements_attributes')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('elements_values');
    }
}
