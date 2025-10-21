<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Support\Str;

class FormSubmissions extends Model
{
    use HasFactory;

    protected $fillable = ['code', 'form_id', 'user_id', 'status', 'email'];

    public function getRouteKeyName()
    {
        return 'code';
    }

    public function generateCode()
    {
        do {
            $code = (string) Str::uuid();
        } while (self::where('code', $code)->exists());
    
        $this->code = $code;
    }

    public function form()
    {
        return $this->belongsTo(Form::class);
    }

    public function answers()
    {
        return $this->hasMany(QuestionAnswer::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
