<?php

namespace App\Http\Controllers;

use App\Models\Apartment;
use App\Models\House;
use App\Models\Images;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\File;

class ImageController extends Controller {

    public function index() {
        return view("imageUpload");
    }

    public function storeImageToApartment(Request $request, $house_id, $apartment_id) {

        $description = $request->input('description');
        $isMain = $request->has('mainimage');

        $request->validate([
            'image' => 'required|image:allow_svg|mimes:jpeg,png,jpg,gif,svg|max:2048',
        ]);

        $apartment = Apartment::find($apartment_id);
        $house = House::find($house_id);

        $imageName = time() . $description . $isMain . '.' . $request->image->extension();
        $imagePath = 'images/apartments/' . $apartment_id . '/';
        $request->image->move(public_path($imagePath), $imageName);

        $apartment->images()->create([
            'filename' => $imageName,
            'description' => $request->input('description'),
            'mainimage' => $request->has('mainimage'),
        ]);

        return back()
                        ->with('success', 'You have successfully upload image.')
                        ->with('house', $house)
                        ->with('apartment', $apartment);
    }
    
    public function deleteImageFromApartment($apartment_id, $image_id){
        $apartment = Apartment::find($apartment_id);
        $image = Images::find($image_id);
        
        $image_path = public_path('images/apartments/' . $apartment_id . '/' . $image->filename);
        if(File::exists($image_path)){
            File::delete($image_path);
        }
        
        $image->delete();
        
        return redirect()->route('admin.apartments.edit', ['house_id' => $apartment->house->id, 'id' => $apartment->id]);
        
    }
    

}
