<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Product;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Log;

class ProductController extends Controller
{
    // Menambahkan produk baru
    public function store(Request $request)
    {
        // Validasi input
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255',
            'description' => 'required|string',
            'price' => 'required|numeric',
            'stock' => 'required|numeric',
            'category_id' => 'required|exists:categories,id',
            'supplier_id' => 'required|exists:suppliers,id',
            'image_url' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
        ]);

        if ($validator->fails()) {
            return response()->json($validator->errors(), 400);
        }

        // Cek jika ada gambar yang di-upload
        if ($request->hasFile('image_url')) {
            $image = $request->file('image_url');
            $imagePath = $image->store('products', 'public');
            $image_url = asset('storage/' . $imagePath);
        } else {
            $image_url = asset('images/default.jpg'); // Gambar default
        }

        // Simpan produk ke database
        $product = Product::create([
            'name' => $request->name,
            'description' => $request->description,
            'price' => $request->price,
            'stock' => $request->stock,
            'image_url' => $image_url,
            'category_id' => $request->category_id,
            'supplier_id' => $request->supplier_id,
        ]);

        // Tambahkan nama kategori dan hapus ID kategori
        $product->category_name = $product->category ? $product->category->name : null;
        unset($product->category_id);

        return response()->json($product, 201);
    }

    // Menampilkan semua produk
    public function index()
    {
        $products = Product::with('category')->get();

        // Tambahkan nama kategori dan hapus ID kategori dari setiap produk
        return response()->json($products->map(function ($product) {
            // $product->category_name = $product->category ? $product->category->name : null;
            unset($product->category_id);
            return $product;
        }), 200);
    }

    // Menampilkan produk berdasarkan supplier_id
    public function getBySupplier($supplier_id)
{
    // Ambil produk berdasarkan supplier_id
    $products = Product::with('category')->where('supplier_id', $supplier_id)->get();

    // Cek jika produk tidak ditemukan
    if ($products->isEmpty()) {
        return response()->json(['message' => 'No products found for this supplier'], 404);
    }

    // Kembalikan response produk dengan nama kategori dan tanpa kategori_id
    return response()->json($products->map(function ($product) {
        unset($product->category_id);
        return $product;
    }), 200);
}


    // Menampilkan detail produk berdasarkan ID
    public function show($id)
    {
        $product = Product::with('category')->find($id);

        if (!$product) {
            return response()->json(['message' => 'Product not found'], 404);
        }

        // Tambahkan nama kategori dan hapus ID kategori
        $product->category_name = $product->category ? $product->category->name : null;
        unset($product->category_id);

        return response()->json($product, 200);
    }

    // Memperbarui produk
    public function update(Request $request, $id)
    {
        $product = Product::find($id);

        if (!$product) {
            return response()->json(['message' => 'Product not found'], 404);
        }

        $validator = Validator::make($request->all(), [
            'name' => 'nullable|string|max:255',
            'description' => 'nullable|string',
            'price' => 'nullable|numeric',
            'stock' => 'nullable|numeric',
            'category_id' => 'nullable|exists:categories,id',
            'supplier_id' => 'nullable|exists:suppliers,id',
            'image_url' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
        ]);

        if ($validator->fails()) {
            return response()->json($validator->errors(), 400);
        }

        // Update gambar jika ada
        if ($request->hasFile('image_url')) {
            // Hapus gambar lama jika ada
            if ($product->image_url && Storage::disk('public')->exists(str_replace(asset('storage/'), '', $product->image_url))) {
                Storage::disk('public')->delete(str_replace(asset('storage/'), '', $product->image_url));
            }

            $image = $request->file('image_url');
            $imagePath = $image->store('products', 'public');
            $product->image_url = asset('storage/' . $imagePath);
        }

        // Update data produk
        $product->update($request->only([
            'name', 'description', 'price', 'stock', 'category_id', 'supplier_id'
        ]));

        Log::info('Updated Product: ', $product->toArray());

        // Tambahkan nama kategori dan hapus ID kategori
        $product->category_name = $product->category ? $product->category->name : null;
        unset($product->category_id);

        return response()->json([
            'success' => true,
            'message' => 'Product updated successfully',
            'data' => $product,
        ], 200);
    }

    // Menghapus produk
    public function destroy($id)
    {
        $product = Product::find($id);

        if (!$product) {
            return response()->json(['message' => 'Product not found'], 404);
        }

        // Hapus gambar jika ada
        if ($product->image_url && Storage::disk('public')->exists(str_replace(asset('storage/'), '', $product->image_url))) {
            Storage::disk('public')->delete(str_replace(asset('storage/'), '', $product->image_url));
        }

        $product->delete();

        return response()->json(['message' => 'Product deleted successfully'], 200);
    }
}
