<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Combination extends Model
{
	public $timestamps = false;
	protected $fillable = ['string', 'size', 'scored', 'complete_team'];

	public function players()
	{
		return $this->belongsToMany(Player::class);
	}
}
