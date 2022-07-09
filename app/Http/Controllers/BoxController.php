<?php

namespace App\Http\Controllers;

use App\Http\Resources\BoxResource;
use App\Models\Box;
use App\Models\BoxItemList;
use App\Models\Item;
use Illuminate\Http\Request;

class BoxController extends Controller
{
    public function index()
    {
        return response()->json([
            'data' => BoxResource::collection(Box::all()),
        ]);
    }

    public function store()
    {
        $admin_id = auth('api')->id();

        $random_items = Item::inRandomOrder()->limit(3)->get(['id', 'price']);

        $items_price = $random_items->sum('price');

        $box_price = ((10 / 100) * $items_price) + $items_price;

        $box = Box::create([
            'admin_id' => $admin_id,
            'price' => $box_price,
        ]);

        foreach ($random_items as $item) {
            BoxItemList::create([
                'box_id' => $box->id,
                'item_id' => $item->id,
            ]);
        }

        return response()->json([
            'success' => 'box created successfully.'
        ], 201);
    }
}
