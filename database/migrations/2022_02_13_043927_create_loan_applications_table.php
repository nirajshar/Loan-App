<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateLoanApplicationsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('loan_applications', function (Blueprint $table) {
            $table->id();
            $table->decimal('amount', 10,2)->default(0);
            $table->text('description');
            $table->integer('repayment_period')->comment('IN WEEKS');
            $table->decimal('interest_percentage', 5,2);
            $table->enum('loan_application_status', ['SUBMITTED', 'PROCESSING', 'APPROVED', 'REJECTED', 'CLOSED']);
            $table->foreignId('user_id')->nullable()->constrained()->onDelete('set null');
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
        Schema::dropIfExists('loan_applications');
    }
}
