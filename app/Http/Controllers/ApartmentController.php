<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

namespace App\Http\Controllers;

use Illuminate\Routing\Controller as BaseController;
use App\Models\Apartment;
use App\Models\House;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use App\Http\Controllers\LangController;
use Carbon\Carbon;
use \App\Models\ApartmentPrices;
use App\Models\ApartmentReviews;

/**
 * Description of ApartmentController
 *
 * @author Stjepan
 */
class ApartmentController extends BaseController {

    public function back($house_id) {
        return redirect()->route('admin.houses.edit', ['id' => $house_id]);
    }

    public function store(Request $request, $house_id) {
        $validatedData = $request->validate([
            'name' => 'required',
        ]);

        $house = House::find($house_id);

        $apartment = $house->apartment()->create([
            'name' => $validatedData['name'],
            'internalName' => $request->input('internalName'),
            'size' => $request->input('size'),
            'apartmentType' => $request->input('type'),
            'note' => $request->input('note'),
            'description' => LangController::getLanguageFieldAsJson($request, 'description'),
        ]);

        return redirect()->route('admin.apartments.edit', ['house_id' => $house_id, 'id' => $apartment->id])->with('succesfulMessage', 'Apartment ' . $apartment->name . ' created successfully');
    }

    public function update(Request $request, $house_id, $id) {
//        error_log('Pozvana update price.');

        $apartment = Apartment::find($id);
        $apartment->name = $request->input('name');
        $apartment->internalName = $request->input('internalName');
        $apartment->apartmentType = $request->input('type');
        $apartment->size = $request->input('size');
        $apartment->note = $request->input('note');

        $apartment->description = LangController::getLanguageFieldAsJson($request, 'description');

        $apartment->save();

        return redirect()->route('admin.apartments.edit', ['house_id' => $house_id, 'id' => $apartment->id])->with('succesfulMessage', 'Apartment - ' . $apartment->name . ' is updated');
    }

    public function new($house_id) {
        $house = House::find($house_id);

        return view('admin/newapartment',
                ['house' => $house]);
    }

    public function edit($house_id, $id) {
        $house = House::find($house_id);
        $apartment = Apartment::find($id);

        return view('admin/newapartment', [
            'apartment' => $apartment,
            'house' => $house,
        ]);
    }

    public function deletePrice($apartment_id, $id) {
//        error_log('Pozvana delete price.');

        $apartment = Apartment::find($apartment_id);
        $price = ApartmentPrices::find($id);
        $price->delete();

        return redirect()->route('admin.apartments.edit', ['house_id' => $apartment->house->id, 'id' => $apartment->id]);
    }

    public function updatePrice(Request $request, $apartent_id, $id) {
        $apartment = Apartment::find($apartent_id);
        $price = ApartmentPrices::find($id);

        $from = $request->input('fromDate') . ' 00:00:00';
        $to = $request->input('toDate') . ' 23:59:59';

        $price->fromDate = Carbon::createFromFormat('d.m.Y H:i:s', $from);
        $price->toDate = Carbon::createFromFormat('d.m.Y H:i:s', $to);
        $price->price = $request->input('price');

        $price->save();

        return redirect()->route('admin.apartments.edit', ['house_id' => $apartment->house->id, 'id' => $apartment->id])->with('succesfulMessage', 'CIJENA JE PROSLA?');
    }

    public function addPrice(Request $request, $house_id, $id) {
        $apartment = Apartment::find($id);

        $price = $apartment->apartmentPrices()->create([
            'fromDate' => Carbon::createFromFormat('d.m.Y H:i:s', $request->input('fromDate') . ' 00:00:00')->toDateString(),
            'toDate' => Carbon::createFromFormat('d.m.Y H:i:s', $request->input('toDate') . ' 23:59:59')->toDateString(),
            'price' => $request->input('price'),
        ]);
        return redirect()->route('admin.apartments.edit', ['house_id' => $apartment->house->id, 'id' => $apartment->id])->with('succesfulMessage', 'CIJENA JE PROSLA?');
    }
    
    public static function duplicatePriceToAllOtherApartments(){
            
//        $ap1 = 1;
//        $ap2 = 2;
//        $ap3 = 3;
//        $ap4 = 4;
//        $ap5 = 5;
//        $ap6 = 6;
//        $ap7 = 7;
//        $mainAp = Apartment::find($ap1);
//        
//        $mainPrices = $mainAp->apartmentPrices()->get();
//        $apartment2 = Apartment::find($ap2);
//        if ($apartment2 != null && !$apartment2->apartmentPrices()->exists()) {
//            error_log('Dupliciram cijene za 2:');
//            foreach ($mainPrices as $price) {
//                $price = $apartment2->apartmentPrices()->create([
//                    'fromDate' => $price->fromDate,
//                    'toDate' => $price->toDate,
//                    'price' => $price->price,
//                ]);
//            }
//        }
//        $apartment3 = Apartment::find($ap3);
//        if ($apartment3 != null && !$apartment3->apartmentPrices()->exists()) {
//            error_log('Dupliciram cijene za 3:');
//            foreach ($mainPrices as $price) {
//                $price = $apartment3->apartmentPrices()->create([
//                    'fromDate' => $price->fromDate,
//                    'toDate' => $price->toDate,
//                    'price' => $price->price,
//                ]);
//            }
//        }
//        $apartment4 = Apartment::find($ap4);
//        if ($apartment4 != null && !$apartment4->apartmentPrices()->exists()) {
//            error_log('Dupliciram cijene za 4:');
//            foreach ($mainPrices as $price) {
//                $price = $apartment4->apartmentPrices()->create([
//                    'fromDate' => $price->fromDate,
//                    'toDate' => $price->toDate,
//                    'price' => $price->price,
//                ]);
//            }
//        }
//        $apartment5 = Apartment::find($ap5);
//        if ($apartment5 != null && !$apartment5->apartmentPrices()->exists()) {
//            error_log('Dupliciram cijene za 5:');
//            foreach ($mainPrices as $price) {
//                $price = $apartment5->apartmentPrices()->create([
//                    'fromDate' => $price->fromDate,
//                    'toDate' => $price->toDate,
//                    'price' => $price->price,
//                ]);
//            }
//        }
//        $apartment6 = Apartment::find($ap6);
//        if ($apartment6 != null  && !$apartment6->apartmentPrices()->exists()) {
//            error_log('Dupliciram cijene za 6:');
//            foreach ($mainPrices as $price) {
//                $price = $apartment6->apartmentPrices()->create([
//                    'fromDate' => $price->fromDate,
//                    'toDate' => $price->toDate,
//                    'price' => $price->price,
//                ]);
//            }
//        }
//        $apartment7 = Apartment::find($ap7);
//        if ($apartment7 != null  && !$apartment7->apartmentPrices()->exists()) {
//            error_log('Dupliciram cijene za 7:');
//            foreach ($mainPrices as $price) {
//                $price = $apartment7->apartmentPrices()->create([
//                    'fromDate' => $price->fromDate,
//                    'toDate' => $price->toDate,
//                    'price' => $price->price,
//                ]);
//            }
//        }
    }

    public function index() {
        return view('admin/apartments', ['apartments' => Apartment::all()]);
    }

    public function show($locale, Apartment $apartment) {
        $lang = LangController::updateLanguage($locale);

        return view('site.apartment', [
            'lang' => $lang,
            'apartment' => $apartment,
            'otherApartments' => SiteController::orderedApartments()->where('id', '!=', $apartment->id)->take(3),
            'reviews' => ApartmentReviews::newestFirst(),
            'reviewSummary' => ApartmentReviews::summary(),
        ]);
    }

    public function destroy(Apartment $apartment) {
        if ($apartment->delete()) {
            return true;
        }
    }

    public static function getById($apartmentId) {
        return Apartment::find($apartmentId);
    }

    public static function getAll() {
        return Apartment::all();
    }

    public function updateSearchCriteria(Request $request) {
        $fromDate = $request->input('checkIn');
        $toDate = $request->input('checkOut');
        $guests = $request->input('guests');

        $request->session()->put('checkIn', $fromDate);
        $request->session()->put('checkOut', $toDate);
        $request->session()->put('guests', $guests);
        
        return redirect()->to(url()->previous() . '#apartments');
    }
    
    public function calculatePriceAjax(Request $request){
        
        $apartment_id = $request->input('apartment_id');
        $periodFrom = $request->input('period_from');
        $periodTo = $request->input('period_to');
        $guests = $request->input('guests');
        
        $request->session()->put('checkIn', $periodFrom);
        $request->session()->put('checkOut', $periodTo);
        $request->session()->put('guests', $guests);
        
        
        $apartment = Apartment::find($apartment_id);
        
        $price = $apartment->calculatePrice($periodFrom, $periodTo, $guests);
        
        $priceAmountTag = $apartment->getPriceAmountTextForApartment(true, $price);
        $priceTextTag = $apartment->getPriceValueTextForApartment(true, $price);
        
        $data = [
            'priceAmountTag' => $priceAmountTag,
            'priceTextTag' => $priceTextTag,
        ];

        return response()->json($data);
    }

}
