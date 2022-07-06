<?php

namespace App\Http\Controllers;

use App\Models\Item;
use Illuminate\Support\Str;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class ItemController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        return response()->json([
            'data' => Item::all()
        ]);
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        $validate = Validator::make($request->all(), [
            'name' => 'required',
            'image' => 'mimes:jpeg,jpg,png|max:10000',
            'price' => 'required|numeric',
            'description' => 'required'
        ]);

        if ($validate->fails()) {
            return response()->json([
                'error' => $validate->errors()
            ], 422);
        }

        if ($request->has('image')) {
            $img_new_name = $this->upload_item_image($request);

            $request->request->add([
                'image' => $img_new_name
            ]);
        }

        Item::create($request->all());

        return response()->json([
            'success' => 'item created successfully.'
        ], 201);
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\Models\Item $item
     * @return \Illuminate\Http\Response
     */
    public function show(Item $item)
    {
        return response()->json([
            'data' => $item
        ]);
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models\Item $item
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, Item $item)
    {
        $validate = Validator::make($request->all(), [
            'image' => 'mimes:jpeg,jpg,png|max:10000',
            'price' => 'numeric',
        ]);

        if ($validate->fails()) {
            return response()->json([
                'error' => $validate->errors()
            ], 422);
        }

        if ($request->has('image')) {
            $old_img = $item->image;
            // remove old image
            $image_path = public_path("/uploaded_images/") . $old_img;
            if (file_exists($image_path)) {
                unlink($image_path);
            }

            $img_new_name = $this->upload_item_image($request);

            $request->request->add([
                'image' => $img_new_name
            ]);
        }

        $item->update($request->all());

        return response()->json([
            'success' => "item updated successfully.",
            'data' => $item
        ]);
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Models\Item $item
     * @return \Illuminate\Http\Response
     */
    public function destroy(Item $item)
    {
        $item->delete();

        return response()->json([
            'success' => 'item deleted successfully.'
        ]);
    }

    private function upload_item_image($request)
    {
        // upload the image
        $file = $request->file('image');
        $newName = "item_" . Str::random(8) . "." . $file->getClientOriginalExtension();
        $destinationPath = public_path('/uploaded_images');
        $file->move($destinationPath, $newName);

        return $newName;
    }
}
