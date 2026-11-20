@extends('v_layouts.app')

@section('content')
<div class="col-md-12">
    <div class="order-summary clearfix">
        <div class="section-title">
            <p>KERANJANG</p>
            <h3 class="title">Keranjang Belanja</h3>
        </div>

        @if (session('success'))
            <div class="alert alert-success">{{ session('success') }}</div>
        @endif
        @if (session('error'))
            <div class="alert alert-danger">{{ session('error') }}</div>
        @endif

        @if ($order && $order->orderItems->isNotEmpty())
            <div class="table-responsive">
                <table class="shopping-cart-table table">
                    <thead>
                        <tr>
                            <th>Produk</th>
                            <th></th>
                            <th class="text-center">Harga</th>
                            <th class="text-center">Jumlah</th>
                            <th class="text-center">Subtotal</th>
                            <th></th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($order->orderItems as $item)
                            <tr>
                                <td class="thumb">
                                    <img src="{{ asset('storage/img-produk/thumb_sm_' . ($item->produk->foto ?? 'no-image.jpg')) }}" alt="{{ $item->produk->nama_produk ?? 'Produk' }}">
                                </td>
                                <td class="details">
                                    <strong>{{ $item->produk->nama_produk }}</strong>
                                    <ul>
                                        <li><span>Berat: {{ $item->produk->berat }} gram</span></li>
                                        <li><span>Stok: {{ $item->produk->stok }}</span></li>
                                    </ul>
                                </td>
                                <td class="price text-center">Rp {{ number_format($item->harga, 0, ',', '.') }}</td>
                                <td class="qty text-center">
                                    <form action="{{ route('order.updateCart', $item->id) }}" method="POST">
                                        @csrf
                                        <input type="number" name="quantity" min="1" max="{{ $item->produk->stok }}" value="{{ $item->quantity }}" style="width:65px">
                                        <button type="submit" class="btn btn-sm btn-warning">Update</button>
                                    </form>
                                </td>
                                <td class="total text-center">
                                    <strong>Rp {{ number_format($item->harga * $item->quantity, 0, ',', '.') }}</strong>
                                </td>
                                <td class="text-right">
                                    <form action="{{ route('order.remove', $item->produk_id) }}" method="POST" onsubmit="return confirm('Hapus produk ini dari keranjang?')">
                                        @csrf
                                        <button type="submit" class="main-btn icon-btn" aria-label="Hapus produk"><i class="fa fa-close"></i></button>
                                    </form>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                    <tfoot>
                        <tr>
                            <td colspan="4" class="text-right"><strong>Total Belanja</strong></td>
                            <td colspan="2"><strong class="primary-color">Rp {{ number_format($order->total_harga, 0, ',', '.') }}</strong></td>
                        </tr>
                        <tr>
                            <td colspan="4" class="text-right"><strong>Total Berat</strong></td>
                            <td colspan="2">{{ number_format($order->total_berat) }} gram</td>
                        </tr>
                    </tfoot>
                </table>
            </div>
            <div class="pull-right">
                <a href="{{ route('ongkir.index') }}" class="primary-btn">Cek Ongkos Kirim</a>
            </div>
        @else
            <p>Keranjang belanja masih kosong.</p>
            <a href="{{ route('produk.all') }}" class="primary-btn">Lihat Produk</a>
        @endif
    </div>
</div>
@endsection
