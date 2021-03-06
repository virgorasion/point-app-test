<?php

namespace Point\Framework\Models;

use Illuminate\Database\Eloquent\Model;

use Point\Core\Traits\ByTrait;

class Formulir extends Model
{
    protected $table = 'formulir';

    use ByTrait;

    /**
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function approvalTo()
    {
        return $this->belongsTo('Point\Core\Models\User', 'approval_to');
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function canceledBy()
    {
        return $this->belongsTo('Point\Core\Models\User', 'canceled_by');
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\MorphTo
     */
    public function formulirable()
    {
        return $this->morphTo();
    }

    public function scopeNotArchived($q, $form_number = 0)
    {
        $q->whereNotNull('form_number');
        if ($form_number) {
            $q->where('form_number', '=', $form_number);
        }
    }

    public function scopeArchived($q, $form_number = 0)
    {
        $q->whereNull('form_number');
        if ($form_number) {
            $q->where('archived', '=', $form_number);
        }
    }

    public function scopeNotCanceled($q)
    {
        $q->where('form_status', '!=', -1);
    }

    public function scopeCanceled($q)
    {
        $q->where('form_status', '!=', -1);
    }

    public function scopeOpen($q)
    {
        $q->where('form_status', '=', 0);
    }

    public function scopeClose($q)
    {
        $q->where('form_status', '=', 1);
    }

    public function scopeApprovalPending($q)
    {
        $q->where('approval_status', '=', 0);
    }

    public function scopeApprovalApproved($q)
    {
        $q->where('approval_status', '=', 1);
    }

    public function scopeApprovalRejected($q)
    {
        $q->where('approval_status', '=', -1);
    }
}
