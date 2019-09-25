<?php

use Click\Elements\PropertyType;
use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreatePropertiesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('elements_properties', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->string('key', 64)->index();
            $table->enum('type', PropertyType::getTypes())->index();
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
