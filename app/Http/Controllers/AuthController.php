<?php

namespace App\Http\Controllers;

use App\Models\Supplier;
use App\Models\Payment;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Midtrans\Snap;
use Midtrans\Config;
use Exception;

class AuthController extends Controller
{
    public function __construct()
    {
        // Konfigurasi Midtrans
        Config::$clientKey = 'SB-Mid-client-ZUBzF67nnKr1QVSp';
        Config::$serverKey = 'SB-Mid-server-E4EnoD_Dadsp8CCfO_Mm7frW';
        Config::$isProduction = false; // Mode Sandbox
        Config::$isSanitized = true;
        Config::$is3ds = true;
    }

    /**
     * Handle Supplier Registration and Payment
     */
    public function register(Request $request)
    {
        // Validasi data input
        $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:suppliers,email',
            'password' => 'required|string|min:6|confirmed',
        ]);

        // Buat supplier baru
        $supplier = Supplier::create([
            'name' => $request->name,
            'email' => $request->email,
            'password' => bcrypt($request->password),
            'payment_status' => 'pending', // Status pembayaran awal adalah pending
        ]);

        // Generate payment request menggunakan Midtrans
        $order = [
            'transaction_details' => [
                'order_id' => 'order-' . time(),
                'gross_amount' => 30000, // Biaya registrasi
            ],
            'customer_details' => [
                'first_name' => $request->name,
                'email' => $request->email,
            ],
        ];

        try {
            // Generate Snap token
            $snapToken = Snap::getSnapToken($order);

            // Simpan entri pembayaran
            $payment = Payment::create([
                'supplier_id' => $supplier->id,
                'order_id' => 'order-' . time(),
                'gross_amount' => 30000,
                'payment_status' => 'pending', // Status pembayaran sementara
            ]);

            // Kembalikan Snap token ke front-end
            return response()->json(['snap_token' => $snapToken]);
        } catch (Exception $e) {
            return response()->json(['error' => 'Error generating payment token: ' . $e->getMessage()], 500);
        }
    }

    /**
     * Handle Supplier Login
     */
    public function login(Request $request)
    {
        // Validasi data login
        $request->validate([
            'email' => 'required|email',
            'password' => 'required',
        ]);

        // Cari supplier berdasarkan email
        $supplier = Supplier::where('email', $request->email)->first();

        // Cek apakah supplier ditemukan dan password sesuai
        if ($supplier && Hash::check($request->password, $supplier->password)) {
            return response()->json([
                'message' => 'Login successful',
                'supplier' => $supplier
            ]);
        }

        return response()->json(['error' => 'Invalid credentials'], 401);
    }

    /**
     * Handle Midtrans Payment Notification (Callback)
     */
    public function paymentNotification(Request $request)
    {
        $notification = $request->all();

        // Verifikasi status transaksi
        $transactionStatus = $notification['transaction_status']; // 'capture', 'settlement', 'pending', dll.
        $orderId = $notification['order_id'];

        // Cari entri pembayaran berdasarkan order_id
        $payment = Payment::where('order_id', $orderId)->first();

        if ($payment) {
            // Update status pembayaran berdasarkan hasil callback
            if ($transactionStatus == 'settlement') {
                $payment->payment_status = 'paid';
                $payment->save();
                
                // Update status supplier
                $supplier = $payment->supplier;
                $supplier->payment_status = 'paid';
                $supplier->save();
            } elseif ($transactionStatus == 'pending') {
                $payment->payment_status = 'pending';
                $payment->save();
            } else {
                $payment->payment_status = 'failed';
                $payment->save();
            }
        }

        return response()->json(['message' => 'Payment status updated successfully']);
    }
}
