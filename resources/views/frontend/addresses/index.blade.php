@extends('layouts.frontend')

@section('title', 'My Addresses')

@section('content')
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-3">
        <!-- Breadcrumb & Title Inline -->
        <div class="mb-1 flex flex-col sm:flex-row sm:items-center justify-between gap-3 pb-3.5 border-b border-slate-100">
            <div>
                <h1 class="text-xl sm:text-2xl font-extrabold text-slate-900 tracking-tight" style="font-family: 'Outfit', sans-serif;">My Account</h1>
                <p class="text-[10px] text-slate-450 mt-1 flex items-center gap-1.5 font-bold uppercase tracking-wider">
                    <a href="/" class="hover:text-primary transition-colors">Home</a> 
                    <span class="text-slate-300">/</span> 
                    <a href="/dashboard" class="hover:text-primary transition-colors">Dashboard</a> 
                    <span class="text-slate-300">/</span> 
                    <span class="text-slate-800">My Addresses</span>
                </p>
            </div>
        </div>

        <div class="flex flex-col lg:flex-row gap-4 mt-4">
            @include('frontend.partials.customer_sidebar')
            
            <div class="w-full lg:w-3/4 space-y-4">
                <!-- Address List -->
                <div class="bg-white rounded-2xl border border-slate-150 p-4 sm:p-5 shadow-xs">
                    <h3 class="text-xs font-bold uppercase tracking-wider text-slate-800 mb-3.5 border-b border-slate-100 pb-2.5 flex items-center gap-1.5" style="font-family: 'Outfit', sans-serif;">
                        <i class="fa-solid fa-map-location-dot text-primary"></i> {{ __('Saved Addresses') }}
                    </h3>

                    @if($addresses->isEmpty())
                        <div class="text-center py-8 text-slate-400">
                            <i class="fa-solid fa-folder-open text-3xl mb-2 text-slate-200"></i>
                            <p class="text-xs font-semibold">No saved addresses found. Please add an address below.</p>
                        </div>
                    @else
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                            @foreach($addresses as $addr)
                                <div class="relative border {{ $addr->is_default ? 'border-primary bg-primary/2.5' : 'border-slate-200' }} rounded-xl p-4 flex flex-col justify-between transition hover:border-slate-300">
                                    <div>
                                        <div class="flex items-center justify-between mb-2">
                                            <span class="text-xs font-bold text-slate-800">Address #{{ $loop->iteration }}</span>
                                            @if($addr->is_default)
                                                <span class="bg-primary text-white text-[8px] font-extrabold uppercase px-2 py-0.5 rounded-full tracking-wider">Default</span>
                                            @endif
                                        </div>

                                        <p class="text-xs text-slate-900 font-bold mb-1 leading-normal">
                                            {{ $addr->address }}@if($addr->address2), {{ $addr->address2 }}@endif
                                        </p>
                                        <p class="text-[11px] text-slate-500 font-medium">
                                            {{ $addr->city }}, {{ $addr->state }} - {{ $addr->zip }}
                                        </p>
                                        <p class="text-[11px] text-slate-500 font-medium mt-1">
                                            <i class="fa-solid fa-phone text-slate-400 mr-1 text-[9px]"></i> {{ $addr->phone }}
                                        </p>

                                    </div>

                                    <div class="mt-4 pt-3.5 border-t border-slate-100/70 flex items-center justify-between">
                                        @if(!$addr->is_default)
                                            <form method="POST" action="{{ route('addresses.setDefault', $addr) }}">
                                                @csrf
                                                @method('PATCH')
                                                <button type="submit" class="text-primary hover:text-primary-dark font-extrabold text-[10px] tracking-wider uppercase cursor-pointer transition">
                                                    Set as Default
                                                </button>
                                            </form>
                                        @else
                                            <span class="text-slate-400 font-extrabold text-[10px] tracking-wider uppercase">Active default</span>
                                        @endif

                                        <form method="POST" action="{{ route('addresses.destroy', $addr) }}" onsubmit="return confirm('Are you sure you want to delete this address?');">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="text-red-500 hover:text-red-700 font-extrabold text-[10px] tracking-wider uppercase cursor-pointer transition">
                                                <i class="fa-regular fa-trash-can text-[11px] mr-0.5"></i> Delete
                                            </button>
                                        </form>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    @endif
                </div>

                <!-- Add Address Form -->
                <div class="bg-white rounded-2xl border border-slate-150 p-4 sm:p-5 shadow-xs">
                    <h3 class="text-xs font-bold uppercase tracking-wider text-slate-800 mb-3.5 border-b border-slate-100 pb-2.5 flex items-center gap-1.5" style="font-family: 'Outfit', sans-serif;">
                        <i class="fa-solid fa-plus text-primary"></i> {{ __('Add New Address') }}
                    </h3>

                    @if ($errors->any())
                        <div class="mb-4 p-3.5 bg-red-50 border border-red-200 text-red-800 rounded-xl shadow-2xs">
                            <span class="block text-xs font-bold text-red-900 mb-1">Please correct the following errors:</span>
                            <ul class="list-disc list-inside text-[11px] space-y-0.5 text-red-750">
                                @foreach ($errors->all() as $error)
                                    <li>{{ $error }}</li>
                                @endforeach
                            </ul>
                        </div>
                    @endif

                    <form method="POST" action="{{ route('addresses.store') }}" enctype="multipart/form-data" class="space-y-4">
                        @csrf

                        <div class="grid grid-cols-1 md:grid-cols-2 gap-3">
                            <div>
                                <label class="block text-[10px] font-bold text-slate-500 uppercase tracking-wider mb-1">Customer Phone Number *</label>
                                <div class="relative">
                                    <span class="absolute inset-y-0 left-0 pl-3 flex items-center text-slate-400 pointer-events-none">
                                        <i class="fa-solid fa-phone text-xs"></i>
                                    </span>
                                    <input type="text" name="phone" value="{{ old('phone') }}" required placeholder="e.g. +1 (555) 0199" 
                                           class="w-full bg-slate-50/50 border border-slate-200 focus:bg-white rounded-xl pl-9 pr-4 py-2.5 text-xs font-bold text-slate-800 shadow-2xs focus:outline-none focus:border-primary focus:ring-1 focus:ring-primary/20 transition duration-200">
                                </div>
                            </div>

                            <div>
                                <label class="block text-[10px] font-bold text-slate-500 uppercase tracking-wider mb-1">Street Address *</label>
                                <div class="relative">
                                    <span class="absolute inset-y-0 left-0 pl-3 flex items-center text-slate-400 pointer-events-none">
                                        <i class="fa-solid fa-map-marker-alt text-xs"></i>
                                    </span>
                                    <input type="text" name="address" value="{{ old('address') }}" required placeholder="e.g. 12800 Northborough Dr" 
                                           class="w-full bg-slate-50/50 border border-slate-200 focus:bg-white rounded-xl pl-9 pr-4 py-2.5 text-xs font-bold text-slate-800 shadow-2xs focus:outline-none focus:border-primary focus:ring-1 focus:ring-primary/20 transition duration-200">
                                </div>
                            </div>
                        </div>

                        <div class="grid grid-cols-1 md:grid-cols-4 gap-3">
                            <div class="md:col-span-2">
                                <label class="block text-[10px] font-bold text-slate-500 uppercase tracking-wider mb-1">Apartment, Suite, Unit, etc. (Optional)</label>
                                <div class="relative">
                                    <span class="absolute inset-y-0 left-0 pl-3 flex items-center text-slate-400 pointer-events-none">
                                        <i class="fa-solid fa-building text-xs"></i>
                                    </span>
                                    <input type="text" name="address2" value="{{ old('address2') }}" placeholder="e.g. Apt 304" 
                                           class="w-full bg-slate-50/50 border border-slate-200 focus:bg-white rounded-xl pl-9 pr-4 py-2.5 text-xs font-bold text-slate-800 shadow-2xs focus:outline-none focus:border-primary focus:ring-1 focus:ring-primary/20 transition duration-200">
                                </div>
                            </div>

                            <div>
                                <label class="block text-[10px] font-bold text-slate-500 uppercase tracking-wider mb-1">City *</label>
                                <input type="text" name="city" value="{{ old('city') }}" required placeholder="Houston" 
                                       class="w-full bg-slate-50/50 border border-slate-200 focus:bg-white rounded-xl px-3 py-2.5 text-xs font-bold text-slate-800 shadow-2xs focus:outline-none focus:border-primary focus:ring-1 focus:ring-primary/20 transition duration-200">
                            </div>

                            <div>
                                <label class="block text-[10px] font-bold text-slate-500 uppercase tracking-wider mb-1">State / Zip *</label>
                                <div class="grid grid-cols-2 gap-1.5">
                                    <input type="text" name="state" value="{{ old('state') }}" required placeholder="TX" maxlength="2" 
                                           class="w-full bg-slate-50/50 border border-slate-200 focus:bg-white rounded-xl px-2 py-2.5 text-xs font-bold text-slate-800 shadow-2xs focus:outline-none focus:border-primary focus:ring-1 focus:ring-primary/20 transition duration-200 uppercase">
                                    <input type="text" name="zip" value="{{ old('zip') }}" required placeholder="77067" 
                                           class="w-full bg-slate-50/50 border border-slate-200 focus:bg-white rounded-xl px-2 py-2.5 text-xs font-bold text-slate-800 shadow-2xs focus:outline-none focus:border-primary focus:ring-1 focus:ring-primary/20 transition duration-200">
                                </div>
                            </div>
                        </div>

                        <div class="flex items-center gap-2">
                            <input type="checkbox" name="is_default" id="is_default" value="1" {{ old('is_default') ? 'checked' : '' }}
                                   class="rounded border-slate-300 text-primary focus:ring-primary/25 h-3.5 w-3.5 cursor-pointer">
                            <label for="is_default" class="text-xs font-bold text-slate-700 select-none cursor-pointer">Set as default address</label>
                        </div>

                        <div class="pt-2">
                            <button type="submit" class="bg-primary hover:bg-primary-dark text-white font-extrabold px-6 py-3 rounded-xl text-xs tracking-wider transition-all shadow-md hover:shadow-lg transform hover:-translate-y-0.5 cursor-pointer flex items-center gap-1.5">
                                <i class="fa-solid fa-floppy-disk text-[10px]"></i> Save Address
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
@endsection
