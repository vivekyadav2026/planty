<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Models\Product;
use App\Models\User;
use App\Models\Setting;
use Illuminate\Http\Request;

class AdminController extends Controller
{
    public function dashboard()
    {
        $totalSales = Order::where('payment_status', 'completed')->sum('total_amount');
        $totalOrders = Order::count();
        $pendingOrders = Order::where('status', 'pending')->count();
        $completedOrders = Order::where('status', 'completed')->count();
        $totalProducts = Product::count();
        $totalUsers = User::where('is_admin', false)->count();

        $recentOrders = Order::with('user')->latest()->take(5)->get();

        return view('admin.dashboard', compact(
            'totalSales',
            'totalOrders',
            'pendingOrders',
            'completedOrders',
            'totalProducts',
            'totalUsers',
            'recentOrders'
        ));
    }

    public function settings()
    {
        try {
            if (!\Illuminate\Support\Facades\Schema::hasTable('settings')) {
                \Illuminate\Support\Facades\Schema::create('settings', function (\Illuminate\Database\Schema\Blueprint $table) {
                    $table->id();
                    $table->string('key')->unique();
                    $table->text('value')->nullable();
                    $table->timestamps();
                });
            }
            $settings = Setting::pluck('value', 'key')->all();
        } catch (\Throwable $e) {
            $settings = [];
        }
        return view('admin.settings.edit', compact('settings'));
    }

    public function updateSettings(Request $request)
    {
        try {
            if (!\Illuminate\Support\Facades\Schema::hasTable('settings')) {
                \Illuminate\Support\Facades\Schema::create('settings', function (\Illuminate\Database\Schema\Blueprint $table) {
                    $table->id();
                    $table->string('key')->unique();
                    $table->text('value')->nullable();
                    $table->timestamps();
                });
            }

            $data = $request->except('_token');

            foreach ($data as $key => $value) {
                Setting::updateOrCreate(
                    ['key' => $key],
                    ['value' => $value]
                );
            }

            Setting::clearCache();
            \Illuminate\Support\Facades\Cache::forget('shiprocket_jwt_token');

            return redirect()->back()->with('success', 'Settings updated successfully.');
        } catch (\Throwable $e) {
            return redirect()->back()->with('error', 'Error updating settings: ' . $e->getMessage());
        }
    }
}
