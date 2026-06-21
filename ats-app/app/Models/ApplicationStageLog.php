<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ApplicationStageLog extends Model
{
    protected $fillable = [
        'application_id',
        'pipeline_stage_id',
        'entered_at',
    ];

    protected function casts(): array
    {
        return [
            'entered_at' => 'datetime',
        ];
    }

    public function application(): BelongsTo
    {
        return $this->belongsTo(Application::class);
    }

    public function stage(): BelongsTo
    {
        return $this->belongsTo(PipelineStage::class, 'pipeline_stage_id');
    }
}
