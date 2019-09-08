<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateEntityPropertiesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('entity_properties', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->unsignedBigInteger('entity_id');
            $table->unsignedBigInteger('property_id');

            $table->boolean('boolean_value')->nullable();
            $table->integer('integer_value')->nullable();
            $table->double('double_value')->nullable();
            $table->string('string_value')->nullable();
            $table->text('text_value')->nullable();
            $table->json('json_value')->nullable();

            $table->timestamps();

            $table->index(['entity_id', 'property_id'], 'fk_index');
            $table->index(['entity_id', 'property_id', 'boolean_value'], 'fk_boolean_index');
            $table->index(['entity_id', 'property_id', 'integer_value'], 'fk_integer_index');
            $table->index(['entity_id', 'property_id', 'float_value'], 'fk_float_index');
            $table->index(['entity_id', 'property_id', 'string_value'], 'fk_string_index');
//            $table->index(['entity_id', 'property_id', 'text_value'], 'text_index');
//            $table->index(['entity_id', 'property_id', 'json_value'], 'json_index');
        });

//        $this->addFullTextIndex('entity_properties', ['text_value', 'string_value']);
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('entity_properties');
    }

    protected function addFullTextIndex($table, $columns, $index = null)
    {
        if(!$index) {
            $index = implode('_', $columns) . '_fulltext_index';
        }

        DB::statement(sprintf('ALTER TABLE %s ADD FULLTEXT %s (%s)', $table, $index, implode(', ', $columns)));
    }
}
