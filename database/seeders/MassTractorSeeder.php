<?php
namespace Database\Seeders;
use Illuminate\Database\Seeder;
use App\Models\Category;
use App\Models\Product;
use App\Models\Banner;
use Illuminate\Support\Facades\DB;

class MassTractorSeeder extends Seeder
{
    public function run()
    {
        DB::statement("SET FOREIGN_KEY_CHECKS=0;");
        Category::truncate();
        Product::truncate();
        Banner::truncate();
        DB::statement("SET FOREIGN_KEY_CHECKS=1;");

        $categories = [
            ["name" => "Fiber Hoods", "slug" => "fiber-hoods", "img" => "images/cat_hood.jpg"],
            ["name" => "Bumpers", "slug" => "bumpers", "img" => "images/cat_bumper.jpg"],
            ["name" => "Silencers", "slug" => "silencers", "img" => "images/cat_silencer.jpg"],
            ["name" => "Music Systems", "slug" => "music-systems", "img" => "images/music_system.jpg"],
            ["name" => "Parking Lights", "slug" => "parking-lights", "img" => "images/parking_light.jpg"],
            ["name" => "Wheel Caps", "slug" => "wheel-caps", "img" => "images/wheel_cap.jpg"],
            ["name" => "Steering Covers", "slug" => "steering-covers", "img" => "images/steering_cover.jpg"]
        ];

        $catModels = [];
        foreach ($categories as $c) {
            $catModels[$c["name"]] = Category::create(["name" => $c["name"], "slug" => $c["slug"], "is_active" => 1]);
        }

        $productsData = [
            // Hoods
            ["Fiber Hoods", "Premium Green Fiber Hood", 250, "images/cat_hood.jpg"],
            ["Fiber Hoods", "Aerodynamic Red Tractor Hood", 280, "images/cat_hood.jpg"],
            ["Fiber Hoods", "Heavy Duty Blue Hood", 260, "images/cat_hood.jpg"],
            ["Fiber Hoods", "Custom Painted Fiber Hood", 300, "images/cat_hood.jpg"],
            
            // Bumpers
            ["Bumpers", "Rugged Steel Front Bumper", 150, "images/cat_bumper.jpg"],
            ["Bumpers", "Heavy Duty Crash Guard", 180, "images/cat_bumper.jpg"],
            ["Bumpers", "Matte Black Defender Bumper", 160, "images/cat_bumper.jpg"],
            ["Bumpers", "Premium Chrome Bumper", 210, "images/cat_bumper.jpg"],

            // Silencers
            ["Silencers", "Chrome Modified Exhaust Pipe", 120, "images/cat_silencer.jpg"],
            ["Silencers", "Straight Pipe Tractor Silencer", 110, "images/cat_silencer.jpg"],
            ["Silencers", "Dual Chrome Exhaust System", 190, "images/cat_silencer.jpg"],
            ["Silencers", "Heavy Duty Steel Silencer", 95, "images/cat_silencer.jpg"],
            
            // Music Systems
            ["Music Systems", "Heavy Bass Tractor DJ System", 450, "images/music_system.jpg"],
            ["Music Systems", "Premium 4-Speaker Audio", 299, "images/music_system.jpg"],
            ["Music Systems", "Waterproof Tractor Stereo", 199, "images/music_system.jpg"],
            ["Music Systems", "LED Integrated Sound Bar", 250, "images/music_system.jpg"],

            // Parking Lights
            ["Parking Lights", "Neon LED Parking Light Bar", 80, "images/parking_light.jpg"],
            ["Parking Lights", "Sequential Turn Signal Lights", 65, "images/parking_light.jpg"],
            ["Parking Lights", "Bright White LED Pods", 45, "images/parking_light.jpg"],
            ["Parking Lights", "RGB Underglow Kit", 110, "images/parking_light.jpg"],

            // Wheel Caps
            ["Wheel Caps", "Neon LED Wheel Cap", 120, "images/wheel_cap.jpg"],
            ["Wheel Caps", "Chrome Spinner Wheel Cap", 85, "images/wheel_cap.jpg"],
            ["Wheel Caps", "Matte Black Wheel Cover", 60, "images/wheel_cap.jpg"],
            ["Wheel Caps", "Stainless Steel Hub Cap", 95, "images/wheel_cap.jpg"],

            // Steering Covers
            ["Steering Covers", "Premium Leather Steering Cover", 35, "images/steering_cover.jpg"],
            ["Steering Covers", "Anti-Slip Rubber Grip Cover", 25, "images/steering_cover.jpg"],
            ["Steering Covers", "Custom Stitched Wheel Cover", 40, "images/steering_cover.jpg"],
            ["Steering Covers", "Heavy Duty Comfort Grip", 30, "images/steering_cover.jpg"],
            ["Steering Covers", "Luxury Woodgrain Steering Cover", 55, "images/steering_cover.jpg"]
        ];

        foreach ($productsData as $index => $pd) {
            $catId = $catModels[$pd[0]]->id;
            $slug = \Illuminate\Support\Str::slug($pd[1] . "-" . $index);
            Product::create([
                "category_id" => $catId,
                "name" => $pd[1],
                "slug" => $slug,
                "price" => $pd[2],
                "quantity" => 50,
                "is_active" => 1,
                "images" => json_encode([$pd[3]])
            ]);
        }

        Banner::create(["title" => "Premium Custom Tractor", "image_path" => "images/slider_new_1.jpg", "link" => "/shop", "is_active" => 1]);
        Banner::create(["title" => "High-End Cabin & Audio", "image_path" => "images/slider_new_2.jpg", "link" => "/shop", "is_active" => 1]);
    }
}

