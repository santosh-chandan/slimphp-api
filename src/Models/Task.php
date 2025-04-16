<?php
/**
 * Task API Model
 * Author: Santoshchandan.it@gmail.com
*/

namespace Santoshchandan\TasksApi\Models;

use Illuminate\Database\Eloquent\Model;

class Task extends Model
{
    protected $table = 'tasks';

    protected $fillable = ['title', 'description', 'completed'];

    public $timestamps = false; // Uses created_at / updated_at
}
