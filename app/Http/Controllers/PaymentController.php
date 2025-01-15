<?php

namespace App\Http\Controllers;

use App\Models\Payment;
use App\Models\Supplier;
use Illuminate\Http\Request;

class PaymentController extends Controller
{
    /**
     * Menyimpan data pembayaran yang baru.
     */
    public function store(Request $request)
    {
        // Validasi data pembayaran
        $request->validate([
            'order_id' => 'required|string|unique:payments',
            'supplier_id' => 'required|exists:suppliers,id',
            'gross_amount' => 'required|numeric',
            'payment_status' => 'required|string',
        ]);

        // Membuat pembayaran baru
        $payment = Payment::create([
            'order_id' => $request->order_id,
            'supplier_id' => $request->supplier_id,
            'gross_amount' => $request->gross_amount,
            'payment_status' => $request->payment_status,
        ]);

        return response()->json([
            'message' => 'Payment created successfully.',
            'payment' => $payment,
        ]);
    }

    /**
     * Menampilkan data pembayaran berdasarkan order_id.
     */
    public function show($order_id)
    {
        // Menampilkan data pembayaran berdasarkan order_id
        $payment = Payment::where('order_id', $order_id)->first();

        if (!$payment) {
            return response()->json(['error' => 'Payment not found'], 404);
        }

        return response()->json([
            'payment' => $payment,
        ]);
    }

    /**
     * Memperbarui status pembayaran berdasarkan order_id.
     */
    public function update(Request $request, $order_id)
    {
        // Validasi data update
        $request->validate([
            'payment_status' => 'required|string',
        ]);

        // Cari pembayaran berdasarkan order_id
        $payment = Payment::where('order_id', $order_id)->first();

        if (!$payment) {
            return response()->json(['error' => 'Payment not found'], 404);
        }

        // Update status pembayaran
        $payment->update([
            'payment_status' => $request->payment_status,
        ]);

        return response()->json([
            'message' => 'Payment status updated successfully.',
            'payment' => $payment,
        ]);
    }
    public function paymentNotification(Request $request)
{
    $payload = $request->getContent();
    $notification = json_decode($payload, true);

    // Ambil informasi transaksi dari Midtrans
    $transactionStatus = $notification['transaction_status'];
    $orderId = $notification['order_id'];

    // Cari data pembayaran berdasarkan order_id dari tabel payments
    $payment = Payment::where('order_id', $orderId)->first();

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
