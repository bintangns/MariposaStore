<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('products', function (Blueprint $table) {
            $table->foreignId('category_id')->nullable()->after('category')->constrained()->nullOnDelete();
        });

        // Backfill: turn each distinct existing category string into a master category row.
        $names = DB::table('products')->whereNotNull('category')->distinct()->pluck('category');
        foreach ($names as $name) {
            $slug = Str::slug($name);
            $categoryId = DB::table('categories')->where('slug', $slug)->value('id');
            if (! $categoryId) {
                $categoryId = DB::table('categories')->insertGetId([
                    'name'       => ucfirst($name),
                    'slug'       => $slug,
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
            }
            DB::table('products')->where('category', $name)->update(['category_id' => $categoryId]);
        }

        Schema::table('products', function (Blueprint $table) {
            $table->dropColumn('category');
        });
    }

    public function down(): void
    {
        Schema::table('products', function (Blueprint $table) {
            $table->string('category')->default('rank')->after('image');
        });

        foreach (DB::table('products')->whereNotNull('category_id')->get() as $product) {
            $name = DB::table('categories')->where('id', $product->category_id)->value('name');
            if ($name) {
                DB::table('products')->where('id', $product->id)->update(['category' => $name]);
            }
        }

        Schema::table('products', function (Blueprint $table) {
            $table->dropConstrainedForeignId('category_id');
        });
    }
};
