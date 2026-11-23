<?php
namespace App\Models;

use App\Models\Concerns\ScopedToDemoMode;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class DocumentExterne extends Model
{
    use ScopedToDemoMode;

    protected $table = 'documents_externes';

    protected $fillable = [
        'title', 'file_path', 'reference_type', 'reference_id', 'uploaded_by', 'notes',
    ];

    public function uploader(): BelongsTo
    {
        return $this->belongsTo(User::class, 'uploaded_by');
    }
}
