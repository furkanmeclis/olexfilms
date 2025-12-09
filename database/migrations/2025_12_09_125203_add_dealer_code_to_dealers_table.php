<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;
use App\Models\Dealer;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('dealers', function (Blueprint $table) {
            $table->string('dealer_code', 8)->unique()->nullable()->after('id');
        });

        // Mevcut kayıtlar için dealer_code üret
        $dealers = Dealer::whereNull('dealer_code')->get();
        foreach ($dealers as $dealer) {
            do {
                $code = strtoupper(Str::random(8));
            } while (Dealer::where('dealer_code', $code)->exists());

            $dealer->update(['dealer_code' => $code]);
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('dealers', function (Blueprint $table) {
            $table->dropColumn('dealer_code');
        });
    }
};
