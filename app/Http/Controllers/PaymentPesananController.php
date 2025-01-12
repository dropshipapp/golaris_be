<?php

namespace App\Http\Controllers;

use App\Models\Pesanan;
use App\Models\PaymentPesanan;
use Illuminate\Http\Request;

class PaymentPesananController extends Controller
{
    // Menyimpan pembayaran pesanan
    public function store(Request $request)
    {
        $validatedData = $request->validate([
            'pesanan_id' => 'required|exists:pesanan,id', // pastikan pesanan_id valid
            'payment_method' => 'required|string',
            'amount' => 'required|numeric',
            'transaction_id' => 'required|string',
            'status' => 'required|in:pending,completed,failed',
            'payment_url' => 'nullable|url',
        ]);
    
        $paymentPesanan = PaymentPesanan::create([
            'pesanan_id' => $validatedData['pesanan_id'],
            'payment_method' => $validatedData['payment_method'],
            'amount' => $validatedData['amount'],
            'transaction_id' => $validatedData['transaction_id'],
            'status' => $validatedData['status'],
            'payment_url' => $validatedData['payment_url'] ?? null, // opsional
        ]);
    
        return response()->json(['message' => 'Payment successfully created!', 'data' => $paymentPesanan], 201);
    }
    
    // Webhook dari Midtrans
    public function midtransWebhook(Request $request)
    {
        $status = $request->input('transaction_status');
        $order_id = $request->input('order_id');
        
        // Mengambil data pembayaran berdasarkan pesanan_id (order_id)
        $payment = PaymentPesanan::where('pesanan_id', $order_id)->first();
        
        if ($payment) {
            // Memperbarui status pembayaran sesuai dengan status transaksi
            $payment->payment_status = $status;
            $payment->save();
            
            // Mengambil pesanan yang terkait
            $pesanan = Pesanan::find($order_id);
            if ($status == 'settlement') {
                $pesanan->status = 'paid'; // Jika pembayaran berhasil, ubah status pesanan
            } else {
                $pesanan->status = 'failed'; // Jika pembayaran gagal, ubah status pesanan
            }
            $pesanan->save();
        }

        return response()->json(['message' => 'Webhook received']);
    }
}
