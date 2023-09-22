<?php

namespace App\Rules;

use App\Models\Leave;
use Carbon\CarbonPeriod;
use Closure;
use Illuminate\Contracts\Validation\DataAwareRule;
use Illuminate\Contracts\Validation\ValidationRule;

class OverlappingLeavePeriods implements DataAwareRule, ValidationRule
{
    protected $ownerId = [];
    protected $start_date = [];
    protected $end_date = [];


    public function setData(array $data): static
    {
        $this->ownerId = $data['ownerRecord']['id'];
        $this->start_date = $data['mountedTableActionsData'][0]['start_date'];
        $this->end_date = $data['mountedTableActionsData'][0]['end_date'];

        // $period = CarbonPeriod::create($this->start_date, $this->end_date); 


        return $this;
    }
    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        $singleDateCheck = Leave::query()
            ->where('employee_id', $this->ownerId)
            ->where(function ($query) use ($value) {
                $query->whereDate('start_date', '<=', $value)->whereDate('end_date', '>=', $value);
            })->count();

        $intersectionCheck = Leave::query()
            ->where('employee_id', $this->ownerId)
            ->where(function ($query) {
                $query->whereBetween('start_date', [$this->start_date, $this->end_date])->orWhereBetween('end_date', [$this->start_date, $this->end_date]);
            })->count();



        if ($singleDateCheck > 0) {
            $fail('The :attribute overlaps an old leave');
        }
        if ($intersectionCheck > 0) {
            $fail('This leave peiod has intersection leave');
        }
    }
}
