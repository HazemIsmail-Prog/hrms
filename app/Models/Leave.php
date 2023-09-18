<?php

namespace App\Models;

use Carbon\Carbon;
use Carbon\CarbonPeriod;
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

    public function attachments()
    {
        return $this->morphMany(Attachment::class, 'model');
    }
    
    public function getLeaveDaysAttribute()
    {

        $startDate = Carbon::parse($this->start_date);
        $endDate = Carbon::parse($this->end_date);

        // Create a date range from the start date to the end date
        $period = CarbonPeriod::create($startDate, $endDate);

        // Initialize a counter variable to keep track of the days excluding Fridays
        $actualLeaveDays = 0;

        // Loop through each date in the range and exclude Fridays
        foreach ($period as $date) {
            // dump($date);
            if ($date->dayOfWeek !== Carbon::FRIDAY && !Holiday::where('is_rest_day', false)->pluck('date')->contains($date)) {
                $actualLeaveDays++;
            }
        }

        return $actualLeaveDays;
    }

}
