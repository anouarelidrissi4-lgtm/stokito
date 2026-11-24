<?php

namespace App\Models;

use App\Models\Concerns\ScopedToDemoMode;
use Illuminate\Database\Eloquent\Model;

/** One AI call (invoice scan), for the monthly limit and the cost follow-up. */
class AiScan extends Model
{
    use ScopedToDemoMode;

    protected $fillable = ['user_id', 'kind', 'status', 'input_tokens', 'output_tokens', 'cost_usd', 'error'];

    public static function usedThisMonth(string $kind = 'facture_fournisseur'): int
    {
        return static::where('kind', $kind)->where('status', 'ok')->where('created_at', '>=', now()->startOfMonth())->count();
    }
}
