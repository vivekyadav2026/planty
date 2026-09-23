<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Product;
use App\Models\Category;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class ProductController extends Controller
{
    public function index(Request $request)
    {
        $query = Product::with('category');

        if ($request->filled('search')) {
            $query->where('name', 'like', '%' . $request->search . '%')
                  ->orWhere('sku', 'like', '%' . $request->search . '%');
        }

        if ($request->filled('category_id')) {
            $query->where('category_id', $request->category_id);
        }

        $products = $query->latest()->paginate(10)->withQueryString();
        $categories = Category::all();

        return view('admin.products.index', compact('products', 'categories'));
    }

    public function create()
    {
        $categories = Category::all();
        return view('admin.products.create', compact('categories'));
    }

    public function store(Request $request)
    {
        if (isset($_SERVER['CONTENT_LENGTH']) && (int)$_SERVER['CONTENT_LENGTH'] > 0 && empty($request->all()) && empty($request->allFiles())) {
            $sizeMb = round((int)$_SERVER['CONTENT_LENGTH'] / (1024 * 1024), 1);
            return redirect()->back()->withInput()->withErrors([
                'images' => "The uploaded images ({$sizeMb}MB) exceed the server upload limit (post_max_size). Please upload smaller image files or compress them first."
            ]);
        }

        $request->validate([
            'name' => 'required|string|max:255',
            'category_id' => 'required|exists:categories,id',
            'price' => 'required|numeric|min:0',
            'sale_price' => 'nullable|numeric|min:0|lt:price',
            'sku' => 'nullable|string|unique:products,sku',
            'quantity' => 'required|integer|min:0',
            'short_description' => 'nullable|string',
            'description' => 'nullable|string',
            'weight' => 'required|numeric|min:0.001',
            'length' => 'required|integer|min:1',
            'width' => 'required|integer|min:1',
            'height' => 'required|integer|min:1',
            'images.*' => 'nullable|image|mimes:jpeg,png,jpg,gif,webp|max:10240',
        ]);

        $imagePaths = [];
        if ($request->hasFile('images')) {
            $path = public_path('uploads/products');
            if (!file_exists($path)) {
                mkdir($path, 0755, true);
            }

            foreach ($request->file('images') as $file) {
                if ($file->isValid()) {
                    $ext = $file->getClientOriginalExtension() ?: 'jpg';
                    $name = time() . '_' . uniqid() . '.' . $ext;
                    $file->move($path, $name);
                    $this->compressImage($path . '/' . $name);
                    $imagePaths[] = 'uploads/products/' . $name;
                }
            }
        }

        if (empty($imagePaths)) {
            $imagePaths[] = 'https://images.unsplash.com/photo-1416879598555-2591605c48b2?q=80&w=400&auto=format&fit=crop';
        }

        Product::create([
            'category_id' => $request->category_id,
            'name' => $request->name,
            'slug' => Product::generateUniqueSlug($request->name),
            'price' => $request->price,
            'sale_price' => $request->sale_price,
            'sku' => $request->sku ?: 'VB-' . strtoupper(Str::random(6)),
            'quantity' => $request->quantity,
            'short_description' => $request->short_description,
            'description' => $request->description,
            'is_featured' => $request->has('is_featured'),
            'is_bestseller' => $request->has('is_bestseller'),
            'deal_of_week' => $request->has('deal_of_week'),
            'is_active' => $request->has('is_active'),
            'weight' => $request->weight,
            'length' => $request->length,
            'width' => $request->width,
            'height' => $request->height,
            'images' => json_encode($imagePaths)
        ]);

        return redirect()->route('admin.products.index')->with('success', 'Product created successfully.');
    }

    public function edit(Product $product)
    {
        $categories = Category::all();
        return view('admin.products.edit', compact('product', 'categories'));
    }

    public function update(Request $request, Product $product)
    {
        if (isset($_SERVER['CONTENT_LENGTH']) && (int)$_SERVER['CONTENT_LENGTH'] > 0 && empty($request->all()) && empty($request->allFiles())) {
            $sizeMb = round((int)$_SERVER['CONTENT_LENGTH'] / (1024 * 1024), 1);
            return redirect()->back()->withInput()->withErrors([
                'images' => "The uploaded images ({$sizeMb}MB) exceed the server upload limit (post_max_size). Please upload smaller image files or compress them first."
            ]);
        }

        $request->validate([
            'name' => 'required|string|max:255',
            'category_id' => 'required|exists:categories,id',
            'price' => 'required|numeric|min:0',
            'sale_price' => 'nullable|numeric|min:0|lt:price',
            'sku' => 'nullable|string|unique:products,sku,' . $product->id,
            'quantity' => 'required|integer|min:0',
            'short_description' => 'nullable|string',
            'description' => 'nullable|string',
            'weight' => 'required|numeric|min:0.001',
            'length' => 'required|integer|min:1',
            'width' => 'required|integer|min:1',
            'height' => 'required|integer|min:1',
            'images.*' => 'nullable|image|mimes:jpeg,png,jpg,gif,webp|max:10240',
        ]);

        $existingImages = json_decode($product->images, true) ?? [];

        // Handle removing checked images
        if ($request->has('remove_images')) {
            foreach ($request->remove_images as $removeImg) {
                if (str_starts_with($removeImg, 'uploads/products/')) {
                    $fullPath = public_path($removeImg);
                    if (file_exists($fullPath)) {
                        @unlink($fullPath);
                    }
                }
            }
            $existingImages = array_filter($existingImages, function($img) use ($request) {
                return !in_array($img, $request->remove_images);
            });
        }

        // Handle uploading new images
        if ($request->hasFile('images')) {
            $path = public_path('uploads/products');
            if (!file_exists($path)) {
                mkdir($path, 0755, true);
            }

            $newImages = [];
            foreach ($request->file('images') as $file) {
                if ($file->isValid()) {
                    $ext = $file->getClientOriginalExtension() ?: 'jpg';
                    $name = time() . '_' . uniqid() . '.' . $ext;
                    $file->move($path, $name);
                    $this->compressImage($path . '/' . $name);
                    $newImages[] = 'uploads/products/' . $name;
                }
            }
            // Put newly uploaded images first so the new image reflects as primary immediately!
            $existingImages = array_merge($newImages, $existingImages);
        }

        // If all images removed, add the default fallback
        if (empty($existingImages)) {
            $existingImages[] = 'https://images.unsplash.com/photo-1416879598555-2591605c48b2?q=80&w=400&auto=format&fit=crop';
        }

        $product->update([
            'category_id' => $request->category_id,
            'name' => $request->name,
            'slug' => Product::generateUniqueSlug($request->name, $product->id),
            'price' => $request->price,
            'sale_price' => $request->sale_price,
            'sku' => $request->sku ?: $product->sku,
            'quantity' => $request->quantity,
            'short_description' => $request->short_description,
            'description' => $request->description,
            'is_featured' => $request->has('is_featured'),
            'is_bestseller' => $request->has('is_bestseller'),
            'deal_of_week' => $request->has('deal_of_week'),
            'is_active' => $request->has('is_active'),
            'weight' => $request->weight,
            'length' => $request->length,
            'width' => $request->width,
            'height' => $request->height,
            'images' => json_encode(array_values($existingImages))
        ]);

        return redirect()->route('admin.products.index')->with('success', 'Product updated successfully.');
    }

    public function destroy(Product $product)
    {
        $product->delete();
        return redirect()->route('admin.products.index')->with('success', 'Product deleted successfully.');
    }

    private function compressImage($sourcePath, $quality = 75)
    {
        if (!file_exists($sourcePath)) {
            return;
        }

        $info = getimagesize($sourcePath);
        if ($info === false) {
            return;
        }

        $mime = $info['mime'];
        switch ($mime) {
            case 'image/jpeg':
                $image = @imagecreatefromjpeg($sourcePath);
                break;
            case 'image/png':
                $image = @imagecreatefrompng($sourcePath);
                if ($image) {
                    imagealphablending($image, false);
                    imagesavealpha($image, true);
                }
                break;
            case 'image/webp':
                $image = @imagecreatefromwebp($sourcePath);
                break;
            case 'image/gif':
                $image = @imagecreatefromgif($sourcePath);
                break;
            default:
                return;
        }

        if ($image) {
            if ($mime === 'image/jpeg') {
                imagejpeg($image, $sourcePath, $quality);
            } elseif ($mime === 'image/png') {
                $pngQuality = round((100 - $quality) / 10);
                if ($pngQuality > 9) $pngQuality = 9;
                if ($pngQuality < 0) $pngQuality = 0;
                imagepng($image, $sourcePath, $pngQuality);
            } elseif ($mime === 'image/webp') {
                imagewebp($image, $sourcePath, $quality);
            }
            imagedestroy($image);
        }
    }
}
