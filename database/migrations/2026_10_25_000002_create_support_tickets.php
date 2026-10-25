<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
return new class extends Migration {
    public function up(): void {
        Schema::create('support_tickets', function (Blueprint $t) {
            $t->id(); $t->string('name'); $t->string('subject'); $t->text('message');
            $t->string('priority')->default('normal'); $t->string('status')->default('open');
            $t->text('reply_note')->nullable();
            $t->foreignId('user_id')->nullable()->constrained()->nullOnDelete();
            $t->timestamps();
        });
    }
    public function down(): void { Schema::dropIfExists('support_tickets'); }
};
