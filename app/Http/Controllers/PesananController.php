<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Models\Pesanan;
use App\Models\Product;
use App\Models\Supplier;
use Midtrans\Snap;
use Midtrans\Config;
use Exception;

class PesananController extends Controller
{
    public function __construct()
    {
        // Konfigurasi Midtrans
        Config::$clientKey = 'SB-Mid-client-ZUBzF67nnKr1QVSp';
        Config::$serverKey = 'SB-Mid-server-E4EnoD_Dadsp8CCfO_Mm7frW';
        Config::$isProduction = false;
        Config::$isSanitized = true;
        Config::$is3ds = true;
    }

    /**
     * Menampilkan daftar pesanan
     */
    public function index()
    {
        // Ambil semua pesanan
        $pesanans = DB::table('pesanan')
        ->join('products', 'pesanan.product_id', '=', 'products.id')
        ->select(
            'pesanan.id',
            'products.name as product_name',
            'pesanan.quantity',
            'pesanan.price',
            'pesanan.total_price',
            'pesanan.status',
            'pesanan.order_date'
        )
        ->get();

        // Mengembalikan data pesanan dalam format JSON
        return response()->json($pesanans);
    }

    public function getOrders()
    {
        $orders = DB::table('pesanan')
            ->join('products', 'pesanan.product_id', '=', 'products.id')
            ->select(
                'pesanan.id',
                'products.name as product_name',
                'pesanan.quantity',
                'pesanan.price',
                'pesanan.total_price',
                'pesanan.status',
                'pesanan.order_date'
            )
            ->get();

        return response()->json($orders);
    }


    /**
     * Handle Pesanan Creation and Payment
     */
    public function createPesanan(Request $request)
    {
    //     // Validasi data input pesanan
    //     $request->validate([
    //         'supplier_id' => 'required|exists:suppliers,id',
    //         'product_id' => 'required|exists:products,id',
    //         'quantity' => 'required|integer|min:1',
    //         'order_date' => 'required|date',
    //     ]);

    //     // Mendapatkan data produk berdasarkan ID produk
    //     $product = Product::find($request->product_id);
    //     $totalPrice = $product->price * $request->quantity; // Menghitung total harga berdasarkan kuantitas

    //     // Membuat pesanan baru
    //     $pesanan = Pesanan::create([
    //         'supplier_id' => $request->supplier_id,
    //         'product_id' => $request->product_id,
    //         'quantity' => $request->quantity,
    //         'price' => $product->price,
    //         'total_price' => $totalPrice,
    //         'order_date' => $request->order_date,
    //         'status' => 'pending',
    //     ]);

    //     // Generate payment request menggunakan Midtrans
    //     $order = [
    //         'transaction_details' => [
    //             'order_id' => 'order-' . $pesanan->id,
    //             'gross_amount' => $totalPrice, // Total harga pesanan
    //         ],
    //         'customer_details' => [
    //             'first_name' => 'Nama Pelanggan', // Ganti dengan nama pelanggan
    //             'email' => 'pelanggan@example.com', // Ganti dengan email pelanggan
    //         ],
    //     ];

    //     try {
    //         // Generate Snap token untuk pembayaran
    //         $snapToken = Snap::getSnapToken($order);

    //         // Mengembalikan snap token untuk digunakan di frontend
    //         return response()->json(['snap_token' => $snapToken, 'pesanan' => $pesanan]);
    //     } catch (Exception $e) {
    //         return response()->json(['error' => $e->getMessage()], 500);
    //     }
    // }

        try {
            // Validasi data input pesanan
            $request->validate([
                'supplier_id' => 'required|exists:suppliers,id',
                'product_id' => 'required|exists:products,id',
                'quantity' => 'required|integer|min:1',
                'order_date' => 'required|date',
            ]);

            // Mendapatkan data produk berdasarkan ID produk
            $product = Product::find($request->product_id);
            if (!$product) {
                return response()->json(['error' => 'Produk tidak ditemukan'], 404);
            }

            $totalPrice = $product->price * $request->quantity; // Menghitung total harga berdasarkan kuantitas

            // Membuat pesanan baru
            $pesanan = Pesanan::create([
                'supplier_id' => $request->supplier_id,
                'product_id' => $request->product_id,
                'quantity' => $request->quantity,
                'price' => $product->price,
                'total_price' => $totalPrice,
                'order_date' => $request->order_date,
                'status' => 'pending',
            ]);

            // Generate payment request menggunakan Midtrans
            $order = [
                'transaction_details' => [
                    'order_id' => 'order-' . $pesanan->id,
                    'gross_amount' => $totalPrice, // Total harga pesanan
                ],
                'customer_details' => [
                    'first_name' => 'Nama Pelanggan', // Ganti dengan nama pelanggan
                    'email' => 'pelanggan@example.com', // Ganti dengan email pelanggan
                ],
            ];

            // Generate Snap token untuk pembayaran
            $snapToken = Snap::getSnapToken($order);

            // Mengembalikan snap token untuk digunakan di frontend
            return response()->json(['snap_token' => $snapToken, 'pesanan' => $pesanan]);
        } catch (\Exception $e) {
            // Tangani error dan kembalikan respons JSON
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }

    /**
     * Store method untuk menangani permintaan POST dari Route::resource
     */
    public function store(Request $request)
    {
        // Panggil fungsi createPesanan dari controller yang sama
        return $this->createPesanan($request);
    }

    /**
     * Handle Midtrans Webhook untuk update status pesanan
     */
    public function midtransWebhook(Request $request)
    {
        $status = $request->input('transaction_status');
        $order_id = $request->input('order_id');
        
        // Mengambil pesanan berdasarkan order_id
        $pesanan = Pesanan::find($order_id);

        if ($pesanan) {
            // Memperbarui status pesanan berdasarkan status transaksi
            if ($status == 'settlement') {
                $pesanan->status = 'paid'; // Jika pembayaran berhasil
            } else {
                $pesanan->status = 'failed'; // Jika pembayaran gagal
            }
            $pesanan->save();
        }

        return response()->json(['message' => 'Webhook received']);
    }
}
