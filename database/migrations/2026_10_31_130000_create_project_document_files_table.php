<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Storage;

/**
 * Uploaded project documents lived only on the server disk, which the host wipes on
 * every restart and redeploy, so a document opened the next day came up as a blank
 * page. A copy of each file is now kept in the database as well.
 */
return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasColumn('project_documents', 'mime_type')) {
            Schema::table('project_documents', function (Blueprint $table) {
                $table->string('mime_type', 100)->nullable();
            });
        }

        if (!Schema::hasTable('project_document_files')) {
            Schema::create('project_document_files', function (Blueprint $table) {
                $table->id();
                $table->foreignId('project_document_id')->unique()->constrained('project_documents')->cascadeOnDelete();
                $table->longText('data'); // base64 of the file
                $table->timestamps();
            });
        }

        // Files still on the disk get their database copy now. Blank placeholders are
        // removed so the viewer reports the file as gone instead of showing an empty page.
        $disk = Storage::disk('public');
        DB::table('project_documents')
            ->whereNotIn('id', DB::table('project_document_files')->select('project_document_id'))
            ->select(['id', 'file_path'])
            ->orderBy('id')
            ->each(function ($doc) use ($disk) {
                if (!$doc->file_path || !$disk->exists($doc->file_path)) {
                    return;
                }

                $contents = $disk->get($doc->file_path);
                if ($this->isBlankPlaceholder($contents)) {
                    $disk->delete($doc->file_path);
                    return;
                }

                DB::table('project_document_files')->insert([
                    'project_document_id' => $doc->id,
                    'data' => base64_encode($contents),
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
            });
    }

    /**
     * The old viewer wrote a tiny one-page PDF with nothing on the page in place of a
     * missing file (a few slightly different versions of it exist).
     */
    private function isBlankPlaceholder(string $contents): bool
    {
        return strlen($contents) < 1024
            && str_starts_with($contents, '%PDF')
            && str_contains($contents, '/Resources<<>>')
            && !str_contains($contents, '/Contents');
    }

    public function down(): void
    {
        Schema::dropIfExists('project_document_files');

        if (Schema::hasColumn('project_documents', 'mime_type')) {
            Schema::table('project_documents', function (Blueprint $table) {
                $table->dropColumn('mime_type');
            });
        }
    }
};
