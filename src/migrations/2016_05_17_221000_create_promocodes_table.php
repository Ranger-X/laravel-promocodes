<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Str;

class CreatePromocodesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create(config('promocodes.table', 'promocodes'), function (Blueprint $table) {
            $table->increments('id');

            $foreignModel = config('promocodes.foreign_model');
            if ($foreignModel)
            {
                // get a class name in short form (without namespace)
                $foreignClass = substr($foreignModel, strrpos($foreignModel, '\\') + 1);

                // hidden param
                $foreignColumnType = config('promocodes.foreign_type', 'integer');

                $table->{$foreignColumnType}(Str::snake($foreignClass) . '_id')->unsigned()->nullable();
            }

            $table->string('code', 32)->unique();
            $table->double('reward', 10, 2)->nullable();
            $table->boolean('is_used')->default(false);
            $table->dateTime('expired_at')->nullable();

            if ($foreignModel)
            {
                $instance = new $foreignModel;
                $foreignTable = $instance->getTable();

                $table->foreign(Str::snake($foreignClass) . '_id')->references('id')->on($foreignTable);
            }

            $table->index('is_used');
            $table->index('expired_at');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::drop(config('promocodes.table', 'promocodes'));
    }
}
