<?php

namespace App\Http\Controllers;

use App\Models\Admin;
use App\Models\Supplier;
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
        Config::$isProduction = false;
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
            return response()->json(['snap_token' => $snapToken]);
        } catch (Exception $e) {
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }

    /**
     * Handle Login (Admin & Supplier)
     */
    public function login(Request $request)
    {
        // Validasi data login
        $request->validate([
            'email' => 'required|email',
            'password' => 'required',
        ]);

        // Cari di tabel admin
        $admin = Admin::where('email', $request->email)->first();
        if ($admin && Hash::check($request->password, $admin->password)) {
            return response()->json([
                'message' => 'Login successful',
                'type' => 'admin',
                'user' => $admin,
            ]);
        }

        // Cari di tabel supplier jika bukan admin
        $supplier = Supplier::where('email', $request->email)->first();
        if ($supplier && Hash::check($request->password, $supplier->password)) {
            return response()->json([
                'message' => 'Login successful',
                'type' => 'supplier',
                'user' => $supplier,
            ]);
        }

        // Jika tidak ditemukan di kedua tabel
        return response()->json(['error' => 'Invalid credentials'], 401);
    }

    /**
     * Handle Admin Registration
     */
    public function registerAdmin(Request $request)
    {
        // Validasi data input
        $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:admins,email',
            'password' => 'required|string|min:6|confirmed',
        ]);

        // Buat admin baru
        $admin = Admin::create([
            'name' => $request->name,
            'email' => $request->email,
            'password' => bcrypt($request->password),
        ]);

        return response()->json(['message' => 'Admin registered successfully', 'admin' => $admin]);
    }




    /**
 * Handle Midtrans Payment Notification
 */
public function paymentNotification(Request $request)
{
    $payload = $request->getContent();
    $notification = json_decode($payload, true);

    // Konfigurasi Midtrans
    \Midtrans\Config::$serverKey = 'SB-Mid-server-E4EnoD_Dadsp8CCfO_Mm7frW';
    \Midtrans\Config::$isProduction = false;

    // Ambil informasi transaksi dari Midtrans
    $transactionStatus = $notification['transaction_status'];
    $orderId = $notification['order_id'];

    // Cari data pembayaran berdasarkan order_id
    $payment = \App\Models\Payment::where('order_id', $orderId)->first();

    // Jika pembayaran tidak ditemukan
    if (!$payment) {
        return response()->json(['error' => 'Payment not found'], 404);
    }

    // Update status pembayaran berdasarkan status dari Midtrans
    if ($transactionStatus === 'settlement') {
        $payment->update(['payment_status' => 'paid']);
    } elseif ($transactionStatus === 'pending') {
        $payment->update(['payment_status' => 'pending']);
    } elseif ($transactionStatus === 'expire') {
        $payment->update(['payment_status' => 'expired']);
    } elseif ($transactionStatus === 'cancel') {
        $payment->update(['payment_status' => 'canceled']);
    }

    return response()->json(['message' => 'Payment status updated successfully']);
}

}



