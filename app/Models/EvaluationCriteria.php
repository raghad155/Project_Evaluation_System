<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class EvaluationCriteria extends Model
{
    protected $fillable = [
        'name',
        'description',
        'weight'
    ];
    protected $table = 'evaluation_criteria';
}