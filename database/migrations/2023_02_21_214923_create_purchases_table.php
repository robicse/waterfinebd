<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('purchases', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->string('purchase_date',100)->nullable();
            $table->bigInteger('store_id')->unsigned()->nullable();
            $table->foreign('store_id')->references('id')->on('stores')->onDelete('cascade');
            $table->bigInteger('supplier_id')->unsigned()->nullable();
            $table->foreign('supplier_id')->references('id')->on('suppliers')->onDelete('cascade');
            $table->float('total_quantity',16,2)->default(0);
            $table->float('sub_total',16,2)->default(0);
            $table->enum('discount_type', ['Flat','Percent']);
            $table->float('discount_percent',16,2)->nullable()->default(0);
            $table->float('discount_amount',16,2)->default(0);
            $table->float('after_discount',16,2)->default(0);
            $table->float('total_vat',16,2)->default(0);
            $table->float('grand_total',16,2)->default(0);
            $table->bigInteger('payment_type_id')->nullable();
            $table->float('paid_amount',16,2)->default(0);
            $table->float('due_amount',16,2)->default(0);
            $table->float('total_sale_price',16,2)->default(0);
            $table->tinyInteger('status')->default(1);
            $table->bigInteger('created_by_user_id')->unsigned()->nullable();
            $table->foreign('created_by_user_id')->references('id')->on('users')->onDelete('cascade');
            $table->bigInteger('updated_by_user_id')->unsigned()->nullable();
            $table->foreign('updated_by_user_id')->references('id')->on('users')->onDelete('cascade');
            $table->softDeletes();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('purchases');
    }
};
