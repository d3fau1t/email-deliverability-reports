<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Email extends Model
{
    use HasFactory;

    protected $fillable = [
        'internal_reference',
        'message_id',
        'to',
        'recipient_name',
        'subject',
        'status',
        'error_message',
        'sender_email',
        'sender_name',
        'sender_domain',
        'body'
    ];

    protected $casts = [
        'body' => 'encrypted',
    ];
}
