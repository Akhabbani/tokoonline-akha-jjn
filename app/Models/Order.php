<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Order extends Model
{
    protected $table = 'orders';

    protected $fillable = [
        'customer_id', 'status', 'total_harga', 'noresi', 'kurir',
        'layanan_ongkir', 'biaya_ongkir', 'estimasi_ongkir',
        'total_berat', 'alamat', 'pos',
    ];

    public function customer()
    {
        return $this->belongsTo(Customer::class);
    }

    public function orderItems()
    {
        return $this->hasMany(OrderItem::class);
    }
}
