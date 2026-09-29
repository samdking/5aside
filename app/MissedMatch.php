<?php namespace App;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class MissedMatch extends Model
{
	use HasFactory;
	protected $fillable = ['date'];
	protected $casts = ['date' => 'datetime'];

	public $timestamps = false;

	public function players()
	{
		return $this->belongsToMany('App\Player');
	}
}
