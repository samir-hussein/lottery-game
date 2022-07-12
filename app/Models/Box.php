<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Box extends Model
{
    use HasFactory;

    protected $table = 'boxs';

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'player_id',
        'admin_id',
        'price',
        'paid',
        'estimate_price',
    ];

    public function items()
    {
        return $this->hasMany(BoxItemList::class);
    }

    public function player()
    {
        return $this->belongsTo(Player::class);
    }
}
