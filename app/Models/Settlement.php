<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Settlement extends Model
{
    use HasFactory;
    protected $guarded = [];


    public function attachments()
    {
        return $this->morphMany(Attachment::class, 'model');
    }

    protected static function boot()
    {
        parent::boot();

        static::deleting(function ($record) {
            $record->attachments()->get()->each->delete();
        });
    }

    public function getAmountOrDaysAttribute(){
        return $this->type === 'leave' ? $this->days: $this->amount  ;
    }
}
