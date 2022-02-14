<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateLoanAmortizationsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('loan_amortizations', function (Blueprint $table) {
            $table->id();
            $table->foreignId('loan_id')->nullable()->constrained()->onDelete('set null');
            $table->decimal('amount_to_pay', 10,2);
            $table->decimal('interest_amount_to_pay', 10,2);
            $table->decimal('principal_amount_to_pay', 10,2);
            $table->date('due_date');
            $table->decimal('amount_paid', 10,2)->nullable();
            $table->date('paid_date')->nullable();
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
        Schema::dropIfExists('loan_amortizations');
    }
}
