<?php
use App\Models\Setting;
function setting($key, $default = '') { $s = Setting::where('key', $key)->first(); return $s ? $s->value : $default; }
function pkr($n) { return 'PKR ' . number_format((float)$n, 0); }
function shop_name() { return setting('shop_name', 'KhataPOS Shop'); }
