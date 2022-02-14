<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateLoansTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('loans', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->nullable()->constrained()->onDelete('set null');
            $table->string('loan_no')->unique();
            $table->foreignId('loan_application_id')->nullable()->constrained()->onDelete('set null');
            $table->decimal('amount_asked', 10,2);
            $table->integer('repayment_period_asked')->comment('IN WEEKS');
            $table->decimal('amount_approved', 10,2);
            $table->integer('repayment_period_approved')->comment('IN WEEKS');
            $table->decimal('principal_amount', 10,2);
            $table->decimal('interest_percentage', 5,2);
            $table->decimal('interest_amount', 10,2);
            $table->decimal('balance_amount', 10,2);
            $table->enum('loan_status', ['ACTIVE', 'CLOSED', 'SETTLED']);
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
        Schema::dropIfExists('loans');
    }
}
