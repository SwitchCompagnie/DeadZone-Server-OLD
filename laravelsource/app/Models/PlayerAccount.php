<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PlayerAccount extends Model
{
    protected $table = 'player_accounts';
    protected $primaryKey = 'player_id';
    public $incrementing = false;
    protected $keyType = 'string';
    public $timestamps = false;

    protected $fillable = [
        'player_id',
        'hashed_password',
        'email',
        'display_name',
        'avatar_url',
        'created_at',
        'last_login',
        'country_code',
        'server_metadata_json',
        'player_objects_json',
        'neighbor_history_json',
        'inventory_json',
        'pay_vault_json'
    ];

    protected $casts = [
        'created_at' => 'integer',
        'last_login' => 'integer',
        'server_metadata_json' => 'array',
        'player_objects_json' => 'array',
        'neighbor_history_json' => 'array',
        'inventory_json' => 'array',
        'pay_vault_json' => 'array'
    ];
}