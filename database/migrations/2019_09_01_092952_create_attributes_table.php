<?php

use Click\Elements\Types\AttributeType;
use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateAttributesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('elements_attributes', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->string('key', 64)->unique();
            $table->enum('type', AttributeType::getTypes())->index();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('elements_properties');
    }
}
