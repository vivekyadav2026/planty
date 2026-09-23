<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Category;
use App\Models\Product;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class NurserySeeder extends Seeder
{
    public function run()
    {
        Schema::disableForeignKeyConstraints();
        Category::truncate();
        Product::truncate();
        Schema::enableForeignKeyConstraints();

        $categoriesData = [
            'indoor-plants' => ['name' => 'Indoor Plants', 'img' => 'https://images.unsplash.com/photo-1416879598555-2591605c48b2?q=80&w=200&auto=format&fit=crop'],
            'succulents' => ['name' => 'Succulents', 'img' => 'https://images.unsplash.com/photo-1459411552884-841db9b3cc2a?q=80&w=200&auto=format&fit=crop'],
            'outdoor-plants' => ['name' => 'Outdoor Plants', 'img' => 'https://images.unsplash.com/photo-1584589167171-541ce45f1eea?q=80&w=200&auto=format&fit=crop'],
            'flowering' => ['name' => 'Flowering Plants', 'img' => 'https://images.unsplash.com/photo-1490750967868-8f52a0928373?q=80&w=200&auto=format&fit=crop'],
            'pots' => ['name' => 'Pots & Planters', 'img' => 'https://images.unsplash.com/photo-1485955900006-10f4d324d411?q=80&w=200&auto=format&fit=crop'],
            'seeds' => ['name' => 'Seeds & Bulbs', 'img' => 'https://images.unsplash.com/photo-1595822527263-fb5208f86f34?q=80&w=200&auto=format&fit=crop'],
            'soil' => ['name' => 'Potting Soil & Co', 'img' => 'https://images.unsplash.com/photo-1622383563227-04401ab4e5ea?q=80&w=200&auto=format&fit=crop'],
            'adenium' => ['name' => 'Adenium Collection', 'img' => 'https://images.unsplash.com/photo-1604762512526-b7ce049b5768?q=80&w=200&auto=format&fit=crop'],
        ];

        $categoryIds = [];

        foreach ($categoriesData as $slug => $data) {
            $cat = Category::create([
                'slug' => $slug,
                'name' => $data['name'],
                'image' => $data['img'],
                'is_active' => true,
            ]);
            $categoryIds[$slug] = $cat->id;
        }

        // [slug, name, sale_price, price, category_slug, image, is_bestseller]
        $productsToSeed = [
            // Indoor
            ["monstera-deliciosa", "Monstera Deliciosa", 499.00, 699.00, "indoor-plants", "https://images.unsplash.com/photo-1614594975525-e45190c55d0b?q=80&w=400&auto=format&fit=crop", true],
            ["snake-plant", "Snake Plant (Sansevieria)", 299.00, 399.00, "indoor-plants", "https://images.unsplash.com/photo-1599598425947-3300262b71fa?q=80&w=400&auto=format&fit=crop", true],
            ["zz-plant", "ZZ Plant", 349.00, 499.00, "indoor-plants", "https://images.unsplash.com/photo-1611681283307-e0fa6302e3b2?q=80&w=400&auto=format&fit=crop", true],
            
            // Succulents
            ["jade-plant", "Jade Plant Mini", 199.00, 299.00, "succulents", "https://images.unsplash.com/photo-1459411552884-841db9b3cc2a?q=80&w=400&auto=format&fit=crop", true],
            ["aloe-vera", "Aloe Vera", 149.00, 199.00, "succulents", "https://images.unsplash.com/photo-1596547609652-9fc5d8d428ae?q=80&w=400&auto=format&fit=crop", true],
            ["echeveria", "Echeveria Elegans", 249.00, 349.00, "succulents", "https://images.unsplash.com/photo-1485955900006-10f4d324d411?q=80&w=400&auto=format&fit=crop", false],
            ["succulent-combo", "Succulent Combo Pack of 3", 499.00, 699.00, "succulents", "https://images.unsplash.com/photo-1497250681960-ef046c08a56e?q=80&w=400&auto=format&fit=crop", true],
            
            // Flowering
            ["peace-lily", "Peace Lily", 399.00, 499.00, "flowering", "https://images.unsplash.com/photo-1490750967868-8f52a0928373?q=80&w=400&auto=format&fit=crop", false],
            ["anthurium", "Anthurium Red", 599.00, 799.00, "flowering", "https://images.unsplash.com/photo-1604762512526-b7ce049b5768?q=80&w=400&auto=format&fit=crop", false],
            
            // Adenium
            ["adenium-obesum", "Adenium Obesum (Desert Rose)", 450.00, 600.00, "adenium", "https://images.unsplash.com/photo-1622383563227-04401ab4e5ea?q=80&w=400&auto=format&fit=crop", true],
            ["adenium-rosy", "Adenium Rosy Hybrid", 650.00, 800.00, "adenium", "https://images.unsplash.com/photo-1584589167171-541ce45f1eea?q=80&w=400&auto=format&fit=crop", false],
            
            // Pots
            ["ceramic-pot-white", "White Ceramic Pot (Small)", 150.00, 200.00, "pots", "https://images.unsplash.com/photo-1485955900006-10f4d324d411?q=80&w=400&auto=format&fit=crop", true],
            ["terracotta-pot", "Classic Terracotta Pot", 99.00, 150.00, "pots", "https://images.unsplash.com/photo-1614594975525-e45190c55d0b?q=80&w=400&auto=format&fit=crop", false],
            
            // Soil
            ["potting-mix-5kg", "Premium Potting Mix 5kg", 250.00, 350.00, "soil", "https://images.unsplash.com/photo-1595822527263-fb5208f86f34?q=80&w=400&auto=format&fit=crop", true],
        ];

        foreach ($productsToSeed as $p) {
            Product::create([
                'slug' => $p[0],
                'name' => $p[1],
                'category_id' => $categoryIds[$p[4]],
                'price' => $p[3],
                'sale_price' => $p[2],
                'description' => 'A beautiful plant/accessory to bring nature home. Easy to care for and perfect for your space.',
                'short_description' => 'Premium nursery product.',
                'images' => json_encode([$p[5], $p[5]]),
                'is_active' => true,
                'is_bestseller' => $p[6],
                'quantity' => 50,
            ]);
        }
    }
}
