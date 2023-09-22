<?php

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Employee extends Model
{
    use HasFactory;
    protected $guarded = [];

    public function user()
    {
        return $this->hasOne(User::class);
    }
    public function department()
    {
        return $this->belongsTo(Department::class);
    }

    public function leaves()
    {
        return $this->hasMany(Leave::class);
    }
    public function increments()
    {
        return $this->hasMany(Increment::class);
    }
    public function settlements()
    {
        return $this->hasMany(Settlement::class);
    }

    public function attachments()
    {
        return $this->morphMany(Attachment::class, 'model');
    }

    protected static function boot()
    {
        parent::boot();

        static::deleting(function ($record) {
            $record->leaves()->get()->each->delete();
            $record->increments()->get()->each->delete();
            $record->settlements()->get()->each->delete();
            $record->attachments()->get()->each->delete();
        });
    }


    public function getSalaryAttribute()
    {
        return $this->initialSalary + $this->increments->sum('amount');;
    }

    protected function status(): Attribute
    {
        return Attribute::make(
            get: fn ($value) => ucfirst($value),
            // set: fn ($value) => lowe $value * 1000,
        );
    }
    protected function initSalary(): Attribute
    {
        return Attribute::make(
            get: fn ($value) => $value / 1000,
            set: fn ($value) => $value * 1000,
        );
    }

    public function getNetWorkingDaysAttribute($date = null)
    {
        $date = $date ?? today();

        $join_date = Carbon::parse($this->joinDate ?? null);
        $today = Carbon::parse($date ?? null);

        if($join_date > $today){
            return 0;
        }

        $diffInDays = $today->diffInDays($join_date);
        $netWorkingDays = $diffInDays - $this->leaves->where('type', 'unpaid')->sum('leaveDays') + 1;

        return $netWorkingDays;
    }

    public function getIndemnityAttribute($date = null)
    {
        $indemnity = 0;

        $date = $date ?? today();

        if ($this->getNetWorkingDaysAttribute($date) <= 1825) // less then 5 years
        {
            $indemnity = ($this->getNetWorkingDaysAttribute($date) / 365 * 15) * $this->salary / 26;
        }

        if ($this->getNetWorkingDaysAttribute($date) > 1825) // more than 5 years
        {
            $first_5_years = 75 * $this->salary / 26;
            $remaining_days = $this->getNetWorkingDaysAttribute($date) - 1825;
            $more_than_5_years = $remaining_days / 365 * $this->salary;
            $indemnity = $first_5_years + $more_than_5_years;
        }


        return $indemnity;
    }

    public function totalLeaveDaysToDateExcludingHolidaysAndFridays($type, $date)
    {
        // return 0;
        return $this->leaves->count() > 0 ? $this->leaves->sum('leaveDays') : 0;
    }

    public function getLeaveBalanceDaysAttribute($date = null)
    {
        $date = $date ?? today();

        return ($this->netWorkingDays / 365 * 30)
            - $this->totalLeaveDaysToDateExcludingHolidaysAndFridays('paid', $date)
            - $this->init_leave_taken_balance;
    }

    public function getLeaveBalanceAmountAttribute($date = null)
    {
        $date = $date ?? today();

        return $this->salary / 26 * $this->leaveBalanceDays;
    }
}
