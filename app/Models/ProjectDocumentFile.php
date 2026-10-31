<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * The database copy of an uploaded project document (base64), kept in its own
 * table so listing documents never loads the file contents.
 */
class ProjectDocumentFile extends Model
{
    protected $fillable = [
        'project_document_id',
        'data',
    ];

    protected $hidden = [
        'data',
    ];

    public function document(): BelongsTo
    {
        return $this->belongsTo(ProjectDocument::class, 'project_document_id');
    }
}
