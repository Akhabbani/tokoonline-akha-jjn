<?php

namespace App\Http\Controllers;

use App\Models\Customer;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Produk;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class OrderController extends Controller
{
    private function customer(): Customer
    {
        return Customer::firstOrCreate(['user_id' => Auth::id()]);
    }

    private function refreshTotals(Order $order): void
    {
        $order->load('orderItems.produk');
        $total = 0;
        $weight = 0;

        foreach ($order->orderItems as $item) {
            $total += $item->harga * $item->quantity;
            $weight += ($item->produk->berat ?? 0) * $item->quantity;
        }

        $order->update(['total_harga' => $total, 'total_berat' => $weight]);
    }

    public function addToCart($id)
    {
        $produk = Produk::where('status', 'aktif')->findOrFail($id);
        $customer = $this->customer();

        if ($produk->stok < 1) {
            return back()->with('error', 'Stok produk sedang kosong.');
        }

        $pendingOrder = Order::where('customer_id', $customer->id)
            ->where('status', 'pending')->latest()->first();
        $currentQuantity = $pendingOrder
            ? OrderItem::where('order_id', $pendingOrder->id)->where('produk_id', $produk->id)->value('quantity')
            : 0;

        if (($currentQuantity ?? 0) + 1 > $produk->stok) {
            return back()->with('error', 'Jumlah produk di keranjang sudah mencapai stok yang tersedia.');
        }

        DB::transaction(function () use ($customer, $produk) {
            $order = Order::firstOrCreate(
                ['customer_id' => $customer->id, 'status' => 'pending'],
                ['total_harga' => 0, 'total_berat' => 0]
            );

            $item = OrderItem::firstOrNew([
                'order_id' => $order->id,
                'produk_id' => $produk->id,
            ]);

            if (($item->quantity ?? 0) + 1 > $produk->stok) {
                throw ValidationException::withMessages(['quantity' => 'Jumlah produk melebihi stok yang tersedia.']);
            }

            $item->quantity = ($item->quantity ?? 0) + 1;
            $item->harga = $produk->harga;
            $item->save();

            $this->refreshTotals($order);
        });

        return redirect()->route('order.cart')->with('success', 'Produk berhasil ditambahkan ke keranjang.');
    }

    public function viewCart()
    {
        $customer = $this->customer();
        $order = Order::with('orderItems.produk')
            ->where('customer_id', $customer->id)
            ->where('status', 'pending')
            ->latest()
            ->first();

        return view('v_order.cart', compact('order'));
    }

    public function updateCart(Request $request, $id)
    {
        $request->validate(['quantity' => 'required|integer|min:1']);
        $customer = $this->customer();
        $item = OrderItem::with(['order', 'produk'])
            ->whereKey($id)
            ->whereHas('order', function ($query) use ($customer) {
                $query->where('customer_id', $customer->id)->where('status', 'pending');
            })->firstOrFail();

        if ($request->quantity > $item->produk->stok) {
            return back()->with('error', 'Jumlah melebihi stok yang tersedia.');
        }

        $item->update(['quantity' => $request->quantity]);
        $this->refreshTotals($item->order);

        return back()->with('success', 'Jumlah produk berhasil diperbarui.');
    }

    public function removeFromCart($id)
    {
        $customer = $this->customer();
        $item = OrderItem::with('order')
            ->where('produk_id', $id)
            ->whereHas('order', function ($query) use ($customer) {
                $query->where('customer_id', $customer->id)->where('status', 'pending');
            })->firstOrFail();

        $order = $item->order;
        $item->delete();
        $this->refreshTotals($order);

        return back()->with('success', 'Produk berhasil dihapus dari keranjang.');
    }
}
