<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Product;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;

class ProductController extends Controller
{
    // Menambahkan produk baru
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255',
            'description' => 'required|string',
            'price' => 'required|numeric',
            'stock' => 'required|numeric',
            'category_id' => 'required|exists:categories,id',
            'image_url' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
        ]);

        if ($validator->fails()) {
            return response()->json($validator->errors(), 400);
        }

        // Jika ada gambar yang di-upload
        if ($request->hasFile('image_url')) {
            $imagePath = $request->file('image_url')->store('products', 'public');
            $image_url = asset('storage/' . $imagePath);
        } else {
            $image_url = $request->image_url ?? 'path_to_image.jpg'; // Default gambar
        }

        // Simpan produk ke database
        $product = Product::create([
            'name' => $request->name,
            'description' => $request->description,
            'price' => $request->price,
            'stock' => $request->stock,
            'image_url' => $image_url,
            'category_id' => $request->category_id,
        ]);

        return response()->json($product, 201);
    }

    // Menampilkan semua produk
    public function index()
    {
        $products = Product::all();
        return response()->json($products, 200);
    }

    // Menampilkan detail produk berdasarkan ID
    public function show($id)
    {
        $product = Product::find($id);

        if (!$product) {
            return response()->json(['message' => 'Product not found'], 404);
        }

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
            'image_url' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
        ]);

        if ($validator->fails()) {
            return response()->json($validator->errors(), 400);
        }

        if ($request->hasFile('image_url')) {
            // Hapus gambar lama jika ada
            if ($product->image_url && Storage::disk('public')->exists(str_replace(asset('storage/'), '', $product->image_url))) {
                Storage::disk('public')->delete(str_replace(asset('storage/'), '', $product->image_url));
            }

            $imagePath = $request->file('image_url')->store('products', 'public');
            $product->image_url = asset('storage/' . $imagePath);
        }

        $product->update($request->only(['name', 'description', 'price', 'stock', 'category_id']));

        return response()->json([
            'success' => true,
            'message' => 'Product updated successfully',
            'data' => $product
        ]);
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
