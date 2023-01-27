<?php

namespace App;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class dailyReport extends Model
{
    use HasFactory;

    protected $fillable = [
      'project_id',
      'date',
      'The_scope_of_work',
      'supervisor_id',
  'workplace',
  'ref',
    'number_of_staff',
    'status',
    'note',
    'contentmanager'
        
    ];

    public function supervisor(){
      return $this->belongsto(User::Class,'supervisor_id');
    }

    public function project(){
      return $this->belongsto(project::Class,'project_id');
    }
    
    public function daily_productivity(){
      return $this->HasMany(daily_productivity::class,'daily_report_id');
    }


    public function users(){
      return $this->belongstomany(User::class,'attending_and_leavings')->withPivot(['user_id','daily_report_id','commitment','performance','time_difference','min']);
    }

    
    public function attachments(){
      return $this->HasMany(daily_report_attachment::class,'daily_report_id');
    }
}
