<?php

namespace App\Models;

use Carbon\Carbon;
use Carbon\CarbonPeriod;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Leave extends Model
{
    use HasFactory;
    protected $guarded = [];

    protected $casts = [
        'start_date' => 'date:Y-m-d',
        'end_date' => 'date:Y-m-d',
    ];

    public function employee()
    {
        return $this->belongsTo(Employee::class);
    }

    public function attachments()
    {
        return $this->morphMany(Attachment::class, 'model');
    }

    public function scopeRelatedEmployees(Builder $query)
    {
        return $query->whereRelation('employee', 'employee_id', auth()->user()->employee_id);
    }

    public function scopePending(Builder $query)
    {
        return $query->where('status', 'pending');
    }

    protected static function boot()
    {
        parent::boot();

        static::deleting(function ($record) {
            $record->attachments()->get()->each->delete();
        });
    }

    public function getLeaveDaysAttribute()
    {
        $startDate = Carbon::parse($this->start_date);
        $endDate = Carbon::parse($this->end_date);
        $period = CarbonPeriod::create($startDate, $endDate);
        $actualLeaveDays = 0;
        foreach ($period as $date) {
            if (
                $date->dayOfWeek !== Carbon::FRIDAY &&
                !Holiday::where('is_rest_day', false)->pluck('date')->contains($date)
            ) {
                $actualLeaveDays++;
            }
        }
        return $actualLeaveDays;
    }
}
