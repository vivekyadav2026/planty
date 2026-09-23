<?php
namespace Database\Seeders;
use Illuminate\Database\Seeder;
use App\Models\Category;
use App\Models\Product;
use App\Models\ProductImage;
use App\Models\Banner;
use Illuminate\Support\Facades\DB;

class TractorSeeder extends Seeder
{
    public function run()
    {
        DB::statement('SET FOREIGN_KEY_CHECKS=0;');
        Category::truncate();
        Product::truncate();
        ProductImage::truncate();
        Banner::truncate();
        DB::statement('SET FOREIGN_KEY_CHECKS=1;');

        $c1 = Category::create(['name'=>'Steering Covers', 'slug'=>'steering-covers', 'is_active'=>1]);
        $c2 = Category::create(['name'=>'Wheel Caps', 'slug'=>'wheel-caps', 'is_active'=>1]);
        $c3 = Category::create(['name'=>'Parking Lights', 'slug'=>'parking-lights', 'is_active'=>1]);
        $c4 = Category::create(['name'=>'Music Systems', 'slug'=>'music-systems', 'is_active'=>1]);

        $p1 = Product::create(['category_id'=>$c1->id,'name'=>'Premium Leather Steering Cover','slug'=>'premium-leather-steering-cover','price'=>150.00,'quantity'=>50,'is_active'=>1]);
        $p2 = Product::create(['category_id'=>$c2->id,'name'=>'Neon LED Wheel Cap','slug'=>'neon-led-wheel-cap','price'=>120.00,'quantity'=>30,'is_active'=>1]);
        $p3 = Product::create(['category_id'=>$c3->id,'name'=>'Neon LED Parking Light Bar','slug'=>'neon-led-parking-light-bar','price'=>80.00,'quantity'=>40,'is_active'=>1]);
        $p4 = Product::create(['category_id'=>$c4->id,'name'=>'Heavy Bass Tractor DJ System','slug'=>'heavy-bass-tractor-dj-system','price'=>450.00,'quantity'=>15,'is_active'=>1]);

        ProductImage::create(['product_id'=>$p1->id,'image_path'=>'images/steering_cover.jpg','is_primary'=>1]);
        ProductImage::create(['product_id'=>$p2->id,'image_path'=>'images/wheel_cap.jpg','is_primary'=>1]);
        ProductImage::create(['product_id'=>$p3->id,'image_path'=>'images/parking_light.jpg','is_primary'=>1]);
        ProductImage::create(['product_id'=>$p4->id,'image_path'=>'images/music_system.jpg','is_primary'=>1]);

        Banner::create(['title'=>'Fiber Hoods & Bumpers', 'image_path'=>'images/slider_2.jpg', 'link'=>'/shop', 'is_active'=>1]);
        Banner::create(['title'=>'LED Lights & Audio', 'image_path'=>'images/slider_3.jpg', 'link'=>'/shop', 'is_active'=>1]);
        Banner::create(['title'=>'Mahadev Tractor Modification', 'image_path'=>'images/slider_1.jpg', 'link'=>'/shop', 'is_active'=>1]);
    }
}

