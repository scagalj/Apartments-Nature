<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\ImageController;
use App\Http\Controllers\ApartmentController;
use App\Http\Controllers\RoomAccessoriesController;
use App\Http\Controllers\AmenitiesController;
use App\Http\Controllers\RoomController;
use App\Http\Controllers\BedController;
use App\Http\Controllers\BathroomController;
use App\Http\Controllers\HouseController;
use App\Http\Controllers\BookingRequestController;
use App\Http\Controllers\LangController;
use App\Http\Controllers\SitemapController;
use App\Http\Controllers\SiteController;

/*
  |--------------------------------------------------------------------------
  | Web Routes
  |--------------------------------------------------------------------------
  |
  | Here is where you can register web routes for your application. These
  | routes are loaded by the RouteServiceProvider within a group which
  | contains the "web" middleware group. Now create something great!
  |
 */



Route::get('/sitemap.xml', [SitemapController::class, 'index']);

//--UPDATE SEARCH CRITERIAS
Route::post('/', [ApartmentController::class, 'updateSearchCriteria'])->name('apartment.searchCriteria.update');

//----AJAX REQUEST-----------
Route::get('/apartment/price', [ApartmentController::class, 'calculatePriceAjax'])->name('apartment.price');



//------IMAGE ROUTES------------

Route::controller(ImageController::class)->group(function() {
    Route::get('image-upload', 'index');
    Route::post('image-upload', 'store')->name('image.store');
});

//------END IMAGE ROUTES------------
//------LANG ROUTES-------------

//Route::get('lang/home', [LangController::class, 'index']);
//Route::get('lang/change', [LangController::class, 'change'])->name('changeLang');

//------END LANG ROUTES-------------

Route::get('/apnatureadmin', function () {
    return view('admin/dashboard');
})->middleware(['auth'])->name('admin');

Route::get('apnatureadmin/dashboard', function () {
    return view('admin/dashboard');
})->middleware(['auth'])->name('dashboard');

require __DIR__ . '/auth.php';
Auth::routes();

Route::get('/home', [App\Http\Controllers\HomeController::class, 'index'])->name('home');

//--APPLICATION PORTAL

$locales = implode('|', config('app.locales'));

Route::get('/', [SiteController::class, 'root'])->name('welcome');

Route::get('/apartment/{apartment}', function ($apartment) {
    return redirect()->route('apartment.locale', ['locale' => LangController::getLanguage(), 'apartment' => $apartment], 301);
})->whereNumber('apartment')->name('apartment');

Route::get('/selfcheckin', function () {
    return redirect()->route('selfcheckin.locale', ['locale' => LangController::getLanguage()], 301);
})->name('selfcheckin');

Route::get('/{locale}', [SiteController::class, 'home'])->where('locale', $locales)->name('welcome.locale');
Route::get('/{locale}/apartment/{apartment}', [ApartmentController::class, 'show'])->where(['locale' => $locales, 'apartment' => '[0-9]+'])->name('apartment.locale');
Route::get('/{locale}/contactus', [SiteController::class, 'contact'])->where('locale', $locales)->name('contactus');
Route::get('/{locale}/privacypolicy', [SiteController::class, 'privacyPolicy'])->where('locale', $locales)->name('privacypolicy');
Route::get('/{locale}/selfcheckin', [SiteController::class, 'selfCheckIn'])->where('locale', $locales)->name('selfcheckin.locale');


//---MAIL CONTACT----
Route::post('/contactus', [BookingRequestController::class, 'sendInformationRequest'])->middleware('throttle:contact-forms')->name('contact.information.request');
Route::post('/apartment/{apartment_id}', [BookingRequestController::class, 'sendBookingRequest'])->whereNumber('apartment_id')->middleware('throttle:contact-forms')->name('contact.booking.request');

//---ADMIN PORTAL--

Route::middleware('auth')->group(function () {

    Route::get('/apnatureadmin/houses', [HouseController::class, 'index'])->name('admin.houses.index');
    Route::get('/apnatureadmin/newhouse', [HouseController::class, 'new'])->name('admin.houses.new');
    Route::get('/apnatureadmin/newhouse/{id}', [HouseController::class, 'edit'])->name('admin.houses.edit');
    Route::patch('/apnatureadmin/newhouse/{id}', [HouseController::class, 'update'])->name('admin.houses.update');
    Route::post('/apnatureadmin/newhouse', [HouseController::class, 'store'])->name('admin.houses.store');

    Route::get('/apnatureadmin/apartments', [ApartmentController::class, 'index'])->name('admin.apartments.index');
    Route::get('/apnatureadmin/newapartment/{house_id}', [ApartmentController::class, 'new'])->name('admin.apartments.new');
    Route::get('/apnatureadmin/newapartment/{house_id}/{id}', [ApartmentController::class, 'edit'])->name('admin.apartments.edit');
    Route::patch('/apnatureadmin/newapartment/{house_id}/{id}', [ApartmentController::class, 'update'])->name('admin.apartments.update');
    Route::post('/apnatureadmin/newapartment/{house_id}', [ApartmentController::class, 'store'])->name('admin.apartments.store');
    Route::get('/apnatureadmin/house/{house_id}', [ApartmentController::class, 'back'])->name('admin.apartments.back');
    Route::post('/apnatureadmin/newapartment/{house_id}/{id}', [ApartmentController::class, 'addPrice'])->name('admin.apartments.addprice');
    Route::post('/apnatureadmin/apartment/price/delete/{apartment_id}/{id}', [ApartmentController::class, 'deletePrice'])->name('admin.apartments.deleteprice');
    Route::post('/apnatureadmin/apartment/price/update/{apartment_id}/{id}', [ApartmentController::class, 'updatePrice'])->name('admin.apartments.updateprice');

    Route::get('/apnatureadmin/newroom/{apartment_id}', [RoomController::class, 'new'])->name('admin.rooms.new');
    Route::get('/apnatureadmin/newroom/{apartment_id}/{id}', [RoomController::class, 'edit'])->name('admin.rooms.edit');
    Route::post('/apnatureadmin/newroom/{apartment_id}', [RoomController::class, 'store'])->name('admin.rooms.store');
    Route::patch('/apnatureadmin/newroom/{apartment_id}/{id}', [RoomController::class, 'update'])->name('admin.rooms.update');
    Route::get('/apnatureadmin/apartment/back/{apartment_id}', [RoomController::class, 'back'])->name('admin.rooms.back');
    Route::post('/apnatureadmin/apartment/rooms/delete/{apartment_id}/{id}', [RoomController::class, 'delete'])->name('admin.rooms.delete');

    Route::get('/apnatureadmin/newbed/{apartment_id}/{room_id}', [BedController::class, 'new'])->name('admin.beds.new');
    Route::get('/apnatureadmin/newbed/{apartment_id}/{room_id}/{id}', [BedController::class, 'edit'])->name('admin.beds.edit');
    Route::post('/apnatureadmin/newbed/{apartment_id}/{room_id}', [BedController::class, 'store'])->name('admin.beds.store');
    Route::patch('/apnatureadmin/newbed/{apartment_id}/{room_id}/{id}', [BedController::class, 'update'])->name('admin.beds.update');
    Route::post('/apnatureadmin/room/bed/delete/{room_id}/{id}', [BedController::class, 'delete'])->name('admin.beds.delete');

    Route::get('/apnatureadmin/newroomaccessories/{room_id}', [RoomAccessoriesController::class, 'new'])->name('admin.roomaccessories.new');
    Route::get('/apnatureadmin/newroomaccessories/{room_id}/{id}', [RoomAccessoriesController::class, 'edit'])->name('admin.roomaccessories.edit');
    Route::post('/apnatureadmin/newroomaccessories/{room_id}', [RoomAccessoriesController::class, 'store'])->name('admin.roomaccessories.store');
    Route::patch('/apnatureadmin/newroomaccessories/{room_id}/{id}', [RoomAccessoriesController::class, 'update'])->name('admin.roomaccessories.update');
    Route::post('/apnatureadmin/room/delete/{room_id}/{id}', [RoomAccessoriesController::class, 'delete'])->name('admin.roomaccessories.delete');

    Route::get('/apnatureadmin/newamenities/{house_id}', [AmenitiesController::class, 'new'])->name('admin.amenities.new');
    Route::get('/apnatureadmin/newamenities/{house_id}/{id}', [AmenitiesController::class, 'edit'])->name('admin.amenities.edit');
    Route::post('/apnatureadmin/newamenities/{house_id}', [AmenitiesController::class, 'store'])->name('admin.amenities.store');
    Route::patch('/apnatureadmin/newamenities/{house_id}/{id}', [AmenitiesController::class, 'update'])->name('admin.amenities.update');

    Route::get('/apnatureadmin/newbathroom/{room_id}', [BathroomController::class, 'new'])->name('admin.bathrooms.new');
    Route::get('/apnatureadmin/newbathroom/{room_id}/{id}', [BathroomController::class, 'edit'])->name('admin.bathrooms.edit');
    Route::post('/apnatureadmin/newbathroom/{room_id}', [BathroomController::class, 'store'])->name('admin.bathrooms.store');
    Route::patch('/apnatureadmin/newbathroom/{room_id}/{id}', [BathroomController::class, 'update'])->name('admin.bathrooms.update');
    Route::post('/apnatureadmin/room/bathroom/delete/{room_id}/{id}', [BathroomController::class, 'delete'])->name('admin.bathrooms.delete');

    Route::post('/apnatureadmin/newapartment/{house_id}/{apartment_id}', [ImageController::class, 'storeImageToApartment'])->name('admin.apartments.uploadimage');
    Route::post('/apnatureadmin/apartment/delete/{apartment_id}/{image_id}', [ImageController::class, 'deleteImageFromApartment'])->name('admin.apartments.deleteimage');
});

